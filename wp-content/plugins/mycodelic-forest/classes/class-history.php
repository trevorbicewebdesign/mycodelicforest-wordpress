<?php

// Camp history: the `camp_year` post type and its fields are defined in ACF
// while the model is still settling. ACF reads/writes them as local JSON in this plugin's
// acf-json/ folder so every change made in the ACF admin UI is versioned with the plugin.
//
// Front end: the child theme's single-camp_year / archive-camp_year templates place the
// ACF blocks in blocks/burn-year-* (facts panel, summary line, details, older/newer nav).
// Each block's render.php calls the public render helpers below with the current post.
class MycodelicForestHistory {

    // The block templates (blocks/*/render.php) render through this instance.
    public static $instance;

    public function __construct() {
        self::$instance = $this;
    }

    public function init() {
        add_filter('acf/settings/save_json', [$this, 'acfJsonPath']);
        add_filter('acf/settings/load_json', [$this, 'acfLoadJsonPaths']);
        add_action('init', [$this, 'registerBlocks']);
        add_action('pre_get_posts', [$this, 'showAllYearsOnArchive']);
        add_action('acf/save_post', [$this, 'syncPostDateToYear'], 20);
        add_action('admin_bar_menu', [$this, 'addEditRosterLink'], 81);

        // Members only: pages, REST, search and the core sitemap.
        add_action('template_redirect', [$this, 'restrictToMembers']);
        add_filter('rest_pre_dispatch', [$this, 'restrictRest'], 10, 3);
        add_filter('register_post_type_args', [$this, 'hideFromSearch'], 10, 2);
        add_filter('wp_sitemaps_post_types', [$this, 'removeFromSitemap']);
        add_filter('sgg_sitemap_exclude_post_ids', [$this, 'excludeFromSitemapPlugin']);
    }

    // Camp history is for members: the Mycodelic Forest Member role (as on the members-only
    // roster pages) or anyone who can edit content.
    public function isMember() {
        if (!is_user_logged_in()) {
            return false;
        }
        $user = wp_get_current_user();
        return in_array('mycodelic_forest_member', (array) $user->roles, true) || current_user_can('edit_posts');
    }

    public function restrictToMembers() {
        if (!is_singular('camp_year') && !is_post_type_archive('camp_year')) {
            return;
        }
        if ($this->isMember()) {
            nocache_headers();
            return;
        }
        if (!is_user_logged_in()) {
            wp_safe_redirect(wp_login_url(home_url(add_query_arg([]))));
            exit;
        }
        wp_die(
            'The camp history is only available to Mycodelic Forest members.',
            'Members only',
            ['response' => 403, 'back_link' => true]
        );
    }

    public function restrictRest($result, $server, $request) {
        if (strpos($request->get_route(), '/wp/v2/camp_year') === 0 && !$this->isMember()) {
            return new WP_Error('rest_forbidden', 'Members only.', ['status' => is_user_logged_in() ? 403 : 401]);
        }
        return $result;
    }

    public function hideFromSearch($args, $post_type) {
        if ($post_type === 'camp_year') {
            $args['exclude_from_search'] = true;
        }
        return $args;
    }

    public function removeFromSitemap($post_types) {
        unset($post_types['camp_year']);
        return $post_types;
    }

