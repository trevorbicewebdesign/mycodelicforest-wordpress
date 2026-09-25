<?php

/**
 * Camp roles (Camp Lead, Treasurer, Ticketmaster, ...), kept per season like budget categories.
 *
 * A role belongs to one season and is held by roster members of that same season, so each
 * year starts fresh: copy last season's roles, then assign this year's holders.
 *
 * Across seasons the rows of one role share a lineage (lineage_id, the id of its earliest
 * row), so a role that was renamed (2023's Goblin is 2025's Treasurer) is still one role with
 * one history. Same-named roles join a lineage on their own; a rename is joined on the role's
 * edit page ("Same role as"). A lineage is known by the name of its newest row.
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
    // Marks (and versions) the roles export file, so an import can tell it from any other JSON.
    const EXPORT_FORMAT = 'camp-manager-roles';
    const EXPORT_VERSION = 1;
    const IMPORT_MAX_BYTES = 1048576;

    /** Per-request cache of the areas each user's current roles grant. */
    private static $areasByUser = [];

    /** Per-request cache of every role's display title, by role id (see lineages()). */
    private static $titles = [];

    public function init()
    {
        add_filter('user_has_cap', [__CLASS__, 'grantCaps'], 10, 4);
        add_action('admin_post_camp_manager_save_role', [$this, 'handle_role_save']);
        add_action('admin_post_camp_manager_copy_roles', [$this, 'handle_copy_roles']);
        add_action('admin_post_camp_manager_export_roles', [$this, 'handle_export_roles']);
        add_action('admin_post_camp_manager_import_roles', [$this, 'handle_import_roles']);
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
        self::$lineages = null;
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
                'same_as'     => isset($_POST['role_same_as']) ? sanitize_text_field(wp_unslash($_POST['role_same_as'])) : '',
                'parent_id'   => isset($_POST['role_parent']) ? (int) $_POST['role_parent'] : 0,
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

        $this->copyRoles($from, $to);

        wp_safe_redirect(admin_url('admin.php?page=camp-manager-roles'));
        exit;
    }

    public function handle_export_roles()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('camp_manager_export_roles');

        $all = isset($_POST['export_scope']) && $_POST['export_scope'] === 'all';
        $season = $all ? null : CampManagerSeason::selected();
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="camp-roles-' . ($all ? 'all-seasons' : (int) $season) . '.json"');
        echo wp_json_encode($this->exportRoles($season), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** The result (or error) is shown on the Camp Roles page after the redirect. */
    public function handle_import_roles()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('camp_manager_import_roles');

        $dry_run = !empty($_POST['import_preview']);
        $result = ['error' => 'Choose a roles file to import.'];
        $file = $_FILES['roles_file'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
            try {
                if ($file['size'] > self::IMPORT_MAX_BYTES) {
                    throw new \InvalidArgumentException('That file is too big to be a roles export.');
                }
                $result = $this->importRolesFromJson((string) file_get_contents($file['tmp_name']), $dry_run);
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage()];
            }
        }
        set_transient('camp_manager_roles_import_' . get_current_user_id(), $result, 300);
        wp_safe_redirect(admin_url('admin.php?page=camp-manager-roles'));
        exit;
    }

    /**
     * Copies one season's roles into a later season that has none yet (so a double click
     * can't duplicate them). Each copy stays the same role as its original (same lineage).
     * Holders are not copied: they are the new season's roster members, assigned fresh.
     */
    public function copyRoles(int $from, int $to)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        if ($from && $from < $to && !$this->countRoles($to)) {
            $roles = $wpdb->get_results($wpdb->prepare(
                "SELECT id, name, description, permissions, sort_order, COALESCE(lineage_id, id) AS lineage FROM $table WHERE season = %d",
                $from
            ), ARRAY_A) ?: [];
            foreach ($roles as $role) {
                $wpdb->insert($table, [
                    'name' => $role['name'], 'description' => $role['description'], 'permissions' => $role['permissions'],
                    'sort_order' => (int) $role['sort_order'], 'season' => $to, 'lineage_id' => (int) $role['lineage'],
                ]);
            }
            $this->copyCircles($from, $to);
            self::$lineages = null;
        }
    }

    /** Puts each copied role inside the copy of the circle it was in (matched by lineage). */
    private function copyCircles(int $from, int $to)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $old = $wpdb->get_results($wpdb->prepare(
            "SELECT id, parent_id, COALESCE(lineage_id, id) AS lineage FROM $table WHERE season = %d",
            $from
        ), ARRAY_A) ?: [];
        $copies = array_column($wpdb->get_results($wpdb->prepare(
            "SELECT id, lineage_id FROM $table WHERE season = %d",
            $to
        ), ARRAY_A) ?: [], 'id', 'lineage_id');
        $lineage_of = array_column($old, 'lineage', 'id');

        foreach ($old as $row) {
            $child = $copies[$row['lineage']] ?? 0;
            $parent = $copies[$lineage_of[$row['parent_id']] ?? 0] ?? 0;
            if ($row['parent_id'] && $child && $parent) {
                $wpdb->update($table, ['parent_id' => (int) $parent], ['id' => (int) $child]);
            }
        }
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
        $roles = self::treeOrder(self::withTitles($roles));

        $members = $this->getMembersByRole(array_column($roles, 'id'));
        foreach ($roles as &$role) {
            $role['members'] = $members[$role['id']] ?? [];
            $role['permissions'] = self::parsePermissions($role['permissions']);
            $role['also_known_as'] = $this->otherNames($role);
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
        $role['parent_id'] = $role['parent_id'] === null ? null : (int) $role['parent_id'];
        $role['members'] = $this->getMembersByRole([$role['id']])[$role['id']] ?? [];
        $role['permissions'] = self::parsePermissions($role['permissions']);
        $role['also_known_as'] = $this->otherNames($role);
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

    /**
     * Adds a 'title' to role rows (id, season, name, parent_id): the name, plus the circle it
     * is in when another role of that season has the same name (every circle has a Circle
     * Lead), so a role can be told apart where only its name is shown.
     */
    private static function withTitles(array $rows): array
    {
        $count = [];
        $names = [];
        foreach ($rows as $row) {
            $key = strtolower($row['name']);
            $count[$row['season']][$key] = ($count[$row['season']][$key] ?? 0) + 1;
            $names[(int) $row['id']] = $row['name'];
        }
        foreach ($rows as &$row) {
            $row['title'] = $row['name'];
            $parent = (int) ($row['parent_id'] ?? 0);
            if ($parent && isset($names[$parent]) && $count[$row['season']][strtolower($row['name'])] > 1) {
                $row['title'] .= ' (' . $names[$parent] . ')';
            }
        }
        return $rows;
    }

    /**
     * Orders one season's roles as a tree: each circle's roles follow it, indented by 'depth',
     * siblings by their order then name. Adds 'depth', 'path' ("Placement › Quartermaster") and
     * 'is_circle' (it holds other roles). A role whose parent is missing, or that sits in a
     * loop, is shown at the top level instead of being lost.
     */
    private static function treeOrder(array $rows): array
    {
        $by_id = [];
        foreach ($rows as &$row) {
            $row['parent_id'] = empty($row['parent_id']) ? null : (int) $row['parent_id'];
            $by_id[(int) $row['id']] = true;
        }
        unset($row);

        $children = [];
        foreach ($rows as $row) {
            $parent = $row['parent_id'];
            $shown_in = $parent && $parent !== (int) $row['id'] && isset($by_id[$parent]) ? $parent : 0;
            $row['parent_id'] = $shown_in ?: null;
            $children[$shown_in][] = $row;
        }

        $ordered = [];
        $seen = [];
        $walk = function (int $parent, int $depth, array $path) use (&$walk, &$children, &$ordered, &$seen) {
            $siblings = $children[$parent] ?? [];
            usort($siblings, function ($a, $b) {
                return [(int) $a['sort_order'], strtolower($a['name'])] <=> [(int) $b['sort_order'], strtolower($b['name'])];
            });
            foreach ($siblings as $row) {
                $id = (int) $row['id'];
                if (isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $row['depth'] = $depth;
                $row['path'] = implode(' › ', array_merge($path, [$row['name']]));
                $row['is_circle'] = !empty($children[$id]);
                $ordered[] = $row;
                $walk($id, $depth + 1, array_merge($path, [$row['name']]));
            }
        };
        $walk(0, 0, []);

        foreach ($rows as $row) {
            if (!isset($seen[(int) $row['id']])) {
                $ordered[] = array_merge($row, ['parent_id' => null, 'depth' => 0, 'path' => $row['name'], 'is_circle' => false]);
            }
        }
        return $ordered;
    }

    /**
     * Circles a role can sit in, for the edit page: every other role of its season except
     * the role itself and the roles inside it (a circle can't sit in itself), as
     * id, label (indented by depth).
     */
    public function parentOptions(int $season, ?int $role_id): array
    {
        $roles = $this->getRoles($season);
        $skip = [];
        if ($role_id) {
            $skip[$role_id] = true;
            foreach ($roles as $role) { // tree order: a role's circle always comes before it
                if (isset($skip[(int) $role['parent_id']])) {
                    $skip[(int) $role['id']] = true;
                }
            }
        }
        $options = [];
        foreach ($roles as $role) {
            if (!isset($skip[(int) $role['id']])) {
                $options[] = ['id' => (int) $role['id'], 'label' => str_repeat('— ', $role['depth']) . $role['name']];
            }
        }
        return $options;
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
            "SELECT m.roster_id, r.id, r.name
             FROM {$wpdb->prefix}mf_role_members m
             JOIN {$wpdb->prefix}mf_roles r ON r.id = m.role_id
             WHERE m.roster_id IN ($placeholders)
             ORDER BY r.sort_order, r.name",
            ...array_map('intval', $roster_ids)
        ), ARRAY_A) ?: [];

        $titles = $this->titles();
        $by_member = [];
        foreach ($rows as $row) {
            $by_member[$row['roster_id']][] = $titles[(int) $row['id']] ?? $row['name'];
        }
        return $by_member;
    }

    /**
     * Inserts (into the viewed season) or updates a role; returns its id.
     *
     * 'same_as' sets the role's lineage: another role's id joins this role's history to that
     * role's, 'new' starts a fresh history for this row alone, and empty leaves it as it is.
     * A new role with nothing given continues the newest other role with the same name (in the
     * same circle, see matchingRoleId()), if any.
     *
     * 'parent_id' puts the role inside a circle (another role of its season); 0 or null makes
     * it a top-level role, and leaving the key out keeps things as they are.
     */
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

        $season = $role_id ? (int) $wpdb->get_var($wpdb->prepare("SELECT season FROM $table WHERE id = %d", $role_id)) : 0;
        if ($season) {
            if (array_key_exists('parent_id', $data)) {
                $row['parent_id'] = $this->checkedParent($data['parent_id'], $season, $role_id);
            }
            if ($wpdb->update($table, $row, ['id' => $role_id]) === false) {
                throw new \Exception("Failed to update role: {$wpdb->last_error}");
            }
        } else {
            $row['season'] = !empty($data['season']) ? (int) $data['season'] : CampManagerSeason::selected();
            if (array_key_exists('parent_id', $data)) {
                $row['parent_id'] = $this->checkedParent($data['parent_id'], (int) $row['season'], null);
            }
            if ($wpdb->insert($table, $row) === false) {
                throw new \Exception("Failed to insert role: {$wpdb->last_error}");
            }
            $role_id = (int) $wpdb->insert_id;
            if (($data['same_as'] ?? '') === '') {
                $data['same_as'] = $this->matchingRoleId($role_id) ?: 'new';
            }
        }
        if (($data['same_as'] ?? '') !== '') {
            $this->setLineage((int) $role_id, $data['same_as']);
        }

        self::flushCache();
        return (int) $role_id;
    }

    // ********************************* //
    // Lineage: the same role across seasons

    /** Per-request cache of every role's lineage: lineage id => rows (newest season first). */
    private static $lineages = null;

    /** The lineage a role belongs to (its earliest row's id). */
    public function lineageOf(int $role_id): int
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(lineage_id, id) FROM {$wpdb->prefix}mf_roles WHERE id = %d",
            $role_id
        ));
    }

    /**
     * Every role grouped by lineage, newest season first, so [0] of a lineage is the row
     * whose name and order stand for the whole role.
     */
    public function lineages(): array
    {
        if (self::$lineages === null) {
            global $wpdb;
            $rows = $wpdb->get_results(
                "SELECT id, season, name, sort_order, parent_id, COALESCE(lineage_id, id) AS lineage
                 FROM {$wpdb->prefix}mf_roles ORDER BY season DESC, sort_order, name",
                ARRAY_A
            ) ?: [];
            self::$lineages = [];
            self::$titles = [];
            foreach (self::withTitles($rows) as $row) {
                self::$lineages[(int) $row['lineage']][] = [
                    'id' => (int) $row['id'], 'season' => (int) $row['season'],
                    'name' => $row['name'], 'title' => $row['title'], 'sort_order' => (int) $row['sort_order'],
                ];
                self::$titles[(int) $row['id']] = $row['title'];
            }
        }
        return self::$lineages;
    }

    /** Display title of every role by id: the name, plus its circle where the name alone is ambiguous. */
    private function titles(): array
    {
        $this->lineages();
        return self::$titles;
    }

    /** Names a role has gone by in other seasons, with those seasons: ['Goblin' => [2024, 2023]]. */
    public function otherNames(array $role): array
    {
        $names = [];
        foreach ($this->lineages()[$this->lineageOf((int) $role['id'])] ?? [] as $row) {
            if (strcasecmp($row['name'], $role['name']) !== 0) {
                $names[$row['name']][] = $row['season'];
            }
        }
        return $names;
    }

    /**
     * Roles a role could be declared the same as, for the edit page: every role from another
     * season that isn't already in its lineage, labelled "YYYY Name", newest first.
     */
    public function lineageOptions(?int $role_id, int $season): array
    {
        $mine = $role_id ? $this->lineageOf($role_id) : 0;
        $options = [];
        foreach ($this->lineages() as $lineage => $rows) {
            if ($lineage === $mine) {
                continue;
            }
            foreach ($rows as $row) {
                if ($row['season'] !== $season) {
                    $options[] = ['id' => $row['id'], 'label' => self::roleLabel($row)];
                }
            }
        }
        usort($options, function ($a, $b) {
            return strcmp($b['label'], $a['label']);
        });
        return $options;
    }

    /**
     * The circle a role may be put in: a role of the same season that isn't the role itself
     * or inside it. Returns its id (null for none) or throws.
     */
    private function checkedParent($parent, int $season, ?int $self): ?int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $parent = (int) $parent;
        if (!$parent) {
            return null;
        }
        if ((int) $wpdb->get_var($wpdb->prepare("SELECT season FROM $table WHERE id = %d", $parent)) !== $season) {
            throw new \Exception('A role can only sit in a circle from its own season.');
        }
        // Walk up from the chosen circle: meeting the role itself would make a loop.
        for ($id = $parent, $steps = 0; $id && $steps < 50; $steps++) {
            if ($self && $id === $self) {
                throw new \Exception('A role can\'t sit inside itself or inside one of its own roles.');
            }
            $id = (int) $wpdb->get_var($wpdb->prepare("SELECT parent_id FROM $table WHERE id = %d", $id));
        }
        return $parent;
    }

    /**
     * Id of the newest role in another season that a new role continues, or 0. A role with the
     * same name (case-insensitive) in the same circle (the circles are the same role across
     * seasons) counts. Failing that, a same-named role that was the only one of its name in
     * its season counts, so a role that later moved into a circle keeps its history, unless
     * that history is already in use in this season (every circle has its own Circle Lead).
     */
    private function matchingRoleId(int $role_id): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $me = $wpdb->get_row($wpdb->prepare("SELECT season, name, parent_id FROM $table WHERE id = %d", $role_id), ARRAY_A);
        if (!$me) {
            return 0;
        }
        $candidates = $wpdb->get_results($wpdb->prepare(
            "SELECT id, season, parent_id, COALESCE(lineage_id, id) AS lineage
             FROM $table
             WHERE LOWER(name) = %s AND season <> %d
             ORDER BY season DESC, id DESC",
            strtolower($me['name']),
            $me['season']
        ), ARRAY_A) ?: [];
        // Each candidate's circle's lineage, from a second query (not a self-join: the
        // integration tests' temporary tables can't be named twice in one query).
        $parents = [];
        $parent_ids = array_values(array_filter(array_unique(array_column($candidates, 'parent_id'))));
        if ($parent_ids) {
            $placeholders = implode(',', array_fill(0, count($parent_ids), '%d'));
            $parents = array_column($wpdb->get_results($wpdb->prepare(
                "SELECT id, COALESCE(lineage_id, id) AS lineage FROM $table WHERE id IN ($placeholders)",
                ...array_map('intval', $parent_ids)
            ), ARRAY_A) ?: [], 'lineage', 'id');
        }
        foreach ($candidates as &$candidate) {
            $candidate['parent_lineage'] = $candidate['parent_id'] ? ($parents[$candidate['parent_id']] ?? null) : null;
        }
        unset($candidate);

        $parent_lineage = $me['parent_id'] ? $this->lineageOf((int) $me['parent_id']) : null;
        foreach ($candidates as $candidate) {
            $theirs = $candidate['parent_lineage'] === null ? null : (int) $candidate['parent_lineage'];
            if ($theirs === $parent_lineage) {
                return (int) $candidate['id'];
            }
        }

        $per_season = array_count_values(array_column($candidates, 'season'));
        $in_use = array_map('intval', $wpdb->get_col($wpdb->prepare(
            "SELECT COALESCE(lineage_id, id) FROM $table WHERE season = %d AND id <> %d",
            $me['season'],
            $role_id
        )));
        foreach ($candidates as $candidate) {
            if ($per_season[$candidate['season']] === 1 && !in_array((int) $candidate['lineage'], $in_use, true)) {
                return (int) $candidate['id'];
            }
        }
        return 0;
    }

    /**
     * Moves a role into another role's lineage (every row of its current lineage comes along,
     * so two histories merge), or with 'new' cuts this one row out into a history of its own.
     */
    public function setLineage(int $role_id, $same_as)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $mine = $this->lineageOf($role_id);
        if (!$mine) {
            return;
        }

        if ($same_as === 'new') {
            $others = array_map('intval', $wpdb->get_col($wpdb->prepare(
                "SELECT id FROM $table WHERE COALESCE(lineage_id, id) = %d AND id <> %d",
                $mine,
                $role_id
            )));
            if ($others) {
                // The rest keep a lineage of their own, rooted at their earliest row.
                $placeholders = implode(',', array_fill(0, count($others), '%d'));
                $wpdb->query($wpdb->prepare("UPDATE $table SET lineage_id = %d WHERE id IN ($placeholders)", min($others), ...$others));
            }
            $wpdb->update($table, ['lineage_id' => $role_id], ['id' => $role_id]);
        } else {
            $target = $this->lineageOf((int) $same_as);
            if ($target && $target !== $mine) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table SET lineage_id = %d WHERE COALESCE(lineage_id, id) = %d",
                    $target,
                    $mine
                ));
            }
        }
        self::$lineages = null;
    }

    /** Joins every same-named role into one lineage where none is set yet (db version 5). */
    public function backfillLineages()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        // Done in PHP, not one UPDATE joined to a grouped copy of the table: the integration
        // tests' temporary tables can't be named twice in one query.
        $rows = $wpdb->get_results("SELECT id, name FROM $table ORDER BY id", ARRAY_A) ?: [];
        $first = [];
        foreach ($rows as $row) {
            $first[strtolower($row['name'])] = $first[strtolower($row['name'])] ?? (int) $row['id'];
        }
        foreach ($first as $name => $id) {
            $wpdb->query($wpdb->prepare("UPDATE $table SET lineage_id = %d WHERE LOWER(name) = %s AND lineage_id IS NULL", $id, $name));
        }
        self::$lineages = null;
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
        // The lineage of any role named Camp Lead, so a season that called it something else still counts.
        $lineages = array_map('intval', $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT COALESCE(lineage_id, id) FROM {$wpdb->prefix}mf_roles WHERE LOWER(name) = %s",
            strtolower(self::LEAD_ROLE)
        )));
        if (!$lineages) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($lineages), '%d'));
        return $wpdb->get_results($wpdb->prepare(
            "SELECT ro.*
             FROM {$wpdb->prefix}mf_roles r
             JOIN {$wpdb->prefix}mf_role_members m ON m.role_id = r.id
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             WHERE r.season = %d
               AND COALESCE(r.lineage_id, r.id) IN ($placeholders)
               AND (ro.status IS NULL OR ro.status NOT IN ('Dropped', 'No'))
             ORDER BY ro.playaname, ro.fname",
            $season,
            ...$lineages
        ), ARRAY_A) ?: [];
    }

    /**
     * Every Camp Manager role a WordPress user has held, for their public profile:
     * role name => seasons held (newest first). Rows of one lineage are one role, under
     * the lineage's current name (so 2023's Goblin is listed as Treasurer; see
     * formerNamesForUser()). Camp Lead comes first, then the others in their sort order.
     * Seasons where the member dropped or declined don't count, as with access.
     */
    public function rolesByYearForUser(int $wpid): array
    {
        $by_role = [];
        foreach ($this->userRoleLineages($wpid) as $info) {
            $by_role[$info['title']] = array_values(array_unique(array_merge($by_role[$info['title']] ?? [], $info['seasons'])));
            rsort($by_role[$info['title']]);
        }
        return $by_role;
    }

    /**
     * For each role in rolesByYearForUser(), the earlier names the user held it under:
     * current name => [former name => seasons]. Only roles with a former name appear.
     */
    public function formerNamesForUser(int $wpid): array
    {
        $former = [];
        foreach ($this->userRoleLineages($wpid) as $info) {
            foreach ($info['former'] as $name => $seasons) {
                $former[$info['title']][$name] = array_values(array_unique(array_merge($former[$info['title']][$name] ?? [], $seasons)));
                rsort($former[$info['title']][$name]);
            }
        }
        return $former;
    }

    /** The user's held roles grouped by lineage and ordered as the profile lists them. */
    private function userRoleLineages(int $wpid): array
    {
        if (!$wpid || (int) get_option(CampManagerSeason::OPTION_DB_VERSION) < 3) {
            return [];
        }
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT r.id, r.name, r.season, COALESCE(r.lineage_id, r.id) AS lineage
             FROM {$wpdb->prefix}mf_roles r
             JOIN {$wpdb->prefix}mf_role_members m ON m.role_id = r.id
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             WHERE ro.wpid = %d AND (ro.status IS NULL OR ro.status NOT IN ('Dropped', 'No'))
             ORDER BY r.season DESC",
            $wpid
        ), ARRAY_A) ?: [];

        $lineages = $this->lineages();
        $held = [];
        foreach ($rows as $row) {
            $lineage = (int) $row['lineage'];
            if (!isset($held[$lineage])) {
                $current = $lineages[$lineage][0] ?? ['name' => $row['name'], 'title' => $row['name'], 'sort_order' => 0];
                $held[$lineage] = ['name' => $current['name'], 'title' => $current['title'], 'sort_order' => $current['sort_order'], 'seasons' => [], 'former' => []];
            }
            $held[$lineage]['seasons'][] = (int) $row['season'];
            if (strcasecmp($row['name'], $held[$lineage]['name']) !== 0) {
                $held[$lineage]['former'][$row['name']][] = (int) $row['season'];
            }
        }
        uasort($held, function ($a, $b) {
            return [strcasecmp($a['name'], self::LEAD_ROLE) !== 0, $a['sort_order'], strtolower($a['name'])]
                <=> [strcasecmp($b['name'], self::LEAD_ROLE) !== 0, $b['sort_order'], strtolower($b['name'])];
        });
        return $held;
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

    // ********************************* //
    // Roles by WordPress user (the wp-admin user profile)

    /**
     * Every role in every season, newest season first, for the user-profile picker. Each
     * row carries a 'label' of "YYYY Name", so typing the year narrows a list to that season.
     */
    public function getAllRoles(): array
    {
        global $wpdb;
        $roles = $wpdb->get_results(
            "SELECT id, season, name, sort_order, parent_id FROM {$wpdb->prefix}mf_roles ORDER BY season DESC, sort_order, name",
            ARRAY_A
        ) ?: [];
        $roles = self::withTitles($roles);
        foreach ($roles as &$role) {
            $role['id'] = (int) $role['id'];
            $role['season'] = (int) $role['season'];
            $role['label'] = self::roleLabel($role);
        }
        return $roles;
    }

    /** "YYYY Role name": the season first, so a role can be found by typing its year. */
    public static function roleLabel(array $role): string
    {
        return (int) $role['season'] . ' ' . ($role['title'] ?? $role['name']);
    }

    /**
     * The roster row that stands for a WordPress user in each season, keyed by season
     * (newest first). A user should have one row per season; if there are several, an
     * active one (not Dropped/No) wins, then the oldest.
     */
    public function rosterRowsForUser(int $wpid): array
    {
        if (!$wpid) {
            return [];
        }
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mf_roster
             WHERE wpid = %d AND season IS NOT NULL
             ORDER BY season DESC, (status IN ('Dropped', 'No')) ASC, id ASC",
            $wpid
        ), ARRAY_A) ?: [];

        $by_season = [];
        foreach ($rows as $row) {
            $by_season[(int) $row['season']] = $by_season[(int) $row['season']] ?? $row;
        }
        return $by_season;
    }

    /** Ids of every role a WordPress user holds through any of their roster rows, newest season first. */
    public function getUserRoleIds(int $wpid): array
    {
        if (!$wpid) {
            return [];
        }
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT r.id
             FROM {$wpdb->prefix}mf_role_members m
             JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
             JOIN {$wpdb->prefix}mf_roles r ON r.id = m.role_id
             WHERE ro.wpid = %d
             ORDER BY r.season DESC, r.sort_order, r.name",
            $wpid
        )) ?: [];
        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * A roster row nobody is linked to yet that looks like this WordPress user, in a season:
     * matched by email, then first and last name, then playa name (all case-insensitive),
     * the best match first. Read-only; autoLinkRosterRow() does the linking.
     */
    public function findUnlinkedRosterRow(int $wpid, int $season): ?array
    {
        $user = $wpid ? get_userdata($wpid) : false;
        if (!$user) {
            return null;
        }
        $email = strtolower(trim((string) $user->user_email));
        $fname = strtolower(trim((string) get_user_meta($wpid, 'first_name', true)));
        $lname = strtolower(trim((string) get_user_meta($wpid, 'last_name', true)));
        $playa = strtolower(trim((string) get_user_meta($wpid, 'playa_name', true)));

        $matches = [];
        $args = [];
        if ($email !== '') {
            $matches[] = 'LOWER(email) = %s';
            $args[] = $email;
        }
        if ($fname !== '' && $lname !== '') {
            $matches[] = '(LOWER(fname) = %s AND LOWER(lname) = %s)';
            array_push($args, $fname, $lname);
        }
        if ($playa !== '') {
            $matches[] = 'LOWER(playaname) = %s';
            $args[] = $playa;
        }
        if (!$matches) {
            return null;
        }

        global $wpdb;
        // The same tests order the candidates, so an email match beats a name match.
        $order = implode(' DESC, ', array_map(function ($m) { return "($m)"; }, $matches)) . ' DESC';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mf_roster
             WHERE season = %d AND (wpid IS NULL OR wpid = 0) AND (" . implode(' OR ', $matches) . ")
             ORDER BY $order, id ASC
             LIMIT 1",
            $season,
            ...$args,
            ...$args
        ), ARRAY_A);
        return $row ?: null;
    }

    /**
     * Links a WordPress user to their roster row in a season when nothing links them yet
     * (see findUnlinkedRosterRow()). Only wpid is written: the entry's names, email, status
     * and everything else stay exactly as they were. Returns the row now linked, or null
     * when there is nothing to link.
     */
    public function autoLinkRosterRow(int $wpid, int $season): ?array
    {
        $row = $this->findUnlinkedRosterRow($wpid, $season);
        if (!$row) {
            return null;
        }
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}mf_roster", ['wpid' => $wpid], ['id' => (int) $row['id']]);
        $row['wpid'] = $wpid;
        self::flushCache();
        return $row;
    }

    /**
     * Replaces a WordPress user's roles across seasons, from the admin user profile: the
     * other direction of setMemberRoles(), for someone rather than one roster row. Roles are
     * held through the roster, so in each season the user is on the roster their roles become
     * the submitted ones from that season (none submitted clears that season). A role from a
     * season they aren't linked to yet first auto-links their roster entry for that season;
     * when there is none to link the role can't be held and is skipped.
     *
     * Returns ['skipped' => role ids, 'linked' => season => roster row linked on the way].
     */
    public function setUserRoles(int $wpid, array $role_ids): array
    {
        $wanted = array_values(array_unique(array_filter(array_map('intval', $role_ids))));
        $rows = $this->rosterRowsForUser($wpid);

        $season_of = [];
        foreach ($this->getAllRoles() as $role) {
            $season_of[$role['id']] = $role['season'];
        }
        $per_season = [];
        $skipped = [];
        $linked = [];
        foreach ($wanted as $role_id) {
            $season = $season_of[$role_id] ?? 0;
            if ($season && !isset($rows[$season]) && !isset($linked[$season])) {
                $row = $this->autoLinkRosterRow($wpid, $season);
                if ($row) {
                    $rows[$season] = $linked[$season] = $row;
                }
            }
            if ($season && isset($rows[$season])) {
                $per_season[$season][] = $role_id;
            } else {
                $skipped[] = $role_id;
            }
        }

        global $wpdb;
        foreach ($rows as $season => $row) {
            // Clear every roster row of theirs in the season (there should be one), then set the chosen one.
            $wpdb->query($wpdb->prepare(
                "DELETE m FROM {$wpdb->prefix}mf_role_members m
                 JOIN {$wpdb->prefix}mf_roster ro ON ro.id = m.roster_id
                 WHERE ro.wpid = %d AND ro.season = %d",
                $wpid,
                $season
            ));
            $this->setMemberRoles((int) $row['id'], $per_season[$season] ?? []);
        }
        return ['skipped' => $skipped, 'linked' => $linked];
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
        // The roles inside a deleted circle stay, at the top level.
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mf_roles SET parent_id = NULL WHERE parent_id IN ($placeholders)", ...$role_ids));
        // Rows whose lineage was rooted at a deleted role are re-rooted at their earliest survivor.
        $rows = $wpdb->get_results("SELECT id, lineage_id FROM {$wpdb->prefix}mf_roles ORDER BY id", ARRAY_A) ?: [];
        $alive = array_flip(array_column($rows, 'id'));
        $orphaned = [];
        foreach ($rows as $row) {
            if ($row['lineage_id'] !== null && !isset($alive[$row['lineage_id']])) {
                $orphaned[(int) $row['lineage_id']][] = (int) $row['id'];
            }
        }
        foreach ($orphaned as $ids) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mf_roles SET lineage_id = %d WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ')',
                $ids[0],
                ...$ids
            ));
        }
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
            ['Circle Lead', 'Facilitates camp meetings and assists in coordinating between groups, helping keep the whole camp on the same page.', []],
            ['Programming Director', 'Organizes and schedules Mycodelic Forest’s events, planning what the camp offers to the wider community and keeping the calendar clear for everyone.', []],
            // The last three are named contacts Burning Man requires of every registered camp.
            ['Leave No Trace Lead', 'Champions Leave No Trace for Mycodelic Forest and organizes the camp’s MOOP sweeps. This role reminds campers to bring MOOP-free gear, clean their vehicles before heading to the playa, and carry a MOOP bag, and helps the camp leave the site exactly as we found it. Also the camp’s contact for Burning Man on Leave No Trace.', []],
            ['Sustainability Lead', 'Guides Mycodelic Forest toward more sustainable choices, from cutting waste and greywater to how we power, feed, and pack out the camp. This role tracks what the camp can reuse, reduce, or do better each year, and is the camp’s contact for Burning Man on sustainability.', []],
            ['R.I.D.E. Lead', 'The camp’s point person for R.I.D.E. at Mycodelic Forest, helping keep the camp welcoming to everyone. This role shares R.I.D.E. information and resources with campers and is the camp’s contact for Burning Man on R.I.D.E.', []],
        ];
    }

    /**
     * Adds any default role (matched by name, any case) a season is missing, after the roles
     * it has, and returns the names added. Safe to run again.
     */
    public function addMissingDefaultRoles(int $season, array $names): array
    {
        global $wpdb;
        $have = array_map('strtolower', $wpdb->get_col($wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}mf_roles WHERE season = %d",
            $season
        )));
        $sort = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(sort_order) FROM {$wpdb->prefix}mf_roles WHERE season = %d",
            $season
        ));

        $added = [];
        foreach (self::defaultRoles() as [$name, $description, $permissions]) {
            if (!in_array($name, $names, true) || in_array(strtolower($name), $have, true)) {
                continue;
            }
            $sort += 10;
            $this->upsertRole([
                'name' => $name, 'description' => $description, 'permissions' => $permissions,
                'sort_order' => $sort, 'season' => $season,
            ]);
            $added[] = $name;
        }
        return $added;
    }

    /**
     * Adds those default roles to every season from $from on that has a roster or roles, and to
     * the current season. Returns the names added per season. Runs from
     * CampManagerSeason::upgrade() (db version 6); safe to run again.
     */
    public function addMissingDefaultRolesSince(int $from, array $names): array
    {
        global $wpdb;
        $seasons = array_map('intval', $wpdb->get_col(
            "SELECT DISTINCT season FROM {$wpdb->prefix}mf_roster WHERE season IS NOT NULL
             UNION SELECT DISTINCT season FROM {$wpdb->prefix}mf_roles"
        ));
        $seasons[] = CampManagerSeason::current();

        $added = [];
        foreach (array_unique(array_filter($seasons)) as $season) {
            if ($season >= $from && ($done = $this->addMissingDefaultRoles($season, $names))) {
                $added[$season] = $done;
            }
        }
        ksort($added);
        return $added;
    }

    /**
     * The camp's circles, loosely after Holacracy: the whole camp is the Anchor Circle, which
     * holds roles and smaller circles, and every circle has a Circle Lead. A role is either a
     * name from defaultRoles() or [name, description, permissions]. Placed into the current
     * season by applyDefaultStructure(); later seasons copy it.
     */
    public static function defaultStructure(): array
    {
        return [
            'name' => 'Mycodelic Forest Anchor Circle',
            'description' => 'The whole camp. Every role and circle in Mycodelic Forest sits here or in a circle inside it, and anything not held by a smaller circle belongs to the camp as a whole.',
            'roles' => [
                'Circle Lead', 'Camp Lead', 'Treasurer', 'Ticketmaster', 'Programming Director', 'Funguy', 'Tech Director',
                'Bike Master', 'Firelord', 'Leave No Trace Lead', 'Sustainability Lead', 'R.I.D.E. Lead',
                ['Build', 'Leads building the camp’s shared structures, getting them set up on arrival and taken down again at the end, with the people and tools to do it.', []],
                ['Power', 'Looks after the camp’s power: making sure everything that needs electricity has it, and that it is set up and used safely.', []],
            ],
            'circles' => [
                [
                    'name' => 'Communications',
                    'description' => 'How Mycodelic Forest talks with its members and the wider community: welcoming newcomers, the website, and documenting the camp.',
                    'roles' => [
                        ['Webmaster', 'Looks after the camp website, mycodelicforest.org, including Camp Manager, member profiles, and keeping the site up to date.', []],
                        'Welcome Wagon', 'Archivist',
                    ],
                ],
                [
                    'name' => 'Placement',
                    'description' => 'Where the camp sits on playa: the placement application and the camp layout.',
                    'roles' => ['Quartermaster'],
                ],
                [
                    'name' => 'Sojourner',
                    'description' => 'The camp’s bus, Sojourner: keeping it running and ready to go.',
                    'roles' => ['Chief Engineer'],
                ],
            ],
        ];
    }

    /**
     * Puts a season's roles into defaultStructure(): creates the circles and any role that
     * isn't there yet, and moves top-level roles of the same name into their circle. A role
     * that is already inside a circle is left where it is, and holders are never touched.
     * Every circle gets its own Circle Lead. Returns the names it created; safe to run again.
     */
    public function applyDefaultStructure(int $season): array
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $known = [];
        foreach (self::defaultRoles() as [$name, $description, $permissions]) {
            $known[strtolower($name)] = [$name, $description, $permissions];
        }
        $sort = (int) $wpdb->get_var($wpdb->prepare("SELECT MAX(sort_order) FROM $table WHERE season = %d", $season));
        $created = [];

        // Finds the role (adopting a top-level one into the circle) or creates it; returns its id.
        $place = function (string $name, string $description, array $permissions, int $parent, bool $per_circle, int $order = 0) use ($wpdb, $table, $season, &$sort, &$created): int {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT id, parent_id FROM $table WHERE season = %d AND LOWER(name) = %s ORDER BY id",
                $season,
                strtolower($name)
            ), ARRAY_A) ?: [];
            $found = null;
            foreach ($rows as $row) {
                if ((int) $row['parent_id'] === $parent) {
                    $found = $row; // already in this circle
                    break;
                }
            }
            if (!$found) {
                foreach ($rows as $row) {
                    // A role of that name elsewhere is left alone, except that a top-level one
                    // is adopted; each circle has its own Circle Lead, so only a top-level
                    // one can be moved.
                    if (!$row['parent_id'] || !$per_circle) {
                        $found = $row;
                        break;
                    }
                }
            }
            if ($found) {
                if (!$found['parent_id'] && $parent) {
                    // A Circle Lead adopted into a circle also moves to the top of it.
                    $wpdb->update($table, ['parent_id' => $parent] + ($per_circle ? ['sort_order' => $order] : []), ['id' => (int) $found['id']]);
                }
                return (int) $found['id'];
            }
            $created[] = $name;
            return $this->upsertRole([
                'name' => $name, 'description' => $description, 'permissions' => $permissions,
                'sort_order' => $order ?: ($sort += 10), 'season' => $season, 'parent_id' => $parent,
            ]);
        };
        $role_in = function (int $circle, string $circle_name, $role) use ($place, $known) {
            [$name, $description, $permissions] = is_array($role) ? $role : ($known[strtolower($role)] ?? [$role, '', []]);
            if (strtolower($name) === 'circle lead') {
                $description = $circle_name === '' ? $description : 'Leads the ' . $circle_name . ' circle: facilitates its meetings and coordinates between its roles and the rest of camp.';
                return $place($name, $description, $permissions, $circle, true, 5);
            }
            return $place($name, $description, $permissions, $circle, false);
        };

        $structure = self::defaultStructure();
        $anchor = $place($structure['name'], $structure['description'], [], 0, false, 1);
        foreach ($structure['roles'] as $role) {
            $role_in($anchor, '', $role);
        }
        foreach ($structure['circles'] as $circle) {
            $id = $place($circle['name'], $circle['description'], [], $anchor, false);
            $role_in($id, $circle['name'], 'Circle Lead');
            foreach ($circle['roles'] as $role) {
                $role_in($id, $circle['name'], $role);
            }
        }
        self::flushCache();
        return $created;
    }

    // ********************************* //
    // Import / export

    /**
     * Every role of one season (or all seasons) as data for a roles file, in tree order. Roles
     * point at their circle by the id they have in the file, and carry a lineage number so
     * the same role in several seasons (even renamed) stays one role when imported. Holders
     * are left out: they are roster rows, which differ from one site to the next.
     */
    public function exportRoles(?int $season = null): array
    {
        global $wpdb;
        $seasons = $season ? [$season] : array_map('intval', $wpdb->get_col("SELECT DISTINCT season FROM {$wpdb->prefix}mf_roles ORDER BY season"));
        $lineage_of = [];
        foreach ($this->lineages() as $lineage => $rows) {
            foreach ($rows as $row) {
                $lineage_of[$row['id']] = $lineage;
            }
        }

        $export = ['format' => self::EXPORT_FORMAT, 'version' => self::EXPORT_VERSION, 'exported' => wp_date('Y-m-d'), 'seasons' => (object) []];
        foreach ($seasons as $year) {
            $roles = [];
            foreach ($this->getRoles($year) as $role) {
                $roles[] = [
                    'id'          => (int) $role['id'],
                    'parent'      => $role['parent_id'],
                    'lineage'     => $lineage_of[(int) $role['id']] ?? (int) $role['id'],
                    'name'        => $role['name'],
                    'description' => (string) $role['description'],
                    'permissions' => $role['permissions'],
                    'sort_order'  => (int) $role['sort_order'],
                ];
            }
            if ($roles) {
                $export['seasons']->{(string) $year} = $roles;
            }
        }
        return json_decode(wp_json_encode($export), true); // plain arrays, as an import reads them back
    }

    /** importRoles() for the text of a roles file. */
    public function importRolesFromJson(string $json, bool $dry_run = false): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('That file isn\'t valid JSON.');
        }
        return $this->importRoles($data, $dry_run);
    }

    /**
     * Brings the roles of an export into this site, season by season. A role is matched by its
     * name and circle (the path, e.g. Placement › Circle Lead) within its season: a match gets
     * the file's description, access, order and circle, anything else is created. Roles the
     * file doesn't mention are left alone, and holders are never touched. Roles that share a
     * lineage number in the file become one role across seasons. The whole file is checked
     * first (an InvalidArgumentException says what's wrong), so a bad file changes nothing.
     * With $dry_run nothing is written. Returns the counts, in total and per season.
     */
    public function importRoles(array $data, bool $dry_run = false): array
    {
        $plan = $this->parseImport($data);
        $summary = ['dry_run' => $dry_run, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'seasons' => []];
        $joins = [];
        $fake_id = 0;

        foreach ($plan as $season => $roles) {
            $existing = [];
            foreach ($this->getRoles($season) as $role) {
                $existing[strtolower($role['path'])][] = $role;
            }
            $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0];
            $ids = [];
            $paths = [];

            foreach ($roles as $role) {
                $parent = $role['parent'] === null ? 0 : $ids[$role['parent']];
                $path = ($role['parent'] === null ? '' : $paths[$role['parent']] . ' › ') . $role['name'];
                $paths[$role['id']] = $path;
                $data = [
                    'name' => $role['name'], 'description' => $role['description'], 'permissions' => $role['permissions'],
                    'sort_order' => $role['sort_order'], 'parent_id' => $parent,
                ];

                $match = !empty($existing[strtolower($path)]) ? array_shift($existing[strtolower($path)]) : null;
                if ($match) {
                    $ids[$role['id']] = (int) $match['id'];
                    $same = wp_kses_post($role['description']) === (string) $match['description']
                        && $role['permissions'] === array_values(array_intersect(array_keys(self::AREAS), $match['permissions']))
                        && (int) $role['sort_order'] === (int) $match['sort_order']
                        && $parent === (int) $match['parent_id'];
                    if ($same) {
                        $counts['unchanged']++;
                    } else {
                        $counts['updated']++;
                        $dry_run || $this->upsertRole($data, (int) $match['id']);
                    }
                } else {
                    $counts['created']++;
                    $ids[$role['id']] = $dry_run ? --$fake_id : $this->upsertRole($data + ['season' => $season]);
                }
                if ($role['lineage'] !== null) {
                    $joins[$role['lineage']][] = $ids[$role['id']];
                }
            }
            foreach ($counts as $what => $n) {
                $summary[$what] += $n;
            }
            $summary['seasons'][$season] = $counts;
        }

        if (!$dry_run) {
            foreach ($joins as $members) {
                foreach (array_slice($members, 1) as $id) {
                    $this->setLineage($id, $members[0]);
                }
            }
            self::flushCache();
        }
        return $summary;
    }

    /**
     * Checks a roles file and returns its roles per season (ascending), each with its circle
     * before it, as [name, description, permissions, sort_order, id, parent, lineage].
     */
    private function parseImport(array $data): array
    {
        if (($data['format'] ?? '') !== self::EXPORT_FORMAT || !isset($data['seasons']) || !is_array($data['seasons'])) {
            throw new \InvalidArgumentException('That isn\'t a Camp Manager roles export.');
        }
        if ((int) ($data['version'] ?? 0) !== self::EXPORT_VERSION) {
            throw new \InvalidArgumentException('That roles export is from a different version of Camp Manager.');
        }

        $plan = [];
        foreach ($data['seasons'] as $year => $list) {
            $year = (int) $year;
            if ($year < 2000 || $year > 2100 || !is_array($list)) {
                throw new \InvalidArgumentException('The roles file has a season that isn\'t a year.');
            }
            $roles = [];
            foreach (array_values($list) as $i => $role) {
                $where = "Season $year, role " . ($i + 1);
                $name = is_array($role) && is_string($role['name'] ?? null) ? trim($role['name']) : '';
                if ($name === '') {
                    throw new \InvalidArgumentException("$where: a role needs a name.");
                }
                if (!isset($role['id']) || !is_int($role['id']) || isset($roles[$role['id']])) {
                    throw new \InvalidArgumentException("$where ($name): every role needs its own numeric id.");
                }
                $roles[$role['id']] = [
                    'id'          => $role['id'],
                    'parent'      => isset($role['parent']) ? (int) $role['parent'] : null,
                    'lineage'     => isset($role['lineage']) ? (int) $role['lineage'] : null,
                    'name'        => $name,
                    'description' => is_string($role['description'] ?? null) ? $role['description'] : '',
                    'permissions' => array_values(array_intersect(array_keys(self::AREAS), (array) ($role['permissions'] ?? []))),
                    'sort_order'  => (int) ($role['sort_order'] ?? 0),
                ];
            }

            // Circles before the roles inside them; a circle that is missing or loops is an error.
            $ordered = [];
            while ($roles) {
                $before = count($roles);
                foreach ($roles as $id => $role) {
                    if ($role['parent'] === null || isset($ordered[$role['parent']])) {
                        $ordered[$id] = $role;
                        unset($roles[$id]);
                    }
                }
                if (count($roles) === $before) {
                    throw new \InvalidArgumentException("Season $year has roles inside a circle that isn't in the file, or circles inside each other (" . implode(', ', array_column($roles, 'name')) . ').');
                }
            }
            $plan[$year] = array_values($ordered);
        }
        ksort($plan);
        return $plan;
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
