<?php

/**
 * Camp roles (Camp Lead, Treasurer, Ticketmaster, ...), kept per season like budget categories.
 *
 * A role belongs to one season and is held by roster members of that same season, so each
 * year starts fresh: copy last season's roles, then assign this year's holders.
 *
 * A role can also grant access to parts of Camp Manager. Only holders of a role in the
 * *current* season get that access, and only while they aren't Dropped/No on the roster, so
 * last year's Treasurer loses the ledger when a new season starts. Admins (manage_options)
 * always have every area.
 */
class CampManagerRoles
{
    /** Access a role can grant, keyed by area; each area is the capability camp_manager_{area}. */
    const AREAS = [
        'roster'    => 'Roster',
        'budgets'   => 'Budgets',
        'finances'  => 'Finances (Dashboard, Ledger, Actuals)',
        'inventory' => 'Inventory',
    ];
    // Held by anyone with at least one area; lets them into wp-admin and use the season switcher.
    const CAP_ACCESS = 'camp_manager_access';
    // The one role every season has had since the camp began; Burn Year pages list its holders.
    const LEAD_ROLE = 'Camp Lead';

    /** Per-request cache of the areas each user's current roles grant. */
    private static $areasByUser = [];

    public function init()
    {
        add_filter('user_has_cap', [__CLASS__, 'grantCaps'], 10, 4);
        add_action('admin_post_camp_manager_save_role', [$this, 'handle_role_save']);
        add_action('admin_post_camp_manager_copy_roles', [$this, 'handle_copy_roles']);
    }

    public static function cap(string $area): string
    {
        return 'camp_manager_' . $area;
    }

    // ********************************* //
    // Capabilities

    public static function grantCaps($allcaps, $caps, $args, $user)
    {
        $wanted = array_merge(array_map([__CLASS__, 'cap'], array_keys(self::AREAS)), [self::CAP_ACCESS]);
        if (!array_intersect($caps, $wanted)) {
            return $allcaps;
        }

        $areas = !empty($allcaps['manage_options']) ? array_keys(self::AREAS) : self::areasForUser((int) $user->ID);
        foreach ($areas as $area) {
            $allcaps[self::cap($area)] = true;
        }
        if ($areas) {
            $allcaps[self::CAP_ACCESS] = true;
        }
        return $allcaps;
    }

    /** Areas the user's roles grant in the current season. */
    public static function areasForUser(int $wpid): array
    {
        // The role tables arrive with db version 3; until then nobody has role-based access.
        if (!$wpid || (int) get_option(CampManagerSeason::OPTION_DB_VERSION) < 3) {
            return [];
        }
        if (isset(self::$areasByUser[$wpid])) {
            return self::$areasByUser[$wpid];
        }

        global $wpdb;
        $permissions = $wpdb->get_col($wpdb->prepare(
            "SELECT r.permissions
             FROM {$wpdb->prefix}mf_roles r
             JOIN {$wpdb->prefix}mf_role_members m ON m.role_id = r.id
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             WHERE r.season = %d AND ro.wpid = %d AND (ro.status IS NULL OR ro.status NOT IN ('Dropped', 'No'))",
            CampManagerSeason::current(),
            $wpid
        ));

        $areas = [];
        foreach ($permissions as $list) {
            $areas = array_merge($areas, self::parsePermissions($list));
        }
        return self::$areasByUser[$wpid] = array_values(array_unique($areas));
    }

    /** Forget cached access, e.g. after roles or holders change within a request. */
    public static function flushCache()
    {
        self::$areasByUser = [];
    }

    private static function parsePermissions($list): array
    {
        return array_values(array_intersect(array_filter(explode(',', (string) $list)), array_keys(self::AREAS)));
    }

    // ********************************* //
    // Admin handlers

    public function handle_role_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('camp_manager_save_role');

        $role_id = !empty($_POST['role_id']) ? (int) $_POST['role_id'] : null;
        try {
            $role_id = $this->upsertRole([
                'name'        => isset($_POST['role_name']) ? wp_unslash($_POST['role_name']) : '',
                'description' => isset($_POST['role_description']) ? wp_unslash($_POST['role_description']) : '',
                'sort_order'  => isset($_POST['role_sort_order']) ? (int) $_POST['role_sort_order'] : 0,
                'permissions' => isset($_POST['role_permissions']) ? (array) $_POST['role_permissions'] : [],
            ], $role_id);
            $this->setRoleMembers($role_id, isset($_POST['role_members']) ? (array) $_POST['role_members'] : []);
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-add-role&error=' . urlencode($e->getMessage()) . ($role_id ? '&id=' . $role_id : '')));
            exit;
        }

