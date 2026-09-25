<?php

/**
 * Season context for Camp Manager.
 *
 * - current():  the season the camp is running now (public pages, new records). Set on the
 *               dashboard with "Start season"; defaults to the latest season on file.
 * - selected(): the season an admin is looking at. Defaults to current(); an admin can browse
 *               an archived season with the switcher, and the choice sticks (user meta) so
 *               list pages and the forms that save from them agree.
 *
 * Older seasons are never deleted. Every roster, ledger, receipt, budget category and camp role
 * row carries a season; budget items follow their category, ledger line items their ledger row
 * and role holders their role.
 */
class CampManagerSeason
{
    const DB_VERSION = 6;
    // Everything recorded before seasons existed belongs to the 2025 season.
    const LEGACY_SEASON = 2025;
    const OPTION_CURRENT = 'camp_manager_season';
    /** First season of the roles added in db version 6 (the camp did not attend 2020 or 2026). */
    const ROLES_SINCE = 2022;
    const OPTION_DB_VERSION = 'camp_manager_db_version';
    const USER_META_VIEWING = 'camp_manager_viewing_season';
    const SWITCH_PARAM = 'camp_manager_switch_season';
    // Pages that can show every season at once (the ledger) take ?season_view=all.
    const VIEW_ALL_PARAM = 'season_view';

    private static $season_tables = ['mf_roster', 'mf_ledger', 'mf_receipts', 'mf_budget_category'];
    // Tables that only contribute to available(); they never had pre-season rows to backfill.
    private static $newer_season_tables = ['mf_roles'];

    public function init()
    {
        add_action('init', [__CLASS__, 'upgrade']);
        add_action('admin_init', [__CLASS__, 'handleSwitch']);
        add_action('admin_post_camp_manager_start_season', [__CLASS__, 'handleStartSeason']);
    }

    public static function current(): int
    {
        $season = (int) get_option(self::OPTION_CURRENT);
        if ($season) {
            return $season;
        }

        global $wpdb;
        $latest = (int) $wpdb->get_var("SELECT MAX(season) FROM {$wpdb->prefix}mf_roster");
        return $latest ?: (int) gmdate('Y');
    }

    public static function selected(): int
    {
        // Only wp-admin honours the viewing choice; public pages always show the current season.
        if (is_admin() && get_current_user_id()) {
            $viewing = (int) get_user_meta(get_current_user_id(), self::USER_META_VIEWING, true);
            if ($viewing && in_array($viewing, self::available(), true)) {
                return $viewing;
            }
        }
        return self::current();
    }

    /** True when the admin asked this page to show every season together. */
    public static function viewingAll(): bool
    {
        return is_admin() && isset($_GET[self::VIEW_ALL_PARAM]) && $_GET[self::VIEW_ALL_PARAM] === 'all';
    }

    /** SQL condition limiting $column to the viewed season, or always-true when viewing all. */
    public static function whereSeason(string $column = 'season'): string
    {
        global $wpdb;
        return self::viewingAll() ? '1=1' : $wpdb->prepare("$column = %d", self::selected());
    }

    /** Every season that has data, plus the current one, newest first. */
    public static function available(): array
    {
        global $wpdb;
        $seasons = [self::current()];
        foreach (array_merge(self::$season_tables, self::$newer_season_tables) as $table) {
            $name = $wpdb->prefix . $table;
            // Skips tables that don't exist yet, or don't have the column until upgrade() has run.
            if (!$wpdb->get_var("SHOW COLUMNS FROM $name LIKE 'season'")) {
                continue;
            }
            $seasons = array_merge($seasons, array_map('intval', $wpdb->get_col("SELECT DISTINCT season FROM $name WHERE season IS NOT NULL")));
        }
        $seasons = array_values(array_unique(array_filter($seasons)));
        rsort($seasons);
        return $seasons;
    }

