<?php

class CampManagerShortcodes
{

    private $receipts;
    private $roster;

    private $inventory;
    private $core;
    private $roles;
    public function __construct( CampManagerCore $CampManagerCore, CampManagerReceipts $CampManagerReceipts, CampManagerRoster $CampManagerRoster, CampManagerInventory $CampManagerInventory, ?CampManagerRoles $CampManagerRoles = null)
    {
        $this->roles = $CampManagerRoles ?: new CampManagerRoles();
        $this->core = $CampManagerCore;
        $this->receipts = $CampManagerReceipts;
        $this->roster = $CampManagerRoster;
        $this->inventory = $CampManagerInventory;
    }

    public function init()
    {
        // need a custom shortcode for displaying the roster
        add_shortcode('camp_manager_roster', [$this, 'displayRoster']);
        add_shortcode('camp_manager_expenses', [$this, 'displayExpenses']);
        add_shortcode('camp_manager_inventory', [$this, 'displayInventory']);

        add_shortcode('camp_manager_financial_summary', [$this, 'displayFinancialSummary']);
        add_shortcode('camp_manager_actuals_chart', [$this, 'displayActualsChart']);
        add_shortcode('camp_manager_roles', [$this, 'displayRoles']);
    }

    /**
     * Camp roles for a season (the current one by default) with who holds them.
     * [camp_manager_roles season="2027" holders="no"]
     */
    public function displayRoles($atts = [])
    {
        $atts = shortcode_atts([
            'season'  => '',
            'holders' => 'yes',
        ], $atts, 'camp_manager_roles');

        $season = !empty($atts['season']) ? (int) $atts['season'] : CampManagerSeason::current();
        $roles = $this->roles->getRoles($season);
        if (!$roles) {
            return '<p>Camp roles for ' . (int) $season . ' haven\'t been set yet.</p>';
        }

        $show_holders = !in_array(strtolower($atts['holders']), ['no', 'false', '0'], true);
        $output = '<div class="camp-manager-roles">';
        foreach ($roles as $role) {
            $output .= '<div class="camp-manager-role">';
            $output .= '<h3 class="camp-manager-role-name">' . esc_html($role['name']) . '</h3>';
            if ($show_holders) {
                // Dropped holders are no longer doing the job.
                $holders = array_filter($role['members'], function ($member) {
                    return !in_array($member['status'], ['Dropped', 'No'], true);
                });
                $names = array_filter(array_map([CampManagerRoles::class, 'displayName'], $holders));
                $output .= '<p class="camp-manager-role-holders"><em>' . ($names ? esc_html(implode(', ', $names)) : 'Open — ask a camp lead if you\'re interested') . '</em></p>';
            }
            $output .= '<div class="camp-manager-role-description">' . wpautop(wp_kses_post($role['description'])) . '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';

        return $output;
    }

    public function displayRoster($atts = [], $content = null)
    {
        // Accept 'season' as a shortcode attribute
        $atts = shortcode_atts([
            'season' => ''
        ], $atts, 'camp_manager_roster');

        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_roster';
        // Confirmed members of the given season (the current season when none is given)
        $season = !empty($atts['season']) ? (int) $atts['season'] : CampManagerSeason::current();
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE season = %d AND status = %s", $season, 'Confirmed');

        $roster = $wpdb->get_results($query, ARRAY_A);

        if (empty($roster)) {
            return '<p>No members found.</p>';
        }

        $output = '<table class="camp-manager-roster" style="width: 100%; border-collapse: collapse;">';
        $headers = [
            '',
            'Name',
            'Dues Paid',
            'RSVP',
            'Status'
        ];
        $output .= '<tr>';
        foreach ($headers as $header) {
            $output .= '<th>' . esc_html($header) . '</th>';
        }
        $output .= '</tr>';

        foreach ($roster as $member) {
            $output .= '<tr>';
            // Add a counter for the first column
            static $counter = 1;
            $output .= '<td>' . $counter++ . '</td>';

            $name = '';
            if (!empty($member['playaname'])) {
            $name = esc_html($member['playaname']) . ' (' . esc_html($member['fname'] . ' ' . $member['lname']) . ')';
            } else {
            $name = esc_html($member['fname'] . ' ' . $member['lname']);
            }
            $output .= '<td>' . $name . '</td>';

            $output .= '<td>' . ($member['fully_paid'] ? 'Yes' : 'No') . '</td>';
            $output .= '<td>' . ($member['rsvp'] ? 'Yes' : 'No') . '</td>';
            $output .= '<td>' . esc_html($member['status']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';

        return $output;
    }

    public function DisplayCampDuesPayments($atts = [])
    {
        // Accept 'season' as a shortcode attribute
        $atts = shortcode_atts([
            'season' => ''
        ], $atts, 'camp_manager_dues_payments');

        $payments = $this->receipts->getCampDuesPayments($atts['season']);

        if (empty($payments)) {
            return '<p>No camp dues payments found.</p>';
        }

        $output = '<table class="camp-manager-dues-payments" style="width: 100%; border-collapse: collapse;">';
        $output .= '<tr>';
        $output .= '<th>Member</th>';
        $output .= '<th>Amount</th>';
        $output .= '<th>Date</th>';
        $output .= '</tr>';

        foreach ($payments as $payment) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($payment['member_name']) . '</td>';
            $output .= '<td>' . esc_html($payment['amount']) . '</td>';
            $output .= '<td>' . esc_html($payment['date']) . '</td>';
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

        $season = !empty($atts['season']) ? (int) $atts['season'] : CampManagerSeason::current();
        $receipt_items = $this->receipts->get_receipt_items(null, $season);


        if (empty($receipt_items)) {
            return '<p>No expenses found.</p>';
        }

        $output = '<table class="camp-manager-expenses" style="width: 100%; border-collapse: collapse;">';
        $output .= '<tr>';
        $output .= '<th>Expense ID</th>';
        $output .= '<th>Name</th>';
        $output .= '<th>Price</th>';
        $output .= '<th>Quantity</th>';
        $output .= '<th>Tax</th>';
        $output .= '<th>Total</th>';
        $output .= '</tr>';

        $total = 0;

        foreach ($receipt_items as $item) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($item->id) . '</td>';
            $output .= '<td>' . esc_html(stripslashes($item->name)) . '</td>';
            $output .= '<td>$' . esc_html(number_format($item->price ?? 0, 2)) . '</td>';
            $output .= '<td>' . esc_html($item->quantity ?? 1) . '</td>';
            $output .= '<td>$' . esc_html(number_format($item->tax ?? 0, 2)) . '</td>';
            $output .= '<td>$' . esc_html(number_format($item->total, 2)) . '</td>';
            $output .= '</tr>';
            $total += floatval($item->total);
        }

        $output .= '<tr>';
        $output .= '<td colspan="2" style="text-align:right;"><strong>Total</strong></td>';
        $output .= '<td colspan="2"><strong>$' . esc_html(number_format($total, 2)) . '</strong></td>';
        $output .= '</tr>';

        $output .= '</table>';

        return $output;
    }

    public function displayInventory($atts = [])
    {
        $inventory_items = $this->inventory->getInventoryItems();

        if (empty($inventory_items)) {
            return '<p>No inventory items found.</p>';
        }

        $output = '<table class="camp-manager-inventory" style="width: 100%; border-collapse: collapse;">';
        $output .= '<tr>';
        $output .= '<th>ID</th>';
        $output .= '<th>Name</th>';
        $output .= '<th>Manufacturer</th>';
        $output .= '<th>Model</th>';
        $output .= '<th>Quantity</th>';
        $output .= '<th>Location</th>';
        $output .= '</tr>';

        foreach ($inventory_items as $item) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($item->id) . '</td>';
            $output .= '<td>' . esc_html(stripslashes($item->name)) . '</td>';
            $output .= '<td>' . esc_html(stripslashes($item->manufacturer)) . '</td>';
            $output .= '<td>' . esc_html(stripslashes($item->model)) . '</td>';
            $output .= '<td>' . esc_html($item->quantity) . '</td>';
            $output .= '<td>' . esc_html(stripslashes($item->location)) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';

        return $output;
    }

    public function displayFinancialSummary()
    {

        $CampManagerChatGPT = new CampManagerChatGPT($this->core);
        $CampManagerReceipts = new CampManagerReceipts($this->core, $CampManagerChatGPT);
        $CampManagerLedger = new CampManagerLedger($CampManagerReceipts);

        
        $total_camp_dues = $CampManagerLedger->totalCampDues();
        $total_donations = $CampManagerLedger->totalDonations();
        $other_revenue = $CampManagerLedger->totalAssetsSold();
        $total_revenue = $CampManagerLedger->totalMoneyIn();
        $total_expenses = $CampManagerLedger->totalMoneyOut();

        $starting_balance = $CampManagerLedger->startingBalance();
        $paypal_balance = $starting_balance + $total_revenue - $total_expenses;

        $output = '
            <div class="postbox">
            <h2 class="hndle"><span>Financial Summary</span></h2>
            <div class="inside">
                <table class="widefat striped">
                <tbody>
                    <tr>
                    <th>PayPal Balance</th>
                    <td><strong>$' . number_format($paypal_balance, 2) . '</strong></td>
                    </tr>
                    <tr>
                    <th>Starting Funds</th>
                    <td>$' . number_format($starting_balance, 2) . '</td>
                    </tr>
                    <tr>
                    <th>Camp Dues Collected</th>
                    <td>$' . number_format($total_camp_dues, 2) . '</td>
                    </tr>
                    <tr>
                    <th>Donations Collected</th>
                    <td>$' . number_format($total_donations, 2) . '</td>
                    </tr>
                    <tr>
                    <th>Other Revenue</th>
                    <td>$' . number_format($other_revenue, 2) . '</td>
                    </tr>
                    <tr>
                    <th>Total Revenue</th>
                    <td>$' . number_format($total_revenue, 2) . '</td>
                    </tr>
                    <tr>
                    <th>Revenue Spent</th>
                    <td class="danger">$' . number_format($total_expenses, 2) . '</td>
                    </tr>
                    <tr>
                    <th>Funds Remaining</th>
                    <td><strong>$' . number_format($total_revenue - $total_expenses, 2) . '</strong></td>
                    </tr>
                </tbody>
                </table>
            </div>
            </div>
        ';
        return $output;
    }

    public function displayActualsChart()
    {
        $categories = $this->core->getItemCategories();
        $rows = [];
        foreach ($categories as $category) {
            $total = $this->receipts->get_total_receipts_by_category($category['id']);
            $rows[] = "['" . str_replace('&amp;', '&', $category['name']) . " $" . number_format($total, 2) . "', " . floatval($total) . "]";
        }
        $chart_html = '
        <div id="piechart" style="width: 100%; height: 500px;"></div>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ["Category", "Amount"],
                ' . implode(",\n                ", $rows) . '
            ]);
            var options = {
                title: "Camp Spending Breakdown"
            };
            var chart = new google.visualization.PieChart(document.getElementById("piechart"));
            chart.draw(data, options);
        }
        </script>
        ';
        return $chart_html;
    }
}
?>