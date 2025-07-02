<?php

echo '<div class="wrap"><h1>Receipts</h1><a href="/wp-admin/admin.php?page=camp-manager-upload-receipt" class="page-title-action">Upload Receipt</a>';

$receipts = $this->get_receipts();

if ($receipts && count($receipts) > 0) {
    echo '<div class="wp-list-table widefat fixed striped posts">';
    echo '<table>';
    echo '<thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary">Store</th>
                <th scope="col" class="manage-column">Date</th>
                <th scope="col" class="manage-column">Subtotal</th>
                <th scope="col" class="manage-column">Tax</th>
                <th scope="col" class="manage-column">Shipping</th>
                <th scope="col" class="manage-column">Total</th>
            </tr>
            </thead>
            <tbody>';
    foreach ($receipts as $receipt) {
        echo '<tr>';
        echo '<td class="column-title has-row-actions column-primary" data-colname="Store">'
            . '<strong>' . esc_html($receipt->store) . '</strong>'
            . '<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>'
            . '</td>';
        echo '<td data-colname="Date">' . esc_html($receipt->date) . '</td>';
        echo '<td data-colname="Subtotal">' . esc_html($receipt->subtotal) . '</td>';
        echo '<td data-colname="Tax">' . esc_html($receipt->tax) . '</td>';
        echo '<td data-colname="Shipping">' . esc_html($receipt->shipping) . '</td>';
        echo '<td data-colname="Total">' . esc_html($receipt->total) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo '<p>No receipts found.</p>';
}
echo '</div>';

?>