    /**
     * Brings the tables up to DB_VERSION, once: version 2 adds the season columns and files
     * pre-season data under the legacy season; version 3 adds the camp role tables; version 4
     * gives every past season a Camp Lead role; version 5 adds role lineages (the same role
     * across seasons, even when renamed); version 6 adds the roles the camp has had since 2022
     * (Circle Lead, Programming Director) or that Burning Man now requires (Leave No Trace,
     * Sustainability and R.I.D.E. Leads) to every season from 2022 on, and adds circles (roles
     * inside roles), starting the current season's roles off in the camp's circles.
     *
     * Only the missing columns are added (no dbDelta over every table), and a database lock
     * lets exactly one request do it: right after a deploy WP-CLI and web requests all hit
     * init at once, and concurrent ALTER TABLEs would queue up on metadata locks. The lock is
     * released by MySQL if the process dies, so it can't get stuck.
     */
    public static function upgrade()
    {
        if (self::upgraded()) {
            return;
        }

        global $wpdb;
        $lock = $wpdb->prefix . 'camp_manager_upgrade';
        if (!(int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock))) {
            return; // another request is upgrading; the tables are usable once it finishes
        }

        try {
            // Someone else may have finished while we were waiting for the lock.
            wp_cache_delete(self::OPTION_DB_VERSION, 'options');
            if (self::upgraded()) {
                return;
            }
            $version = (int) get_option(self::OPTION_DB_VERSION);

            if ($version < 2) {
                foreach (['mf_ledger', 'mf_receipts', 'mf_budget_category'] as $table) {
                    $name = $wpdb->prefix . $table;
                    if (!$wpdb->get_var("SHOW COLUMNS FROM $name LIKE 'season'")) {
                        $wpdb->query("ALTER TABLE $name ADD COLUMN season int DEFAULT NULL");
                    }
                }
                foreach (self::$season_tables as $table) {
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}$table SET season = %d WHERE season IS NULL", self::LEGACY_SEASON));
                }
            }

