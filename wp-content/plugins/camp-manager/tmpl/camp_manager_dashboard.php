<?php
$CampManagerLedger = new CampManagerLedger();
$CampManagerRoster = new CampManagerRoster();

$starting_balance = $CampManagerLedger->startingBalance();

$total_camp_dues = $CampManagerLedger->totalCampDues();
$total_donations = $CampManagerLedger->totalDonations();
$total_revenue = $CampManagerLedger->totalMoneyIn();
$total_expenses = $CampManagerLedger->totalMoneyOut();

$paypal_balance = $starting_banace + $total_revenue - $total_expenses;

$total_camp_members = $CampManagerRoster->countRosterMembers();
$total_low_income_members = $CampManagerRoster->countLowIncomeMembers();
$total_paid_camp_dues = $CampManagerRoster->countPaidCampDues();
$total_paid_low_income_camp_dues = $CampManagerRoster->countPaidLowIncomeCampDues();

$total_unpaid_camp_dues = $CampManagerRoster->countUnpaidCampDues()
?>
<div class="wrap">
    <h1>Camp Manager Dashboard</h1>
    <hr>

    <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
        <div style="flex: 1;">
            <h3>Camp Dues Collected: $<?php echo number_format($total_camp_dues, 2); ?></h3>
            <h3>Donations Collected: $<?php echo number_format($total_donations, 2); ?></h3>
            <h3>Revenue Spent: $<?php echo number_format($total_expenses, 2); ?></h3>
            <h3>PayPal Balance: $<?php echo number_format($paypal_balance + $total_revenue - $total_expenses, 2); ?></h3>
            <h3>Remaining Funds: $<?php echo number_format($total_revenue - $total_expenses, 2); ?></h3>
            <h3>Starting Funds: $<?php echo $starting_balance; ?></h3>
            <h3>Unreimbursed Expenses: </h3>
            <hr>
            <h3>Collected Revenue: <?php echo $total_revenue; ?></h3>
            <h3>Estimated Revenue: <?php echo ($total_low_income_members * 250) + (($total_camp_members - $total_low_income_members) * 350); ?></h3>
            <h3>Estimated Fund Remaining: </h3>
            <h3>Unallocated Funds: </h3>
            <hr/>
            <h3>Camp Dues: 350.00</h3>
            <h3>Low Income Camp Dues: 250.00</h3>
            <h3>Low Income Camp Dues Count: <?php echo $total_low_income_members; ?></h3>
            <h3># Camp Members: <?php echo $total_camp_members; ?></h3>
            <h3>Camp Dues Collected: <?php echo $total_camp_dues; ?></h3>
            <h3>Camp Dues Remaining: <?php echo $total_unpaid_camp_dues; ?></h3>
            <h3>Low Income Camp Dues Remaining: <?php echo $total_paid_low_income_camp_dues; ?></h3>
            <h3>Total Camp Dues: <?php echo $total_paid_camp_dues; ?></h3>
            <h3>Camp Dues Left To Collect: </h3>
        </div>
        <div style="flex: 3;">
            <div id="piechart" style="width: 100%; height: 500px;"></div>
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
                var options = {
                title: 'Camp Budget Breakdown'
                };
                var chart = new google.visualization.PieChart(document.getElementById('piechart'));
                chart.draw(data, options);
            }
            </script>
        </div>
    </div>
</div>