        $page = isset($_POST['save_close_role']) ? 'camp-manager-roles' : 'camp-manager-add-role&id=' . $role_id;
        wp_redirect(admin_url('admin.php?page=' . $page . '&success=1'));
        exit;
    }

    public function handle_copy_roles()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('camp_manager_copy_roles');

        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $to = CampManagerSeason::selected();
        $from = isset($_POST['from_season']) ? (int) $_POST['from_season'] : 0;

        // Only into a season that has no roles yet, so a double click can't duplicate them.
        // Holders are not copied: they are this season's roster members, assigned fresh.
        if ($from && $from < $to && !$this->countRoles($to)) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (name, description, permissions, sort_order, season)
                 SELECT name, description, permissions, sort_order, %d FROM $table WHERE season = %d",
                $to,
                $from
            ));
        }

        wp_safe_redirect(admin_url('admin.php?page=camp-manager-roles'));
        exit;
    }

    // ********************************* //
    // Data

    public function getRoles(?int $season = null): array
    {
        global $wpdb;
        $season = $season ?: CampManagerSeason::selected();
        $roles = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mf_roles WHERE season = %d ORDER BY sort_order, name",
            $season
        ), ARRAY_A) ?: [];

        $members = $this->getMembersByRole(array_column($roles, 'id'));
        foreach ($roles as &$role) {
            $role['members'] = $members[$role['id']] ?? [];
            $role['permissions'] = self::parsePermissions($role['permissions']);
        }
        return $roles;
    }

    public function getRole($role_id): ?array
    {
        global $wpdb;
        $role = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mf_roles WHERE id = %d", (int) $role_id), ARRAY_A);
        if (!$role) {
            return null;
        }
        $role['members'] = $this->getMembersByRole([$role['id']])[$role['id']] ?? [];
        $role['permissions'] = self::parsePermissions($role['permissions']);
        return $role;
    }

    public function countRoles(int $season): int
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mf_roles WHERE season = %d", $season));
    }

    /** Most recent earlier season that has roles, or 0. */
    public function previousSeasonWithRoles(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(season) FROM {$wpdb->prefix}mf_roles WHERE season < %d",
            CampManagerSeason::selected()
        ));
    }

    /** Roster rows holding each role, keyed by role id. */
    private function getMembersByRole(array $role_ids): array
    {
        if (!$role_ids) {
            return [];
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($role_ids), '%d'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT m.role_id, ro.*
             FROM {$wpdb->prefix}mf_role_members m
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             WHERE m.role_id IN ($placeholders)
             ORDER BY ro.playaname, ro.fname",
            ...array_map('intval', $role_ids)
        ), ARRAY_A) ?: [];

        $by_role = [];
        foreach ($rows as $row) {
            $by_role[$row['role_id']][] = $row;
        }
        return $by_role;
    }

    /** Role names each roster member holds, keyed by roster id. */
    public function getRoleNamesByMember(array $roster_ids): array
    {
        if (!$roster_ids) {
            return [];
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($roster_ids), '%d'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT m.roster_id, r.name
             FROM {$wpdb->prefix}mf_role_members m
             JOIN {$wpdb->prefix}mf_roles r ON r.id = m.role_id
             WHERE m.roster_id IN ($placeholders)
             ORDER BY r.sort_order, r.name",
            ...array_map('intval', $roster_ids)
        ), ARRAY_A) ?: [];

        $by_member = [];
        foreach ($rows as $row) {
            $by_member[$row['roster_id']][] = $row['name'];
        }
        return $by_member;
    }

    /** Inserts (into the viewed season) or updates a role; returns its id. */
    public function upsertRole(array $data, ?int $role_id = null): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";

        $name = sanitize_text_field($data['name'] ?? '');
        if ($name === '') {
            throw new \Exception('A role needs a name.');
        }
        $row = [
            'name'        => $name,
            'description' => wp_kses_post($data['description'] ?? ''),
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'permissions' => implode(',', self::parsePermissions(implode(',', (array) ($data['permissions'] ?? [])))),
        ];

        if ($role_id && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $role_id))) {
            if ($wpdb->update($table, $row, ['id' => $role_id]) === false) {
                throw new \Exception("Failed to update role: {$wpdb->last_error}");
            }
        } else {
            $row['season'] = !empty($data['season']) ? (int) $data['season'] : CampManagerSeason::selected();
            if ($wpdb->insert($table, $row) === false) {
                throw new \Exception("Failed to insert role: {$wpdb->last_error}");
            }
            $role_id = (int) $wpdb->insert_id;
        }

        self::flushCache();
        return (int) $role_id;
    }

    /** Replaces a role's holders. Only roster members from the role's own season are kept. */
    public function setRoleMembers(int $role_id, array $roster_ids)
    {
        global $wpdb;
        $members = "{$wpdb->prefix}mf_role_members";
        $wpdb->delete($members, ['role_id' => $role_id]);

        $roster_ids = array_values(array_unique(array_filter(array_map('intval', $roster_ids))));
        if ($roster_ids) {
            $placeholders = implode(',', array_fill(0, count($roster_ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $members (role_id, roster_id)
                 SELECT %d, ro.id FROM {$wpdb->prefix}mf_roster ro
                 JOIN {$wpdb->prefix}mf_roles r ON r.id = %d AND r.season = ro.season
                 WHERE ro.id IN ($placeholders)",
                $role_id,
                $role_id,
                ...$roster_ids
            ));
        }
        self::flushCache();
    }

    /**
     * Active holders of the Camp Lead role in a season (roster rows), for the Burn Year
     * "Camp Leads" section. Matched by name, since each season has its own role rows.
     */
    public function leadsForSeason(int $season): array
    {
        if ((int) get_option(CampManagerSeason::OPTION_DB_VERSION) < 3) {
            return [];
        }
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT ro.*
             FROM {$wpdb->prefix}mf_roles r
             JOIN {$wpdb->prefix}mf_role_members m ON m.role_id = r.id
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             WHERE r.season = %d AND LOWER(r.name) = %s
               AND (ro.status IS NULL OR ro.status NOT IN ('Dropped', 'No'))
             ORDER BY ro.playaname, ro.fname",
            $season,
            strtolower(self::LEAD_ROLE)
        ), ARRAY_A) ?: [];
    }

    /**
     * Every Camp Manager role a WordPress user has held, for their public profile:
     * role name => seasons held (newest first). Roles are per season, so the same
     * name across seasons is one role with several years. Camp Lead comes first,
     * then the others in their sort order. Seasons where the member dropped or
     * declined don't count, as with access.
     */
    public function rolesByYearForUser(int $wpid): array
    {
        if (!$wpid || (int) get_option(CampManagerSeason::OPTION_DB_VERSION) < 3) {
            return [];
        }
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT r.name, r.season
             FROM {$wpdb->prefix}mf_roles r
             JOIN {$wpdb->prefix}mf_role_members m ON m.role_id = r.id
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             WHERE ro.wpid = %d AND (ro.status IS NULL OR ro.status NOT IN ('Dropped', 'No'))
             ORDER BY (LOWER(r.name) = %s) DESC, r.sort_order, r.name, r.season DESC",
            $wpid,
            strtolower(self::LEAD_ROLE)
        ), ARRAY_A) ?: [];

        $by_role = [];
        foreach ($rows as $row) {
            $by_role[$row['name']][] = (int) $row['season'];
        }
        foreach ($by_role as &$seasons) {
            $seasons = array_values(array_unique($seasons));
            rsort($seasons);
        }
        return $by_role;
    }

    /**
     * Makes sure every season that has a roster (or roles, or is current) has a Camp Lead
     * role, since the camp has always had one. Holders are not guessed. Returns the seasons
     * a role was added to. Runs from CampManagerSeason::upgrade() (db version 4); safe to
     * run again.
     */
    public function ensureLeadRoleEverySeason(): array
    {
        global $wpdb;
        $seasons = array_map('intval', $wpdb->get_col(
            "SELECT DISTINCT season FROM {$wpdb->prefix}mf_roster WHERE season IS NOT NULL
             UNION SELECT DISTINCT season FROM {$wpdb->prefix}mf_roles"
        ));
        $seasons[] = CampManagerSeason::current();
        $have = array_map('intval', $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT season FROM {$wpdb->prefix}mf_roles WHERE LOWER(name) = %s",
            strtolower(self::LEAD_ROLE)
        )));

        [$name, $description, $permissions] = self::defaultRoles()[0];
        $added = [];
        foreach (array_unique(array_filter($seasons)) as $season) {
            if (in_array($season, $have, true)) {
                continue;
            }
            $this->upsertRole([
                'name' => $name, 'description' => $description, 'permissions' => $permissions,
                'sort_order' => 10, 'season' => $season,
            ]);
            $added[] = $season;
        }
        sort($added);
        return $added;
    }

    /** Ids of the roles one roster member holds. */
    public function getMemberRoleIds(int $roster_id): array
    {
        global $wpdb;
        return array_map('intval', $wpdb->get_col($wpdb->prepare(
            "SELECT role_id FROM {$wpdb->prefix}mf_role_members WHERE roster_id = %d",
            $roster_id
        )) ?: []);
    }

    /**
     * Replaces one roster member's roles: the other direction of setRoleMembers(), for the
     * member edit page. Only roles from the member's own season are kept.
     */
    public function setMemberRoles(int $roster_id, array $role_ids)
    {
        global $wpdb;
        $members = "{$wpdb->prefix}mf_role_members";
        $wpdb->delete($members, ['roster_id' => $roster_id]);

        $role_ids = array_values(array_unique(array_filter(array_map('intval', $role_ids))));
        if ($role_ids) {
            $placeholders = implode(',', array_fill(0, count($role_ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $members (role_id, roster_id)
                 SELECT r.id, %d FROM {$wpdb->prefix}mf_roles r
                 JOIN {$wpdb->prefix}mf_roster ro ON ro.id = %d AND ro.season = r.season
                 WHERE r.id IN ($placeholders)",
                $roster_id,
                $roster_id,
                ...$role_ids
            ));
        }
        self::flushCache();
    }

    public function deleteRoles(array $role_ids)
    {
        $role_ids = array_map('intval', $role_ids);
        if (!$role_ids) {
            return;
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($role_ids), '%d'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_role_members WHERE role_id IN ($placeholders)", ...$role_ids));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_roles WHERE id IN ($placeholders)", ...$role_ids));
        self::flushCache();
    }

    /** Public display name for a roster row: playa name, else first name. */
    public static function displayName(array $member): string
    {
        return trim($member['playaname'] ?? '') !== '' ? trim($member['playaname']) : trim($member['fname'] ?? '');
    }

    /**
     * The roles the camp started seasons with, before they were managed here (from the old
     * /camp-roles/ page; Goblin has since gone back to Treasurer). Seeded once into the
     * current season by CampManagerSeason::upgrade() when no roles exist anywhere.
     */
    public static function defaultRoles(): array
    {
        $all = array_keys(self::AREAS);
        return [
            ['Camp Lead', 'Serves as a core leadership role within Mycodelic Forest. While no formal purpose, domains, or accountabilities are currently defined, this role typically supports coordination, decision-making, and overall camp cohesion through leadership presence and guidance.', $all],
            ['Ticketmaster', 'Ensures Mycodelic Forest members have the tickets and vehicle passes they need by facilitating access, optimizing purchases, and supporting fair distribution. They stay up to date on the full ticketing schedule and are the go-to resource for any ticket-related questions. <strong>If you need a ticket or have one to sell, it is essential that you contact the Ticketmaster</strong> so they can help match needs within the community and maximize camp participation.', []],
            ['Treasurer', 'Manages the camp’s budget for Mycodelic Forest. Focused on all things financial, this role ensures resources are allocated wisely and tracks overall spending to keep the camp fiscally grounded.', ['finances', 'budgets']],
            ['Welcome Wagon', 'First point of contact on playa for anyone arriving at Mycodelic Forest. This role is all about hospitality—greeting incoming campers, helping them get oriented, answering questions, and making sure they feel welcomed and supported as they arrive. The Welcome Wagon helps set the tone for the camp’s culture and ensures a smooth, friendly landing for everyone joining the community.', []],
            ['Tech Director', 'Ensures that all technical equipment functions properly for Mycodelic Forest. This includes overseeing and maintaining lighting, audio gear, and other tech infrastructure. The role is responsible for making sure everything “just works” so the camp can operate smoothly.', []],
            ['Funguy', 'All about uplifting spirits and spreading joy throughout Mycodelic Forest. This role focuses on keeping morale high and encouraging rest and rejuvenation, helping create a balanced and fun camp atmosphere for everyone.', []],
            ['Bike Master', 'Responsible for all things bike-related at Mycodelic Forest. This includes maintaining camp bikes, ensuring they’re in good working condition, and managing their storage.', []],
            ['Archivist', 'Responsible for documenting Mycodelic Forest on playa. This includes taking daily group photos, capturing events, recording the camp layout and decor, and gathering photos from others after the event to create a shared visual archive of the experience.', []],
            ['Firelord', 'Oversees all things related to fuel and fire at Mycodelic Forest. This includes being the main contact for fuel, ensuring fire safety, managing fuel storage, setting up the generator, and handling the setup of kitchen cooking equipment and propane systems.', []],
            ['Quartermaster', 'The first point of contact on playa for Mycodelic Forest and responsible for creating the camp layout submitted for placement. Once on playa, they ensure everyone gets to their designated spot and that the camp is set up according to plan, helping organize a smooth and well-structured arrival process.', []],
            ['Chief Engineer', 'Responsible for maintaining the Sojourner and other essential engines, including the camp generator. This role ensures that all mechanical systems are in good working order to support the camp’s operations throughout the event.', []],
        ];
    }

    public function seedDefaultRoles(int $season)
    {
        foreach (self::defaultRoles() as $i => [$name, $description, $permissions]) {
            $this->upsertRole([
                'name' => $name, 'description' => $description, 'permissions' => $permissions,
                'sort_order' => ($i + 1) * 10, 'season' => $season,
            ]);
        }
    }
}
