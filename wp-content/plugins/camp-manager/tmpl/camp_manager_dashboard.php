<?php
$CampManagerLedger = new CampManagerLedger();

?>
<div class="wrap">
    <h1>Camp Manager Dashboard</h1>
    <hr>

    <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
        <div style="flex: 1;">
            <h3>Camp Dues Collected: <?php echo $CampManagerLedger->totalCampDues(); ?></h3>
            <h3>Donations Collected: <?php echo $CampManagerLedger->totalDonations(); ?></h3>
            <h3>Revenue Spent: </h3>
            <h3>PayPal Balance: </h3>
            <h3>Remaining Funds: </h3>
            <h3>Starting Funds: </h3>
            <h3>Unreimbursed Expenses: </h3>
            <hr>
            <h3>Collected Revenue: </h3>
            <h3>Estimated Revenue: </h3>
            <h3>Estimated Fund Remaining: </h3>
            <h3>Unallocated Funds: </h3>
            <hr/>
            <h3>Camp Dues: 350.00</h3>
            <h3>Low Income Camp Dues: 250.00</h3>
            <h3>Low Income Camp Dues Count: </h3>
            <h3># Camp Members: </h3>
            <h3>Camp Dues Collected: </h3>
            <h3>Camp Dues Remaining: </h3>
            <h3>Low Income Camp Dues Remaining: </h3>
            <h3>Total Camp Dues: </h3>
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