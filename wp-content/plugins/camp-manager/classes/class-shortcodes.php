<?php

class CampManagerShortcodes
{

    public function __construct()
    {
       
    }

    public function init()
    {
        // need a custom shortcode for displaying the roster
        add_shortcode('camp_manager_roster', [$this, 'displayRoster']);
    }

    public function displayRoster($atts = [], $content = null)
    {
        // Accept 'season' as a shortcode attribute
        $atts = shortcode_atts([
            'season' => ''
        ], $atts, 'camp_manager_roster');

        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_roster';

        // Build query with optional season filter
        if (!empty($atts['season'])) {
            $query = $wpdb->prepare("SELECT * FROM $table_name WHERE season = %s", $atts['season']);
        } else {
            $query = "SELECT * FROM $table_name";
        }

        $roster = $wpdb->get_results($query, ARRAY_A);

        if (empty($roster)) {
            return '<p>No members found.</p>';
        }

        $output = '<table class="camp-manager-roster">';
        $output .= '<tr><th>First Name</th><th>Last Name</th><th>Email</th></tr>';

        foreach ($roster as $member) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($member['fname']) . '</td>';
            $output .= '<td>' . esc_html($member['lname']) . '</td>';
            $output .= '<td>' . esc_html($member['email']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';

        return $output;
    }
}
?>