<?php

$tote_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tote_code = isset($_GET['tote_code']) ? sanitize_text_field($_GET['tote_code']) : '';
if($tote_id) {
    $tote = $this->inventory->getTote($tote_id);
} else if ($tote_code) {
    $tote = $this->inventory->getToteByCode($tote_code);
} else {
    $tote = null;
}
$is_edit = $tote !== null;
$tote_id = $is_edit ? intval($tote->id) : 0;
?>
<?php CampManagerInventory::pageStyles(); ?>
<style>
    .cm-tote-edit { display: flex; gap: 32px; flex-wrap: wrap; align-items: flex-start; }
    .cm-tote-edit > div { flex: 1 1 420px; min-width: 0; }
    .cm-tote-edit .form-table { margin-top: 0; }
    .cm-tote-edit h2 { margin: 0 0 8px; }
    .cm-tote-edit .cm-tote-items-actions { margin: 0 0 12px; }
    .cm-tote-edit .cm-tote-items-actions .page-title-action { margin-left: 0; }
    .cm-tote-edit .column-inventory_name { width: 46%; }
</style>
<div class="wrap cm-list-page">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Tote' : 'Add New Tote'; ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=camp-manager-totes')); ?>" class="page-title-action">All totes</a>
    <hr class="wp-header-end">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="tote-form">
        <input type="hidden" name="action" value="camp_manager_save_tote" id="action-field">
        <?php if ($is_edit): ?>
            <input type="hidden" name="tote_id" value="<?php echo esc_attr($tote_id); ?>">
        <?php endif; ?>
        <div class="cm-tote-edit">
            <div>
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
            </div>
            <input type="hidden" name="return_url" value="<?php echo esc_url( base64_encode( admin_url('admin.php?page=camp-manager-tote-inventory') ) ); ?>">
            </form>
            <div>
                <h2>Packed in this tote</h2>
                <?php
                // A tote that is not saved yet has nothing packed and nowhere to add items to.
                $table = new CampManagerToteInventoryTable($is_edit ? $tote_id : false, $this->inventory);
                $table->process_bulk_action();
                $table->prepare_items();
                $back_here = base64_encode(admin_url('admin.php?page=camp-manager-add-tote&id=' . $tote_id));
                ?>
                <?php if ($is_edit): ?>
                    <p class="cm-tote-items-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=camp-manager-add-tote-inventory&tote_id=' . $tote_id . '&return=' . $back_here)); ?>" class="page-title-action">Add item to tote</a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=camp-manager-add-inventory&return=' . $back_here)); ?>" class="page-title-action">New inventory item</a>
                    </p>
                <?php else: ?>
                    <p class="description">Save the tote first, then add items to it.</p>
                <?php endif; ?>
                <form method="post">
                    <?php
                    $table->display();
                    ?>
                    <?php
                    $return_url = '';
                    if (!empty($_REQUEST['return'])) {
                        $return_url = base64_decode(sanitize_text_field($_REQUEST['return']));
                    }
                    ?>
                    <input type="hidden" name="return_url" value="<?php echo esc_url( base64_encode( $return_url ? $return_url : admin_url('admin.php?page=camp-manager-add-tote&id=' . $tote_id) ) ); ?>">
                </form>
            </div>
        </div>
    
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
        var returnUrl = '';
        var returnField = $('input[name="return_url"]').val();
        if (returnField) {
            try {
            returnUrl = atob(returnField);
            } catch (e) {
            returnUrl = atob('<?php echo base64_encode(admin_url('admin.php?page=camp-manager-totes')); ?>');
        }
    } else {
        returnUrl = atob('<?php echo base64_encode(admin_url('admin.php?page=camp-manager-totes')); ?>');
        }
        window.location.href = returnUrl;
        e.preventDefault();
    });

});
</script>
