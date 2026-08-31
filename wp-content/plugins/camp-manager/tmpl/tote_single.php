<?php
// Use tote code from URL, not ID from $_GET
$tote_code = get_query_var('tote_code') ?: ($_GET['tote_code'] ?? '');
$tote = $tote_code ? $this->inventory->getToteByCode($tote_code) : null;

if (!$tote) {
    echo '<div class="wrap"><h2>Tote Not Found</h2><p>No tote matches this code.</p></div>';
    return;
}
// Optionally: fetch inventory items for this tote
$items = $this->inventory->getToteInventoryItems($tote->id); // Adjust this if your method is named differently
?>
<div class="wrap campmanager-tote-view">
    <h1>Tote: <?php echo esc_html($tote->name); ?></h1>
    <table class="form-table">
        <tr>
            <th>Weight</th>
            <td><?php echo esc_html($tote->weight); ?> lbs</td>
        </tr>
        <tr>
            <th>UID</th>
            <td><?php echo esc_html($tote->uid); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo esc_html($tote->status); ?></td>
        </tr>
        <tr>
            <th>Location</th>
            <td><?php echo esc_html($tote->location); ?></td>
        </tr>
        <tr>
            <th>Size</th>
            <td><?php echo esc_html($tote->size); ?></td>
        </tr>
    </table>

    <h2 style="margin-top:2em;">Tote Inventory Items</h2>
    <?php if (!empty($items)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th style="text-align:right;">Quantity</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item->inventory_name); ?></td>
                        <td style="text-align:right;"><?php echo esc_html($item->quantity); ?></td>
                        <td><?php echo esc_html($item->location); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No items in this tote.</p>
    <?php endif; ?>
</div>
