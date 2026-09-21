<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hl'));
}

global $wpdb;

$inv_table   = "{$wpdb->prefix}hl_inventory";
$known_table = "{$wpdb->prefix}hl_known_items";

// Are we editing an existing inventory row?
$inv_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$inv = null;

if ($inv_id) {
    $inv = $wpdb->get_row(
        $wpdb->prepare("SELECT id, item_id, item_name, expiration FROM {$inv_table} WHERE id = %d", $inv_id)
    );
    if (!$inv) {
        wp_die(__('Inventory item not found.', 'hl'));
    }
}

// Known items list for the selector
$known_items = $wpdb->get_results("
    SELECT id, item_name, has_expiration
    FROM {$known_table}
    ORDER BY item_name ASC
");

// Destination after “Close” (adjust to your listing page slug)
$index_url = admin_url('admin.php?page=house-ledger-inventory');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $inv ? esc_html__('Edit Inventory Item', 'hl') : esc_html__('Add Inventory Item', 'hl'); ?>
    </h1>
    <hr/>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('hl_inventory_save', 'hl_nonce'); ?>
        <input type="hidden" name="action" value="house_ledger_save_inventory" id="action-field">
        <input type="hidden" name="id" value="<?php echo esc_attr($inv->id ?? ''); ?>">

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="hl_known_item_id"><?php esc_html_e('Known Item (optional)', 'hl'); ?></label></th>
                    <td>
                        <select id="hl_known_item_id" name="item_id">
                            <option value=""><?php esc_html_e('— None —', 'hl'); ?></option>
                            <?php if (!empty($known_items)): ?>
                                <?php foreach ($known_items as $ki): ?>
                                    <option
                                        value="<?php echo esc_attr($ki->id); ?>"
                                        data-name="<?php echo esc_attr($ki->item_name); ?>"
                                        data-hasexp="<?php echo esc_attr((int)$ki->has_expiration); ?>"
                                        <?php selected((int)($inv->item_id ?? 0), (int)$ki->id); ?>
                                    >
                                        <?php echo esc_html($ki->item_name . " (ID: {$ki->id})"); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Link this inventory row to a Known Item. You can still override the name below.', 'hl'); ?>
                        </p>
                        <p>
                            <button type="button" class="button" id="hl-copy-known-name">
                                <?php esc_html_e('Copy name from Known Item', 'hl'); ?>
                            </button>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="hl_item_name"><?php esc_html_e('Item Name', 'hl'); ?></label></th>
                    <td>
                        <input type="text" id="hl_item_name" name="item_name" class="regular-text"
                               value="<?php echo esc_attr($inv->item_name ?? ''); ?>">
                        <p class="description">
                            <?php esc_html_e('Optional. If blank, your UI can fall back to the linked Known Item’s name.', 'hl'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="hl_expiration"><?php esc_html_e('Expiration', 'hl'); ?></label></th>
                    <td>
                        <input
                            type="date"
                            id="hl_expiration"
                            name="expiration"
                            class="regular-text"
                            value="<?php echo esc_attr(!empty($inv->expiration) ? date('Y-m-d', strtotime($inv->expiration)) : ''); ?>"
                        >
                        <p class="description" id="hl-exp-help">
                            <?php esc_html_e('Optional. If the selected Known Item typically expires, consider setting a date.', 'hl'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="display:flex; gap:10px; align-items:center; margin-top: 12px;">
            <?php submit_button(__('Save', 'hl'), 'secondary', 'hl_save', false, array('id' => 'hl-save-btn')); ?>
            <?php submit_button(__('Save & Close', 'hl'), 'primary', 'hl_save_close', false, array('id' => 'hl-save-close-btn')); ?>
            <a class="button" href="<?php echo esc_url($index_url); ?>"><?php esc_html_e('Close', 'hl'); ?></a>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function ($) {
    if (typeof ajaxurl === 'undefined') {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    // Set correct action on button click
    $('#save-btn').on('click', function() {
        $('#action-field').val('house_ledger_save_inventory');
    });
    $('#save-close-btn').on('click', function() {
        $('#action-field').val('house_ledger_save_and_close_inventory');
    });

    // Track initial state for dirty check
    let initialForm = $('#inventory-form').serialize();

    // Handle Close button (no form submit, just redirect with prompt)
    $('#close-btn').on('click', function(e) {
        if ($('#inventory-form').serialize() !== initialForm) {
            if (!confirm('You have unsaved changes. Are you sure you want to close?')) {
                e.preventDefault();
                return false;
            }
        }
        window.location.href = '<?php echo esc_url(admin_url('admin.php?page=house-ledger-inventory')); ?>';
        e.preventDefault();
    });

});
</script>
