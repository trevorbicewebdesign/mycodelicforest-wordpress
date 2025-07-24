<?php

class CampManagerShortcodes
{

    private $receipts;
    private $roster;
    private $core;
    public function __construct( CampManagerCore $CampManagerCore, CampManagerReceipts $CampManagerReceipts, CampManagerRoster $CampManagerRoster)
    {
       $this->core = $CampManagerCore;
       $this->receipts = $CampManagerReceipts;
       $this->roster = $CampManagerRoster;
    }

    public function init()
    {
        // need a custom shortcode for displaying the roster
        add_shortcode('camp_manager_roster', [$this, 'displayRoster']);
        add_shortcode('camp_manager_expenses', [$this, 'displayExpenses']);
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

        $output = '<table class="camp-manager-roster" style="width: 100%; border-collapse: collapse;">';
        $headers = [
            '',
            'Name',
            'Dues Paid',
        ];
        $output .= '<tr>';
        foreach ($headers as $header) {
            $output .= '<th>' . esc_html($header) . '</th>';
        }
        $output .= '</tr>';

        foreach ($roster as $member) {
            $output .= '<tr>';
            // Add a counter for the first column
            $output .= '<td>' . esc_html($member['id']) . '</td>';

            $name = '';
            if (!empty($member['playaname'])) {
            $name = esc_html($member['playaname']) . ' (' . esc_html($member['fname'] . ' ' . $member['lname']) . ')';
            } else {
            $name = esc_html($member['fname'] . ' ' . $member['lname']);
            }
            $output .= '<td>' . $name . '</td>';

            $output .= '<td>' . ($member['fully_paid'] ? 'Yes' : 'No') . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';

        return $output;
    }

    public function displayExpenses($atts = [])
    {
        // Accept 'season' as a shortcode attribute
        $atts = shortcode_atts([
            'season' => ''
        ], $atts, 'camp_manager_expenses');

        $expenses = "";

        if (empty($expenses)) {
            return '<p>No expenses found.</p>';
        }

        $categories = $this->core->getItemCategories();
        $expenses .= '<table class="camp-manager-expenses" style="width: 100%; border-collapse: collapse;">';
        $expenses .= '<tr>';
        $expenses .= '<th>Category</th>';
        $expenses .= '<th>Description</th>';
        $expenses .= '<th>Total</th>';

        foreach ($categories as $category) {
            $expenses .= '<tr>';
            $expenses .= '<td>' . esc_html($category['name']) . '</td>';
            $expenses .= '<td>' . esc_html($category['description']) . '</td>';
            $expenses .= '<td>' . esc_html($category['total']) . '</td>';
            $expenses .= '</tr>';
        }

        $expenses .= '</table>';

        return $expenses;
    }
}
?>