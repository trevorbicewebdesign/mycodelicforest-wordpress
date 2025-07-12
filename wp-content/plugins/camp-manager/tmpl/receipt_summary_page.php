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
                $rows[] = "['" . esc_js($category['name']) . " $" . number_format($total, 2) . "', " . floatval($total) . "]";
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
                <th>Diff</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_receipts = 0;
            $total_budget = 0;
            foreach ($categories as $category) {
                $actual = $this->receipts->get_total_receipts_by_category($category['id']);
                $budget = $this->budgets->getPriorityTotal($category['id'], 1);
                $total_receipts += $actual;
                $total_budget += $budget;
            }
            $total_diff = $total_budget - $total_receipts;
            // Output total row
            echo '<tr style="font-weight:bold;background:#f9f9f9">';
            echo '<td>Total</td>';
            echo '<td>$' . esc_html(number_format($total_receipts, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_budget, 2)) . '</td>';
            echo '<td>$' . esc_html(number_format($total_diff, 2)) . '</td>';
            echo '</tr>';

            // Output category rows
            foreach ($categories as $category) {
                $actual = $this->receipts->get_total_receipts_by_category($category['id']);
                $budget = $this->budgets->getPriorityTotal($category['id'], 1);
                $diff = $budget - $actual;
                $row_style = '';
                if ($diff < 0) {
                    $row_style = 'style="background-color:#ffdddd;color:#fff;"';
                }
                echo '<tr ' . $row_style . '>';
                echo '<td>' . esc_html($category['name']) . '</td>';
                echo '<td>$' . esc_html(number_format($actual, 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($budget, 2)) . '</td>';
                echo '<td>$' . esc_html(number_format($diff, 2)) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

     
    
    
    
</div>