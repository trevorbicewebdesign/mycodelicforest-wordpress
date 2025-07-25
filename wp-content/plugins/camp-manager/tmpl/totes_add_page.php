<?php

$tote_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tote = $tote_id ? $this->inventory->getTote($tote_id) : null;
$is_edit = $tote !== null;
$tote_id = $is_edit ? intval($tote->id) : 0;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Tote' : 'Add New Tote'; ?></h1>
    <hr/>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="tote-form">
        <input type="hidden" name="action" value="camp_manager_save_tote" id="action-field">
        <?php if ($is_edit): ?>
            <input type="hidden" name="tote_id" value="<?php echo esc_attr($tote_id); ?>">
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th><label for="tote_name">Name</label></th>
                <td>
                    <input type="text" name="tote_name" id="tote_name" class="regular-text" value="<?php echo esc_attr($tote->name ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="tote_weight">Weight</label></th>
                <td>
                    <input type="number" step="0.01" name="tote_weight" id="tote_weight" value="<?php echo esc_attr($tote->weight ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="tote_uid">UID</label></th>
                <td>
                    <input type="text" name="tote_uid" id="tote_uid" class="regular-text" value="<?php echo esc_attr($tote->uid ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="tote_status">Status</label></th>
                <td>
                    <input type="text" name="tote_status" id="tote_status" class="regular-text" value="<?php echo esc_attr($tote->status ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="tote_location">Location</label></th>
                <td>
                    <input type="text" name="tote_location" id="tote_location" class="regular-text" value="<?php echo esc_attr($tote->location ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="tote_size">Size</label></th>
                <td>
                    <select name="tote_size" id="tote_size">
                        <option value="Full" <?php selected($tote->size ?? 'Full', 'Full'); ?>>Full</option>
                        <option value="Half" <?php selected($tote->size ?? 'Full', 'Half'); ?>>Half</option>
                    </select>
                </td>
            </tr>
        </table>

        <div style="display: flex; gap: 10px;">
            <?php submit_button('Save Tote', 'secondary', 'save_tote', false, array('id' => 'save-btn')); ?>
            <?php submit_button('Save & Close Tote', 'primary', 'save_close_tote', false, array('id' => 'save-close-btn')); ?>
            <?php submit_button('Close', 'secondary', 'close_tote', false, array('id' => 'close-btn', 'formnovalidate' => true)); ?>
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
        $('#action-field').val('camp_manager_save_tote');
    });
    $('#save-close-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_and_close_tote');
    });

    // Track initial state for dirty check
    let initialForm = $('#tote-form').serialize();

    // Handle Close button (no form submit, just redirect with prompt)
    $('#close-btn').on('click', function(e) {
        if ($('#tote-form').serialize() !== initialForm) {
            if (!confirm('You have unsaved changes. Are you sure you want to close?')) {
                e.preventDefault();
                return false;
            }
        }
        window.location.href = '<?php echo esc_url(admin_url('admin.php?page=camp-manager-totes')); ?>';
        e.preventDefault();
    });

});
</script>