    // "XML Sitemap Generator for Google" lists every public custom post type unless it is
    // switched off in its settings; keep the Burn Year URLs out of it regardless.
    public function excludeFromSitemapPlugin($ids) {
        $years = get_posts(['post_type' => 'camp_year', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids']);
        return array_merge((array) $ids, $years);
    }

    // Toolbar "Edit Roster" on a Burn Year page: opens camp-manager's roster with that
    // year's season selected (its switcher param stores the admin's viewing season).
    public function addEditRosterLink($wp_admin_bar) {
        if (is_admin() || !is_singular('camp_year') || !current_user_can('manage_options') || !class_exists('CampManagerSeason')) {
            return;
        }
        $year = (int) $this->field(get_queried_object_id(), 'burn_year');
        if (!$year) {
            return;
        }
        $wp_admin_bar->add_node([
            'id'    => 'mf-edit-roster',
            'title' => '<span class="ab-icon dashicons dashicons-groups" style="top:2px"></span>Edit Roster',
            'href'  => admin_url('admin.php?page=camp-manager-members&' . CampManagerSeason::SWITCH_PARAM . '=' . $year),
            'meta'  => ['title' => "Edit the $year roster in Camp Manager"],
        ]);
    }

    public function acfJsonPath() {
        return MYCO_CORE_ABS_PATH . 'acf-json';
    }

    public function acfLoadJsonPaths($paths) {
        $paths[] = $this->acfJsonPath();
        return $paths;
    }

    // A Burn Year's post date is Jan 1 of its year, so date order is year order everywhere
    // (the /history/ archive, previous/next links, admin list).
    public function syncPostDateToYear($post_id) {
        if (!is_numeric($post_id) || get_post_type($post_id) !== 'camp_year') {
            return;
        }
        $year = (int) get_post_meta($post_id, 'burn_year', true);
        if ($year < 1986) {
            return;
        }
        $date = sprintf('%04d-01-01 00:00:00', $year);
        if (get_post_field('post_date', $post_id) === $date) {
            return;
        }
        remove_action('acf/save_post', [$this, 'syncPostDateToYear'], 20);
        wp_update_post([
            'ID'            => $post_id,
            'post_date'     => $date,
            'post_date_gmt' => get_gmt_from_date($date),
            'edit_date'     => true,
        ]);
        add_action('acf/save_post', [$this, 'syncPostDateToYear'], 20);
    }

    // /history/ shows every year on one page (newest first: the default date order).
    public function showAllYearsOnArchive($query) {
        if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('camp_year')) {
            $query->set('posts_per_page', 100);
        }
    }

    // ACF blocks (ACF PRO). The shared stylesheet is each block's `style` handle, so it loads
    // wherever one of the blocks renders, in the editor too.
    public function registerBlocks() {
        wp_register_style(
            'mycodelic-forest-history',
            plugins_url('assets/css/history.css', MYCO_CORE_PLUGIN_FILE),
            [],
            MYCO_CORE_VERSION
        );
        foreach (['facts', 'summary', 'details', 'neighbors', 'leads', 'roster', 'nav'] as $block) {
            register_block_type(MYCO_CORE_ABS_PATH . 'blocks/burn-year-' . $block);
        }
    }

    // Raw meta rather than get_field() so the templates still render if ACF is off.
    private function field($post_id, $name) {
        return trim((string) get_post_meta($post_id, $name, true));
    }

    private function didNotBurn($post_id) {
        return (bool) get_post_meta($post_id, 'did_not_burn', true);
    }

    // "$350 · $200 low income" (either may be blank).
    private function dues($post_id) {
        $money = function ($value) {
            return '$' . number_format((float) $value, fmod((float) $value, 1) ? 2 : 0);
        };
        $regular = $this->field($post_id, 'dues_regular');
        $low     = $this->field($post_id, 'dues_low_income');
        $parts   = [];
        if ($regular !== '') {
            $parts[] = $money($regular);
        }
        if ($low !== '') {
            $parts[] = $money($low) . ' low income';
        }
        return implode(' · ', $parts);
    }

    // "3:00 & E · Avenue": the address plus what the camp fronts on (either may be blank).
    private function placement($post_id) {
        $parts = array_filter([
            $this->field($post_id, 'placement_address'),
            ucfirst($this->field($post_id, 'placement_street_type')),
        ]);
        return implode(' · ', $parts);
    }

    // "Aug 25 – Sep 2, 2024" from the Ymd values ACF stores.
    // Pass 'playa' for the camp's on-playa span (playa_start / playa_end) instead of the burn.
    public function formatDates($post_id, $prefix = 'burn') {
        $start = DateTime::createFromFormat('Ymd', $this->field($post_id, $prefix . '_start'));
        $end   = DateTime::createFromFormat('Ymd', $this->field($post_id, $prefix . '_end'));
        if (!$start && !$end) {
            return '';
        }
        if ($start && $end) {
            $start_format = $start->format('Y') === $end->format('Y') ? 'M j' : 'M j, Y';
            return $start->format($start_format) . ' – ' . $end->format('M j, Y');
        }
        return ($start ?: $end)->format('M j, Y');
    }

    public function renderSummary($post_id) {
        $parts = [];
        if ($this->didNotBurn($post_id)) {
            $parts[] = 'No burn this year';
        } elseif ($dates = $this->formatDates($post_id)) {
            $parts[] = $dates;
        }
        if (!$this->didNotBurn($post_id) && ($placement = $this->placement($post_id))) {
            $parts[] = $placement;
        }
        if (!$parts) {
            return '';
        }
        return '<p class="mf-burn-summary">' . esc_html(implode(' · ', $parts)) . '</p>';
    }

    public function renderFacts($post_id) {
        $rows = [
            'Year'        => $this->field($post_id, 'burn_year'),
            'Theme'       => $this->field($post_id, 'theme'),
            'Dates'       => $this->didNotBurn($post_id) ? 'No burn this year' : $this->formatDates($post_id),
            'On playa'    => $this->didNotBurn($post_id) ? '' : $this->formatDates($post_id, 'playa'),
            'Placement'   => $this->placement($post_id),
            'Lot size'    => $this->field($post_id, 'lot_size'),
            'Camp size'   => $this->field($post_id, 'camp_population') ?: (string) (count($this->rosterMembers($post_id)) ?: ''),
            'Dues'        => $this->dues($post_id),
            'Listed as'   => $this->field($post_id, 'guide_camp_name'),
        ];

        $html = '';
        foreach ($rows as $label => $value) {
            if ($value === '') {
                continue;
            }
            $html .= '<div class="mf-burn-facts__row"><dt>' . esc_html($label) . '</dt><dd>' . esc_html($value) . '</dd></div>';
        }
        if ($html === '') {
            return '';
        }
        return '<dl class="mf-burn-facts">' . $html . '</dl>';
    }

    public function renderDetails($post_id) {
        $html = '';

        $notes = $this->field($post_id, 'placement_notes');
        $map   = (int) get_post_meta($post_id, 'placement_map', true);
        if ($notes !== '' || $map) {
            $html .= '<section class="mf-burn-details__section"><h3>Placement</h3>';
            if ($map) {
                $html .= wp_get_attachment_image($map, 'large', false, ['class' => 'mf-burn-details__map']);
            }
            $html .= wp_kses_post(wpautop($notes)) . '</section>';
        }

        return $html === '' ? '' : '<div class="mf-burn-details">' . $html . '</div>';
    }

    /**
     * Confirmed camp-manager roster members for the post's Burn Year (mf_roster.season),
     * sorted by the name shown. Names only: dues, RSVP and email stay in camp-manager.
     *
     * @return array[] Each ['name' => string, 'url' => string|null].
     */
    public function rosterMembers($post_id) {
        global $wpdb;
        static $cache = [];

        $year = (int) $this->field($post_id, 'burn_year');
        if (!$year) {
            return [];
        }
        if (isset($cache[$year])) {
            return $cache[$year];
        }

        $table = $wpdb->prefix . 'mf_roster';
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            return $cache[$year] = [];
        }

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT wpid, fname, lname, playaname FROM $table WHERE season = %d AND status = %s",
            $year,
            'Confirmed'
        ), ARRAY_A);

        return $cache[$year] = $this->namedMembers($rows);
    }

    /**
     * Roster rows as ['name' => "Playa (Real Name)", 'url' => profile link or null], sorted
     * by the name shown.
     */
    private function namedMembers(array $rows) {
        $members = [];
        foreach ($rows as $row) {
            $real  = preg_replace('/\s+/', ' ', trim($row['fname'] . ' ' . $row['lname']));
            $playa = preg_replace('/\s+/', ' ', trim((string) $row['playaname']));
            $name  = $playa !== '' ? $playa . ($real !== '' ? ' (' . $real . ')' : '') : $real;
            if ($name === '') {
                continue;
            }
            $user = !empty($row['wpid']) ? get_user_by('id', (int) $row['wpid']) : false;
            $members[] = [
                'name' => $name,
                'url'  => $user ? home_url('/profile/' . $user->user_nicename . '/') : null,
            ];
        }
        usort($members, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        return $members;
    }

    /**
     * Who held camp-manager's Camp Lead role in the post's Burn Year (mf_roles is per season,
     * so the year is the season). Same shape as rosterMembers().
     */
    public function leads($post_id) {
        $year = (int) $this->field($post_id, 'burn_year');
        if (!$year || !class_exists('CampManagerRoles')) {
            return [];
        }
        return $this->namedMembers((new CampManagerRoles())->leadsForSeason($year));
    }

    /**
     * Neighbor Camps section (ACF repeater `neighbor_camps`: camp_name, camp_url). ACF stores
     * the row count in `neighbor_camps` and each row as `neighbor_camps_{i}_{sub_field}`.
     */
    public function renderNeighbors($post_id) {
        $count = (int) get_post_meta($post_id, 'neighbor_camps', true);
        $items = '';
        for ($i = 0; $i < $count; $i++) {
            $name = $this->field($post_id, "neighbor_camps_{$i}_camp_name");
            $url  = $this->field($post_id, "neighbor_camps_{$i}_camp_url");
            if ($name === '') {
                continue;
            }
            $items .= '<li>' . ($url !== '' ? '<a href="' . esc_url($url) . '">' . esc_html($name) . '</a>' : esc_html($name)) . '</li>';
        }

        $html = '<section class="mf-burn-neighbors"><h2 class="mf-burn-roster__title">Neighbor Camps</h2>';
        if ($items !== '') {
            $html .= '<ul class="mf-burn-roster__list">' . $items . '</ul>';
        }
        return $html . '</section>';
    }

    // Camp Leads section: the year's Camp Lead role holders, assigned in Camp Manager
    // (Camp Roles, or a roster member's Camp Roles field) for that season.
    public function renderLeads($post_id) {
        $leads = $this->leads($post_id);
        $html  = '<section class="mf-burn-leads"><h2 class="mf-burn-roster__title">Camp Leads</h2>';
        if (!$leads) {
            return $html . '<p class="mf-burn-leads__empty">Not recorded for this year.</p></section>';
        }
        $html .= '<ul class="mf-burn-roster__list">';
        foreach ($leads as $lead) {
            $name  = esc_html($lead['name']);
            $html .= '<li>' . ($lead['url'] ? '<a href="' . esc_url($lead['url']) . '">' . $name . '</a>' : $name) . '</li>';
        }
        return $html . '</ul></section>';
    }

    public function renderRoster($post_id) {
        $members = $this->rosterMembers($post_id);
        if (!$members) {
            return '';
        }

        $html = '<section class="mf-burn-roster"><h2 class="mf-burn-roster__title">Roster <span>' . count($members) . '</span></h2><ul class="mf-burn-roster__list">';
        foreach ($members as $member) {
            $name = esc_html($member['name']);
            $html .= '<li>' . ($member['url'] ? '<a href="' . esc_url($member['url']) . '">' . $name . '</a>' : $name) . '</li>';
        }
        return $html . '</ul></section>';
    }

    public function renderNav($post_id) {
        $year  = (int) $this->field($post_id, 'burn_year');
        $older = $year ? $this->adjacentYear($year, '<', 'DESC') : 0;
        $newer = $year ? $this->adjacentYear($year, '>', 'ASC') : 0;

        $link = function ($id, $class, $label) {
            return '<a class="mf-burn-nav__' . $class . '" href="' . esc_url(get_permalink($id)) . '"><span>' . esc_html($label) . '</span>' . esc_html(get_the_title($id)) . '</a>';
        };

        $html  = '<nav class="mf-burn-nav" aria-label="Burn years">';
        $html .= $older ? $link($older, 'older', '← Older') : '<span></span>';
        $html .= '<a class="mf-burn-nav__all" href="' . esc_url(get_post_type_archive_link('camp_year')) . '">All years</a>';
        $html .= $newer ? $link($newer, 'newer', 'Newer →') : '<span></span>';
        return $html . '</nav>';
    }

    private function adjacentYear($year, $compare, $order) {
        $ids = get_posts([
            'post_type'      => 'camp_year',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => $order,
            'date_query'     => [[$compare === '<' ? 'before' : 'after' => ['year' => $year, 'month' => 1, 'day' => 1], 'inclusive' => false]],
        ]);
        return $ids[0] ?? 0;
    }
}
