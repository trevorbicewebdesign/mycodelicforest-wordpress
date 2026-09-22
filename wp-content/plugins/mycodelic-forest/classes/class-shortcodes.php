<?php

class MycodelicForestShortcodes
{
    private $MycodelicForestCiviCRM;
    public function __construct(MycodelicForestCiviCRM $MycodelicForestCiviCRM)
    {
        $this->MycodelicForestCiviCRM = $MycodelicForestCiviCRM;
    }

    public function init()
    {
        add_shortcode('civi_group_contacts', array($this, 'my_civicrm_group_shortcode'));
        add_shortcode('civi_roster_index', array($this, 'rosterIndexShortcode'));
    }

    /**
     * Shortcode: [civi_roster_index]
     *
     * Lists every year that has a roster, newest first. A year comes from either Camp Manager
     * (seasons with confirmed members in mf_roster) or a yearly CiviCRM roster group; Camp
     * Manager wins when both exist. Years without a roster (2020, 2026) simply don't appear.
     * Each links back to the same page with ?roster_year=YYYY. Only years found here can be
     * shown, so the query string can't be used to read other CiviCRM groups.
     */
    public function rosterIndexShortcode($atts)
    {
        $atts = shortcode_atts(array(
            'show_email' => '0',
        ), $atts, 'civi_roster_index');

        $seasons = $this->getCampManagerSeasons();

        $groups = array();
        $notice = '';
        if (function_exists('civicrm_api3')) {
            try {
                $groups = $this->MycodelicForestCiviCRM->getRosterGroups();
            } catch (Exception $e) {
                $notice = '<p>Error fetching rosters: ' . esc_html($e->getMessage()) . '</p>';
            }
        } else {
            $notice = '<p>CiviCRM is not available, so earlier rosters can\'t be listed.</p>';
        }

        // year => title
        $items = array();
        foreach ($groups as $title) {
            $items[substr($title, 0, 4)] = $title;
        }
        foreach ($seasons as $season) {
            $items[(string) $season] = $season . ' Roster';
        }
        krsort($items, SORT_STRING);

        $year = isset($_GET['roster_year']) ? sanitize_text_field(wp_unslash($_GET['roster_year'])) : '';
        if (preg_match('/^\d{4}$/', $year) && isset($items[$year])) {
            $back = '<p><a href="' . esc_url(remove_query_arg('roster_year')) . '">&larr; All rosters</a></p>';
            if (in_array((int) $year, $seasons, true)) {
                return $back . '<h3>' . esc_html($items[$year]) . '</h3>' . do_shortcode('[camp_manager_roster season="' . (int) $year . '"]');
            }
            return $back . $this->my_civicrm_group_shortcode(array(
                'group_id'   => array_search($items[$year], $groups, true),
                'show_email' => $atts['show_email'],
            ));
        }

        $html = $notice;
        if ($items) {
            $html .= '<ul class="civi-roster-index">';
            foreach ($items as $item_year => $title) {
                $html .= '<li><a href="' . esc_url(add_query_arg('roster_year', $item_year, get_permalink())) . '">' . esc_html($title) . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * Seasons that Camp Manager holds a roster for (at least one confirmed member).
     *
     * @return int[]
     */
    private function getCampManagerSeasons()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_roster';

        if (!shortcode_exists('camp_manager_roster') || $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            return array();
        }

        return array_map('intval', $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT season FROM $table WHERE season IS NOT NULL AND status = %s",
            'Confirmed'
        )));
    }

    /**
     * Shortcode: [civi_group_contacts group_id="123"]
     *
     * Displays all contacts from the specified group (by group_id).
     */
    public function my_civicrm_group_shortcode($atts)
    {

        // 1. Parse shortcode attributes
        $atts = shortcode_atts(array(
            'group_id'   => '', // Example usage: [civi_group_contacts group_id="123"]
            'show_email' => '1',
        ), $atts, 'civi_group_contacts');

        // 2. Basic validation
        $group_id = (int) trim($atts['group_id']);
        if (empty($group_id)) {
            return '<p>No group_id specified in shortcode.</p>';
        }

        if (!function_exists('civicrm_api3')) {
            return '<p>CiviCRM is not available.</p>';
        }

        try {
            $contacts   = $this->MycodelicForestCiviCRM->getGroupContacts($group_id);
            $group_name = $this->MycodelicForestCiviCRM->getGroupName($group_id);
        } catch (Exception $e) {
            return '<p>Error fetching contacts: ' . esc_html($e->getMessage()) . '</p>';
        }

        // 5. Construct output HTML
        $html = '<div class="civi-group-contacts">';
        $html .= '<h3>' . esc_html($group_name) . '</h3>';

        if (empty($contacts)) {
            $html .= '<p>No contacts found in this group.</p>';
        } else {
            $html .= '<ul>';
            foreach ($contacts as $contact) {
                $display_name = !empty($contact['display_name']) ? $contact['display_name'] : '(No Name)';
                $email = !empty($contact['email']) ? $contact['email'] : '(No Email)';

                $html .= '<li>';
                $html .= '<strong>' . esc_html($display_name) . '</strong>';
                if (!empty($atts['show_email'])) {
                    $html .= ' &ndash; ' . esc_html($email);
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>'; // .civi-group-contacts

        return $html;
    }

    
}
