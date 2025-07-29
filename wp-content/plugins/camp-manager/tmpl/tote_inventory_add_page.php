<?php

$tote_inventory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$inventory = $tote_inventory_id ? $this->inventory->getToteInventoryItem($tote_inventory_id) : null;
$is_edit = $inventory !== null;
$tote_inventory_id = $is_edit ? intval($inventory->id) : 0;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Tote Inventory' : 'Add New Tote Inventory'; ?></h1>
    <hr/>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="tote-inventory-form">
        <input type="hidden" name="action" value="camp_manager_save_tote_inventory" id="action-field">
        <?php if ($is_edit): ?>
            <input type="hidden" name="tote_inventory_id" value="<?php echo esc_attr($tote_inventory_id); ?>">
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th><label for="inventory_id">Inventory Item</label></th>
                <td>
                    <select name="inventory_id" id="inventory_id" required>
                        <?php
                        $inventory_items = $this->inventory->getInventoryItems();
                        foreach ($inventory_items as $item) {
                            $selected = ($inventory->inventory_id ?? '') == $item->id ? 'selected' : '';
                            echo '<option value="' . esc_attr($item->id) . '" ' . $selected . '>' . esc_html($item->name) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="tote_id">Tote</label></th>
                <td>
                    <select name="tote_id" id="tote_id" required>
                        <?php
                        $totes = $this->inventory->getAllTotes();
                        foreach ($totes as $tote) {
                            $selected = (($inventory->tote_id ?? '') == $tote->id || (isset($_GET['tote_id']) && intval($_GET['tote_id']) == $tote->id)) ? 'selected' : '';
                            echo '<option value="' . esc_attr($tote->id) . '" ' . $selected . '>' . esc_html($tote->name) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="quantity">Quantity</label></th>
                <td>
                    <input type="number" step="1" min="1" name="quantity" id="quantity" value="<?php echo esc_attr($inventory->quantity ?? '1'); ?>" required>
                </td>
            </tr>
        </table>

        <div style="display: flex; gap: 10px;">
            <?php submit_button('Save Tote Inventory', 'secondary', 'save_receipt', false, array('id' => 'save-btn')); ?>
            <?php submit_button('Save & Close Tote Inventory', 'primary', 'save_close_receipt', false, array('id' => 'save-close-btn')); ?>
            <?php submit_button('Close', 'secondary', 'close_receipt', false, array('id' => 'close-btn', 'formnovalidate' => true)); ?>
        </div>
    </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    if (typeof ajaxurl === 'undefined') {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    // Set correct action on button click
    $('#save-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_tote_inventory');
    });
    $('#save-close-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_and_close_tote_inventory');
    });

    // Track initial state for dirty check
    let initialForm = $('#tote-inventory-form').serialize();

    // Handle Close button (no form submit, just redirect with prompt)
    $('#close-btn').on('click', function(e) {
        if ($('#tote-inventory-form').serialize() !== initialForm) {
            if (!confirm('You have unsaved changes. Are you sure you want to close?')) {
                e.preventDefault();
                return false;
            }
        }
        window.location.href = '<?php echo esc_url(admin_url('admin.php?page=camp-manager-inventory')); ?>';
        e.preventDefault();
    });

});
</script>
