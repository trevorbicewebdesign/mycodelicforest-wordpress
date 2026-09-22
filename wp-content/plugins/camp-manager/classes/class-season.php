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
 * Older seasons are never deleted. Every roster, ledger, receipt and budget category row
 * carries a season; budget items follow their category and ledger line items their ledger row.
 */
class CampManagerSeason
{
    const DB_VERSION = 2;
    // Everything recorded before seasons existed belongs to the 2025 season.
    const LEGACY_SEASON = 2025;
    const OPTION_CURRENT = 'camp_manager_season';
    const OPTION_DB_VERSION = 'camp_manager_db_version';
    const USER_META_VIEWING = 'camp_manager_viewing_season';
    const SWITCH_PARAM = 'camp_manager_switch_season';
    // Pages that can show every season at once (the ledger) take ?season_view=all.
    const VIEW_ALL_PARAM = 'season_view';

    private static $season_tables = ['mf_roster', 'mf_ledger', 'mf_receipts', 'mf_budget_category'];

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
        foreach (self::$season_tables as $table) {
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

    /** Adds the season columns (dbDelta) and files pre-season data under the legacy season, once. */
    public static function upgrade()
    {
        if ((int) get_option(self::OPTION_DB_VERSION) >= self::DB_VERSION) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once __DIR__ . '/class-install.php';
        (new CampManagerInstall())->install();

        global $wpdb;
        foreach (self::$season_tables as $table) {
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}$table SET season = %d WHERE season IS NULL", self::LEGACY_SEASON));
        }

        update_option(self::OPTION_DB_VERSION, self::DB_VERSION);
    }

    public static function handleSwitch()
    {
        if (!isset($_GET[self::SWITCH_PARAM]) || !current_user_can('manage_options')) {
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
        <form method="get" style="margin: 8px 0 12px;">
            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>">
            <label for="camp-manager-season"><strong>Season:</strong></label>
            <select name="<?php echo esc_attr(self::SWITCH_PARAM); ?>" id="camp-manager-season" onchange="this.form.submit()">
                <?php if ($allowAll): ?>
                    <option value="all" <?php selected($all); ?>>All seasons</option>
                <?php endif; ?>
                <?php foreach (self::available() as $season): ?>
                    <option value="<?php echo esc_attr($season); ?>" <?php selected(!$all && $season === $selected); ?>>
                        <?php echo esc_html($season . ($season === $current ? ' (current)' : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button class="button">Switch</button></noscript>
        </form>
        <?php if ($all): ?>
            <div class="notice notice-info inline"><p>Showing <strong>all seasons</strong>. New entries are saved to the season you were last viewing (<?php echo (int) $selected; ?>).</p></div>
        <?php elseif ($selected !== $current): ?>
            <div class="notice notice-warning inline"><p>
                You are viewing the archived <strong><?php echo (int) $selected; ?></strong> season. New entries are saved to it.
                The current season is <strong><?php echo (int) $current; ?></strong>.
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
