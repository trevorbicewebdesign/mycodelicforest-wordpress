<?php

class CampManagerShortcodes
{

    private $receipts;
    private $roster;

    private $inventory;
    private $core;
    public function __construct( CampManagerCore $CampManagerCore, CampManagerReceipts $CampManagerReceipts, CampManagerRoster $CampManagerRoster, CampManagerInventory $CampManagerInventory)
    {
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
            $output .= '<td>' . esc_html($member['id']) . '</td>';

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

        $receipt_items = "";

        // Pass the season attribute if get_receipt_items supports it
       
        $receipt_items = $this->receipts->get_receipt_items();
        

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
        $CampManagerRoster = new CampManagerRoster();

        
        $total_camp_dues = $CampManagerLedger->totalCampDues();
        $total_donations = $CampManagerLedger->totalDonations();
        $other_revenue = $CampManagerLedger->totalAssetsSold();
        $total_revenue = $total_camp_dues + $total_donations + $other_revenue;
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
}
?>