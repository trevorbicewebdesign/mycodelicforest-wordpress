<?php
// Pass the required arguments to CampManagerReceipts constructor
// Replace 'ARG1' and 'ARG2' with the actual values or variables needed
$CampManagerCore = new CampManagerCore();
$CampManagerChatGPT = new CampManagerChatGPT($CampManagerCore);
$CampManagerReceipts = new CampManagerReceipts($CampManagerCore, $CampManagerChatGPT);
$CampManagerLedger = new CampManagerLedger($CampManagerReceipts);
$CampManagerRoster = new CampManagerRoster();

$starting_balance = $CampManagerLedger->startingBalance();

$total_camp_dues = $CampManagerLedger->totalCampDues();
$total_donations = $CampManagerLedger->totalDonations();
$total_revenue = $CampManagerLedger->totalMoneyIn();
$total_expenses = $CampManagerLedger->totalMoneyOut();
$other_revenue = $CampManagerLedger->totalAssetsSold();

$total_unpaid_receipts = $CampManagerReceipts->totalUnpaidReceipts();

$paypal_balance = $starting_balance + $total_revenue - $total_expenses;

$total_camp_members = $CampManagerRoster->countConfirmedRosterMembers();
$total_low_income_members = $CampManagerRoster->countLowIncomeMembers();
$total_regular_members = $total_camp_members - $total_low_income_members;
// Count members who have paid full camp dues

$total_paid_low_income_camp_dues = $CampManagerRoster->countPaidLowIncomeCampDues();
$total_paid_camp_dues = $CampManagerRoster->countPaidCampDues()-$total_paid_low_income_camp_dues;

$total_unpaid_camp_dues = $CampManagerRoster->totalUnpaidCampDues();

$collected_revenue = $total_revenue;
$estimated_revenue = $collected_revenue + $total_unpaid_camp_dues;
$estimated_funds_remaining = $estimated_revenue - $total_expenses;
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Camp Manager Dashboard</h1>
    <hr class="wp-header-end">

    <div class="notice notice-info inline" style="margin-bottom: 20px;">
        <p><strong>Quick Overview</strong>: See all camp financials, dues, and membership at a glance.</p>
    </div>

    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">

        <!-- Left column: Financial Snapshot -->
        <div style="flex: 1 1 350px; min-width: 350px;">
            <div class="postbox">
                <h2 class="hndle"><span>Financial Summary</span></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <th>PayPal Balance</th>
                                <td><strong>$<?php echo number_format($paypal_balance, 2); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Starting Funds</th>
                                <td>$<?php echo number_format($starting_balance, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Camp Dues Collected</th>
                                <td>$<?php echo number_format($total_camp_dues, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Donations Collected</th>
                                <td>$<?php echo number_format($total_donations, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Other Revenue</th>
                                <td>$<?php echo number_format($other_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Total Revenue</th>
                                <td>$<?php echo number_format($total_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Revenue Spent</th>
                                <td class="danger">$<?php echo number_format($total_expenses, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Funds Remaining</th>
                                <td><strong>$<?php echo number_format($total_revenue - $total_expenses, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><span>Membership & Dues</span></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <th>Total Members</th>
                                <td><?php echo $total_camp_members; ?></td>
                            </tr>
                            <tr>
                                <th>Full Dues Members</th>
                                <td><?php echo $total_regular_members; ?> (Paid: <?php echo $total_paid_camp_dues; ?>)</td>
                            </tr>
                            <tr>
                                <th>Low Income Members</th>
                                <td><?php echo $total_low_income_members; ?> (Paid: <?php echo $total_paid_low_income_camp_dues; ?>)</td>
                            </tr>
                            <tr>
                                <th>Camp Dues Remaining</th>
                                <td><?php echo $total_unpaid_camp_dues; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

         <div style="flex: 1 1 350px; min-width: 350px;">
            <div class="postbox">
                <h2 class="hndle"><span>Ledger</span></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <th>Money In</th>
                                <td>$<?php echo number_format($total_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Money Out</th>
                                <td>$<?php echo number_format($total_expenses, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Net Revenue</th>
                                <td>$<?php echo number_format($total_revenue - $total_expenses, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Unpaid Receipts</th>
                                <td>$<?php echo number_format($total_unpaid_receipts, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Remaining</th>
                                <td>$<?php echo number_format($total_revenue - $total_expenses - $total_unpaid_receipts, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


        <!-- Right column: Revenue & Estimates -->
        <div style="flex: 1 1 350px; min-width: 350px;">
            <div class="postbox">
                <h2 class="hndle"><span>Revenue & Estimates</span></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <th>Collected Revenue</th>
                                <td>$<?php echo number_format($collected_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Estimated Revenue</th>
                                <td>$<?php echo number_format($estimated_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Estimated Revenue Remaining</th>
                                <td>$<?php echo number_format($estimated_funds_remaining, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Unallocated Funds</th>
                                <td><!-- Add calculation if needed --></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><span>Dues Breakdown</span></h2>
                <div class="inside">
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Dues Type</th>
                                <th>Count</th>
                                <th>Paid</th>
                                <th>Estimated Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Full Camp Dues ($350)</td>
                                <td><?php echo $total_regular_members;?></td>
                                <td><?php echo $total_paid_camp_dues; ?></td>
                                <td>$<?php echo number_format($total_regular_members * 350, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Low Income Dues ($250)</td>
                                <td><?php echo $total_low_income_members;?></td>
                                <td><?php echo $total_paid_low_income_camp_dues; ?></td>
                                <td>$<?php echo number_format($total_low_income_members * 250, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional: Pie Chart (hidden by default) -->
    <div class="postbox" style="margin-top: 2rem; display: none;">
        <h2 class="hndle"><span>Camp Budget Breakdown</span></h2>
        <div class="inside">
            <div id="piechart" style="width: 100%; height: 350px;"></div>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script>
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Category', 'Amount'],
                    ['Power',      30],
                    ['Sojourner',  20],
                    ['Sound',      15],
                    ['Misc',       35]
                ]);
                var options = {title: 'Camp Budget Breakdown'};
                var chart = new google.visualization.PieChart(document.getElementById('piechart'));
                chart.draw(data, options);
            }
            </script>
        </div>
    </div>
</div>
