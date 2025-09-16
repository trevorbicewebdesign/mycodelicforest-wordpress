<?php

$inventory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$inventory = $inventory_id ? $this->inventory->getInventoryItem($inventory_id) : null;
$is_edit = $inventory !== null;
$inventory_id = $is_edit ? intval($inventory->id) : 0;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Inventory' : 'Add New Inventory'; ?></h1>
    <hr/>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="inventory-form">
        <input type="hidden" name="action" value="camp_manager_save_inventory" id="action-field">
        <?php if ($is_edit): ?>
            <input type="hidden" name="inventory_id" value="<?php echo esc_attr($inventory_id); ?>">
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th><label for="inventory_uuid">UUID</label></th>
                <td>
                    <input type="number" name="inventory_uuid" id="inventory_uuid" class="regular-text" value="<?php echo esc_attr($inventory->uuid ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_name">Name</label></th>
                <td>
                    <input type="text" name="inventory_name" id="inventory_name" class="regular-text" value="<?php echo esc_attr($inventory->name ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="inventory_manufacturer">Manufacturer</label></th>
                <td>
                    <input type="text" name="inventory_manufacturer" id="inventory_manufacturer" class="regular-text" value="<?php echo esc_attr($inventory->manufacturer ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_model">Model</label></th>
                <td>
                    <input type="text" name="inventory_model" id="inventory_model" class="regular-text" value="<?php echo esc_attr($inventory->model ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_description">Description</label></th>
                <td>
                    <input type="text" name="inventory_description" id="inventory_description" class="regular-text" value="<?php echo esc_attr($inventory->description ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_quantity">Quantity</label></th>
                <td>
                    <input type="number" step="1" name="inventory_quantity" id="inventory_quantity" value="<?php echo esc_attr($inventory->quantity ?? '1'); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_photo_id">Photo</label></th>
                <td>
                    <input type="hidden" name="inventory_photo_id" id="inventory_photo_id" value="<?php echo esc_attr($inventory->photo ?? ''); ?>">
                    <button type="button" class="button" id="upload_inventory_image">Select Image</button>
                    <div id="inventory_image_preview" style="margin-top: 10px;">
                        <?php
                        if (!empty($inventory->photo)) {
                            echo wp_get_attachment_image($inventory->photo, 'medium');
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label for="inventory_location">Location</label></th>
                <td>
                    <input type="text" name="inventory_location" id="inventory_location" class="regular-text" value="<?php echo esc_attr($inventory->location ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_weight">Weight</label></th>
                <td>
                    <input type="number" step="0.01" name="inventory_weight" id="inventory_weight" value="<?php echo esc_attr($inventory->weight ?? '0'); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_category">Category</label></th>
                <td>
                    <input type="text" name="inventory_category" id="inventory_category" class="regular-text" value="<?php echo esc_attr($inventory->category ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_category_name">Category Name</label></th>
                <td>
                    <input type="text" name="inventory_category_name" id="inventory_category_name" class="regular-text" value="<?php echo esc_attr($inventory->category_name ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_links">Links</label></th>
                <td>
                    <input type="text" name="inventory_links" id="inventory_links" class="regular-text" value="<?php echo esc_attr($inventory->links ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_amp">Amp</label></th>
                <td>
                    <input type="number" step="0.01" name="inventory_amp" id="inventory_amp" value="<?php echo esc_attr($inventory->amp ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="inventory_set_name">Set Name</label></th>
                <td>
                    <input type="text" name="inventory_set_name" id="inventory_set_name" class="regular-text" value="<?php echo esc_attr($inventory->set_name ?? ''); ?>">
                </td>
            </tr>
        </table>

        <div style="display: flex; gap: 10px;">
            <?php submit_button('Save Inventory', 'secondary', 'save_inventory', false, array('id' => 'save-btn')); ?>
            <?php submit_button('Save & Close Inventory', 'primary', 'save_close_inventory', false, array('id' => 'save-close-btn')); ?>
            <?php submit_button('Close Inventory', 'secondary', 'close_inventory', false, array('id' => 'close-btn', 'formnovalidate' => true)); ?>
        </div>

        <?php
        $return_url = '';
        if (!empty($_REQUEST['return'])) {
            $return_url = $_REQUEST['return'];
        }
        ?>
        <input type="hidden" name="return_url" value="<?php echo esc_attr($return_url); ?>">
    </form>
</div>
<script type="text/javascript">
jQuery(function ($) {
    let mediaUploader;

    $('#upload_inventory_image').on('click', function (e) {
        e.preventDefault();

        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            alert('Media uploader not loaded. Please reload the page.');
            return;
        }

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Inventory Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        mediaUploader.on('select', function () {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#inventory_photo_id').val(attachment.id);
            $('#inventory_image_preview').html('<img src="' + attachment.url + '" style="max-width: 200px;" />');
        });

        mediaUploader.open();
    });
});

jQuery(document).ready(function ($) {
    if (typeof ajaxurl === 'undefined') {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    // Set correct action on button click
    $('#save-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_inventory');
    });
    $('#save-close-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_and_close_inventory');
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
        window.location.href = '<?php echo esc_url(admin_url('admin.php?page=camp-manager-inventory')); ?>';
        e.preventDefault();
    });

});
</script>