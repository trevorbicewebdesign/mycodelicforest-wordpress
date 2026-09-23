<?php

// Camp history: the `camp_year` post type and its fields are defined in ACF
// while the model is still settling. ACF reads/writes them as local JSON in this plugin's
// acf-json/ folder so every change made in the ACF admin UI is versioned with the plugin.
//
// Front end: the child theme's single-camp_year / archive-camp_year templates place the
// ACF blocks in blocks/burn-year-* (facts panel, summary line, details, older/newer nav).
// Each block's render.php calls the public render helpers below with the current post.
class MycodelicForestHistory {

    public function __construct() {

    }

    public function init() {
        add_filter('acf/settings/save_json', [$this, 'acfJsonPath']);
        add_filter('acf/settings/load_json', [$this, 'acfLoadJsonPaths']);
        add_action('init', [$this, 'registerBlocks']);
        add_action('pre_get_posts', [$this, 'showAllYearsOnArchive']);
        add_action('acf/save_post', [$this, 'syncPostDateToYear'], 20);
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
        foreach (['facts', 'summary', 'details', 'nav'] as $block) {
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

    // "Aug 25 – Sep 2, 2024" from the Ymd values ACF stores.
    public function formatDates($post_id) {
        $start = DateTime::createFromFormat('Ymd', $this->field($post_id, 'burn_start'));
        $end   = DateTime::createFromFormat('Ymd', $this->field($post_id, 'burn_end'));
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
        if (!$this->didNotBurn($post_id) && ($address = $this->field($post_id, 'placement_address'))) {
            $parts[] = $address;
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
            'Placement'   => $this->field($post_id, 'placement_address'),
            'Lot size'    => $this->field($post_id, 'lot_size'),
            'Camp size'   => $this->field($post_id, 'camp_population'),
            'Camp leads'  => $this->field($post_id, 'camp_leads'),
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

        $offerings = $this->field($post_id, 'camp_offerings');
        if ($offerings !== '') {
            $html .= '<section class="mf-burn-details__section"><h3>What We Offered</h3>' . wp_kses_post(wpautop($offerings)) . '</section>';
        }

        $listing = $this->field($post_id, 'guide_listing');
        $url     = $this->field($post_id, 'guide_url');
        if ($listing !== '' || $url !== '') {
            $html .= '<section class="mf-burn-details__section"><h3>In the Guide</h3>';
            if ($listing !== '') {
                $html .= '<blockquote class="mf-burn-details__listing">' . wp_kses_post(wpautop($listing)) . '</blockquote>';
            }
            if ($url !== '') {
                $html .= '<p><a href="' . esc_url($url) . '">Directory listing &rarr;</a></p>';
            }
            $html .= '</section>';
        }

        return $html === '' ? '' : '<div class="mf-burn-details">' . $html . '</div>';
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
