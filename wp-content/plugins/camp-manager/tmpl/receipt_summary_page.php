<?php
$receipt_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$receipt = $receipt_id ? $this->receipts->get_receipt($receipt_id) : null;
$is_edit = $receipt !== null;
$receipt_id = $is_edit ? intval($receipt->id) : 0;

$store = $receipt->store ?? '';
$date = $receipt->date ?? '';
$subtotal = $receipt->subtotal ?? '';
$tax = $receipt->tax ?? '';
$shipping = $receipt->shipping ?? '';
$total = $receipt->total ?? '';
$items = $receipt->items ?? [];

$form_action = admin_url('admin-post.php');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Receipt Summary</h1>

    <h3>Total Receipts: $<?php echo esc_html(number_format($this->receipts->get_total_receipts(), 2)); ?></h3>
    <h3>Total Money In: $<?php echo esc_html(number_format($this->ledger->totalMoneyIn(), 2)); ?></h3>
    <h3>Expected Money In: $<?php echo esc_html(number_format($this->roster->expectedCampDuesRevenue(), 2)); ?></h3>
    <h3>Total Donations: $<?php echo esc_html(number_format($this->ledger->totalDonations(), 2)); ?></h3>
    <h3>Total Assets Sold: $<?php echo esc_html(number_format($this->ledger->totalAssetsSold(), 2)); ?></h3>
    <h3>Total Expected Revenue: <?php  echo esc_html(number_format($this->roster->expectedCampDuesRevenue() + $this->ledger->totalDonations() + $this->ledger->totalAssetsSold(), 2)); ?></h3>
    <h3>Remaining Camp Dues: $<?php echo esc_html(number_format($this->roster->totalUnpaidCampDues()), 2); ?></h3>
    <?php
    // get all the categories at the top so they're available for both chart and table
    $categories = $this->core->getItemCategories();
    ?>

    <div id="piechart" style="width: 100%; height: 500px;"></div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Category', 'Amount'],
            <?php
            $rows = [];
            foreach ($categories as $category) {
                $total = $this->receipts->get_total_receipts_by_category($category['id']);
                $rows[] = "['" . str_replace('&amp;', '&', $category['name'])   . " $" . number_format($total, 2) . "', " . floatval($total) . "]";
            }
            echo implode(",\n            ", $rows);
            ?>
        ]);
        var options = {
        title: 'Camp Budget Breakdown'
        };
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
    }
    </script>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>Category</th>
                <th>Receipts</th>
                <th>Must Have</th>
                <th>Remaining Must Have</th>
                <th>Diff Remaining</th>
                <th>Should Have</th>
                <th>Remaining Should Have</th>                
            </tr>
        </thead>
        <tbody>
            <?php
            $total_receipts = 0;
            $total_budget = 0;
            foreach ($categories as $category) {
                $actual = $this->receipts->get_total_receipts_by_category($category['id']);
                $remaining = $this->budgets->get_remaining_budget_by_category($category['id'], 1);
                $remaining_should_have = $this->budgets->get_remaining_budget_by_category($category['id'], 2);
                $must_have = $this->budgets->getPriorityTotal($category['id'], 1);
                $total_should_have += $this->budgets->getPriorityTotal($category['id'], 2) + $must_have;
                $total_remaining_should_have += $remaining_should_have;
                $total_remaining += $remaining;
                $total_receipts += $actual;
                $total_must_have += $must_have;
            }
            $total_diff = $total_must_have - $total_receipts;
            // Output total row
            echo '<tr style="font-weight:bold;background:#f9f9f9">';
            echo '<td>Total</td>';
            echo '<td>$' . esc_html(number_format($total_receipts, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_must_have, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_remaining, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_diff, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_should_have, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_remaining_should_have, 2)) . '</td>';
            echo '</tr>';

            // Output category rows
            foreach ($categories as $category) {
                $actual = $this->receipts->get_total_receipts_by_category($category['id']);
                $must_have = $this->budgets->getPriorityTotal($category['id'], 1);
                $remaining = $this->budgets->get_remaining_budget_by_category($category['id'], 1);
                $should_have = $this->budgets->getPriorityTotal($category['id'], 2);
                $remaining_should_have = $this->budgets->get_remaining_budget_by_category($category['id'], 2);
                $diff = $must_have - $actual;
                $row_style = '';
                if ($diff < 0) {
                    $row_style = 'style="background-color:#ffdddd;color:#fff;"';
                }
                echo '<tr ' . $row_style . '>';
                $category_url = admin_url('admin.php?page=camp-manager-add-budget-category&id=' . intval($category['id']));
                echo '<td><a href="' . esc_url($category_url) . '">' . esc_html($category['name']) . '</a></td>';
                echo '<td>$' . esc_html(number_format($actual, 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($must_have, 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($remaining, 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($diff, 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($should_have    , 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($remaining_should_have, 2)) . '</td>';
                
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

     
    
    
    
</div>