            if ($version < 3) {
                // Camp roles: new tables, started off with the roles from the old /camp-roles/ page.
                require_once CAMPMANAGER_CORE_ABS_PATH . 'classes/class-install.php';
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                $installer = new CampManagerInstall();
                $installer->create_mf_roles_table();
                $installer->create_mf_role_members_table();
                if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mf_roles")) {
                    (new CampManagerRoles())->seedDefaultRoles(self::current());
                }
            }

            if ($version < 4) {
                // The camp has always had a Camp Lead: give every season one so Burn Year
                // pages can name that year's leads. Holders are assigned by hand.
                (new CampManagerRoles())->ensureLeadRoleEverySeason();
            }

            if ($version < 5) {
                // Role lineage: rows of the same role across seasons share a lineage_id, so a
                // renamed role (Goblin -> Treasurer) keeps its history. Same-named roles are
                // joined here; renames are joined by hand on the role's edit page.
                $name = $wpdb->prefix . 'mf_roles';
                if (!$wpdb->get_var("SHOW COLUMNS FROM $name LIKE 'lineage_id'")) {
                    $wpdb->query("ALTER TABLE $name ADD COLUMN lineage_id int DEFAULT NULL, ADD KEY lineage_id (lineage_id)");
                }
                (new CampManagerRoles())->backfillLineages();
            }

            if ($version < 6) {
                // Circles: a role can sit inside another role (its circle). Then the roles the
                // camp has had since 2022 or that Burning Man requires are added, and the
                // current season is put into the camp's circles. Holders are assigned by hand.
                $name = $wpdb->prefix . 'mf_roles';
                if (!$wpdb->get_var("SHOW COLUMNS FROM $name LIKE 'parent_id'")) {
                    $wpdb->query("ALTER TABLE $name ADD COLUMN parent_id int DEFAULT NULL, ADD KEY parent_id (parent_id)");
                }
                $roles = new CampManagerRoles();
                $roles->addMissingDefaultRolesSince(
                    self::ROLES_SINCE,
                    ['Circle Lead', 'Programming Director', 'Leave No Trace Lead', 'Sustainability Lead', 'R.I.D.E. Lead']
                );
                $roles->applyDefaultStructure(self::current());
            }

            update_option(self::OPTION_DB_VERSION, self::DB_VERSION);
        } finally {
            $wpdb->query($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }

    private static function upgraded(): bool
    {
        return (int) get_option(self::OPTION_DB_VERSION) >= self::DB_VERSION;
    }

    public static function handleSwitch()
    {
        if (!isset($_GET[self::SWITCH_PARAM]) || !current_user_can(CampManagerRoles::CAP_ACCESS)) {
            return;
        }

        $base = remove_query_arg([self::SWITCH_PARAM, self::VIEW_ALL_PARAM]);

        // "All seasons" is a view of this page only; it doesn't change the season used elsewhere.
        if ($_GET[self::SWITCH_PARAM] === 'all') {
            wp_safe_redirect(add_query_arg(self::VIEW_ALL_PARAM, 'all', $base));
            exit;
        }

        $season = (int) $_GET[self::SWITCH_PARAM];
        if (in_array($season, self::available(), true) && $season !== self::current()) {
            update_user_meta(get_current_user_id(), self::USER_META_VIEWING, $season);
        } else {
            delete_user_meta(get_current_user_id(), self::USER_META_VIEWING);
        }

        wp_safe_redirect($base);
        exit;
    }

    public static function handleStartSeason()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('camp_manager_start_season');

        $season = isset($_POST['season']) ? (int) $_POST['season'] : 0;
        if ($season >= 2000 && $season <= 2100) {
            update_option(self::OPTION_CURRENT, $season);
            delete_user_meta(get_current_user_id(), self::USER_META_VIEWING);
        }

        wp_safe_redirect(admin_url('admin.php?page=camp-manager'));
        exit;
    }

    /** Season picker plus an "archived season" notice, for the top of admin pages. */
    public static function renderSwitcher(bool $allowAll = false)
    {
        $all = $allowAll && self::viewingAll();
        $selected = self::selected();
        $current = self::current();
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        ?>
        <form method="get" class="camp-manager-season-switcher" style="display: flex; align-items: center; gap: 8px; margin: 8px 0 12px;">
            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>">
            <label for="camp-manager-season"><strong>Season:</strong></label>
            <select name="<?php echo esc_attr(self::SWITCH_PARAM); ?>" id="camp-manager-season" onchange="this.form.submit()">
                <?php if ($allowAll): ?>
                    <option value="all" <?php selected($all); ?>>All seasons</option>
                <?php endif; ?>
                <?php foreach (self::available() as $season): ?>
                    <option value="<?php echo esc_attr($season); ?>" <?php selected(!$all && $season === $selected); ?>>
                        <?php echo esc_html($season); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button class="button">Switch</button></noscript>
            <?php if (!$all): ?>
                <span aria-hidden="true" style="color: #a7aaad;">|</span>
                <span class="camp-manager-season-status" style="color: #50575e;"><?php echo $selected === $current ? 'Current' : 'Archived'; ?></span>
            <?php endif; ?>
        </form>
        <?php if ($all): ?>
            <div class="notice notice-info inline"><p>Showing <strong>all seasons</strong>. New entries are saved to the season you were last viewing (<?php echo (int) $selected; ?>).</p></div>
        <?php elseif ($selected !== $current): ?>
            <div class="notice notice-warning inline camp-manager-archived-notice"><p>
                You are viewing the archived <strong><?php echo (int) $selected; ?></strong> season.
                New entries will be saved to <strong><?php echo (int) $selected; ?></strong>.
                The current season is <strong><?php echo (int) $current; ?></strong>.
                <a href="<?php echo esc_url(add_query_arg(self::SWITCH_PARAM, $current, remove_query_arg([self::SWITCH_PARAM, self::VIEW_ALL_PARAM]))); ?>">View current season.</a>
            </p></div>
        <?php endif;
    }

    /** Dashboard control to begin a new season; older seasons stay as they are. */
    public static function renderStartSeason()
    {
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 8px 0 16px;">
            <input type="hidden" name="action" value="camp_manager_start_season">
            <?php wp_nonce_field('camp_manager_start_season'); ?>
            <label for="camp-manager-new-season"><strong>Start a new season:</strong></label>
            <input type="number" name="season" id="camp-manager-new-season" class="small-text" min="2000" max="2100"
                value="<?php echo (int) self::current() + 1; ?>">
            <button class="button" onclick="return confirm('Make this the current season? Earlier seasons stay viewable.');">Start season</button>
        </form>
        <?php
    }
}
