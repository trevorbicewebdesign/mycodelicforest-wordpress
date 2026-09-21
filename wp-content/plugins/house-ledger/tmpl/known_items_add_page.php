<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) { wp_die(__('You do not have sufficient permissions to access this page.', 'hl')); }

global $wpdb;
$known_table = "{$wpdb->prefix}hl_known_items";

$known_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$known    = $known_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$known_table} WHERE id = %d", $known_id)) : null;
$is_edit  = $known !== null;
$known_id = $is_edit ? (int) $known->id : 0;

$parent_options = $is_edit
    ? $wpdb->get_results($wpdb->prepare("SELECT id, item_name FROM {$known_table} WHERE id <> %d ORDER BY item_name ASC", $known_id))
    : $wpdb->get_results("SELECT id, item_name FROM {$known_table} ORDER BY item_name ASC");

$categories = $wpdb->get_col("SELECT DISTINCT category FROM {$known_table} WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
$index_url  = admin_url('admin.php?page=house-ledger-known-items');
$return_url = !empty($_REQUEST['return']) ? $_REQUEST['return'] : '';

// IMPORTANT: enable the Media Library modal
wp_enqueue_media();

/**
 * Helper to render a media field.
 * $key   - machine key used in POST and column name, e.g., 'product_label_id'
 * $label - field label, e.g., 'Product Label'
 * $id    - current attachment id (or 0)
 */
function hl_media_field($key, $label, $id = 0) {
    $att_id  = (int) $id;
    $img_url = $att_id ? wp_get_attachment_image_url($att_id, 'thumbnail') : '';
    $img_alt = $att_id ? get_post_meta($att_id, '_wp_attachment_image_alt', true) : '';
    ?>
    <tr>
        <th><label><?php echo esc_html($label); ?></label></th>
        <td>
            <div class="hl-media-field" data-field="<?php echo esc_attr($key); ?>" style="display:flex; gap:12px; align-items:flex-start;">
                <img id="<?php echo esc_attr($key); ?>_preview"
                     src="<?php echo esc_url($img_url ?: ''); ?>"
                     alt="<?php echo esc_attr($img_alt ?: $label); ?>"
                     style="width:80px; height:80px; object-fit:cover; border:1px solid #ccd0d4; border-radius:4px; <?php echo $img_url ? '' : 'display:none;'; ?>">
                <div>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>_id" value="<?php echo esc_attr($att_id ?: 0); ?>">
                    <button type="button" class="button hl-media-select" data-target="<?php echo esc_attr($key); ?>">
                        <?php echo $img_url ? esc_html__('Change image', 'hl') : esc_html__('Select image', 'hl'); ?>
                    </button>
                    <button type="button" class="button-link-delete hl-media-clear" data-target="<?php echo esc_attr($key); ?>" style="margin-left:8px; <?php echo $img_url ? '' : 'display:none;'; ?>">
                        <?php esc_html_e('Clear', 'hl'); ?>
                    </button>
                    <p class="description" style="margin-top:6px;"><?php esc_html_e('Choose from Media Library or upload a new one.', 'hl'); ?></p>
                </div>
            </div>
        </td>
    </tr>
    <?php
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? esc_html__('Edit Known Item', 'hl') : esc_html__('Add Known Item', 'hl'); ?></h1>
    <hr/>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="known-item-form">
        <?php wp_nonce_field('hl_known_item_save', 'hl_nonce'); ?>
        <input type="hidden" name="action" value="house_ledger_save_known_item" id="action-field">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($known_id); ?>">
        <?php endif; ?>

        <table class="form-table" role="presentation">
        <!-- Item Name -->
            <tr>
                <th><label for="hl_item_name"><?php esc_html_e('Item Name', 'hl'); ?></label></th>
                <td>
                    <input type="text" name="item_name" id="hl_item_name" class="regular-text"
                        value="<?php echo esc_attr($known->item_name ?? ''); ?>" required>
                </td>
            </tr>

            <!-- Category + datalist -->
            <tr>
                <th><label for="hl_category"><?php esc_html_e('Category', 'hl'); ?></label></th>
                <td>
                    <input list="hl_category_list" type="text" name="category" id="hl_category" class="regular-text"
                        value="<?php echo esc_attr($known->category ?? ''); ?>">
                    <?php if (!empty($categories)) : ?>
                        <datalist id="hl_category_list">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo esc_attr($cat); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    <?php endif; ?>
                    <p class="description">
                        <?php esc_html_e('Optional. Pick an existing category or type a new one.', 'hl'); ?>
                    </p>
                </td>
            </tr>

            <!-- Barcode -->
            <tr>
                <th><label for="hl_barcode"><?php esc_html_e('Barcode', 'hl'); ?></label></th>
                <td>
                    <input type="text" name="barcode" id="hl_barcode" class="regular-text"
                        value="<?php echo esc_attr($known->barcode ?? ''); ?>">
                    <p class="description">
                        <?php esc_html_e('UPC/EAN or your own code. Optional.', 'hl'); ?>
                    </p>
                </td>
            </tr>

            <!-- Units -->
            <tr>
                <th><label for="hl_units"><?php esc_html_e('Units', 'hl'); ?></label></th>
                <td>
                    <input type="text" name="units" id="hl_units" class="regular-text"
                        value="<?php echo esc_attr($known->units ?? ''); ?>">
                    <p class="description">
                        <?php esc_html_e('e.g., “pack”, “roll”, “lb”, “oz”, etc. Optional.', 'hl'); ?>
                    </p>
                </td>
            </tr>

            <!-- Weight -->
            <tr>
                <th><label for="hl_weight"><?php esc_html_e('Weight', 'hl'); ?></label></th>
                <td>
                    <input type="text" name="weight" id="hl_weight" class="regular-text"
                        value="<?php echo esc_attr($known->weight ?? ''); ?>">
                    <p class="description">
                        <?php esc_html_e('Optional. Free text (e.g., “16 oz”, “1.2 lb”).', 'hl'); ?>
                    </p>
                </td>
            </tr>

            <!-- Has Expiration -->
            <tr>
                <th><?php esc_html_e('Has Expiration', 'hl'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="has_expiration" value="1"
                            <?php checked(isset($known->has_expiration) ? (int)$known->has_expiration : 0, 1); ?>>
                        <?php esc_html_e('This item typically has an expiration date.', 'hl'); ?>
                    </label>
                </td>
            </tr>

            <!-- Parent Item -->
            <tr>
                <th><label for="hl_parent"><?php esc_html_e('Parent Item', 'hl'); ?></label></th>
                <td>
                    <select name="parent" id="hl_parent">
                        <option value=""><?php esc_html_e('— None —', 'hl'); ?></option>
                        <?php if (!empty($parent_options)) : ?>
                            <?php foreach ($parent_options as $p): ?>
                                <option value="<?php echo esc_attr($p->id); ?>"
                                    <?php selected((int)($known->parent ?? 0), (int)$p->id); ?>>
                                    <?php echo esc_html("{$p->item_name} (ID: {$p->id})"); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Optional. Use if this item is a variant or part of another known item.', 'hl'); ?>
                    </p>
                </td>
            </tr>

            <!-- === New media fields === -->
            <?php
            hl_media_field('product_label_id',   __('Product Label', 'hl'),   $known->product_label_id ?? 0);
            hl_media_field('nutrition_image_id', __('Nutrition Facts', 'hl'), $known->nutrition_image_id ?? 0);
            ?>
        </table>


        <div style="display:flex; gap:10px;">
            <?php submit_button(__('Save Known Item', 'hl'), 'secondary', 'hl_save', false, ['id' => 'save-btn']); ?>
            <?php submit_button(__('Save & Close Known Item', 'hl'), 'primary', 'hl_save_close', false, ['id' => 'save-close-btn']); ?>
            <?php submit_button(__('Close', 'hl'), 'secondary', 'close_known', false, ['id' => 'close-btn', 'formnovalidate' => true]); ?>
        </div>

        <input type="hidden" name="return_url" value="<?php echo esc_attr($return_url); ?>">
    </form>
</div>

<script type="text/javascript">
jQuery(function ($) {
    // Keep your existing button wiring
    $('#save-btn').on('click', function () {
        $('#action-field').val('house_ledger_save_known_item');
    });
    $('#save-close-btn').on('click', function () {
        $('#action-field').val('house_ledger_save_and_close_known_item');
    });

    // Close button dirty check
    const $form = $('#known-item-form');
    const initial = $form.serialize();
    $('#close-btn').on('click', function (e) {
        if ($form.serialize() !== initial) {
            if (!confirm('<?php echo esc_js(__('You have unsaved changes. Close without saving?', 'hl')); ?>')) {
                e.preventDefault();
                return false;
            }
        }
        window.location.href = '<?php echo esc_url($index_url); ?>';
        e.preventDefault();
    });

    // === Media field logic (re-usable) ===
    function openMediaFrame(targetKey) {
        const frame = wp.media({
            title: '<?php echo esc_js(__('Select an image', 'hl')); ?>',
            library: { type: 'image' },
            button: { text: '<?php echo esc_js(__('Use this image', 'hl')); ?>' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            const $id = $('#' + targetKey + '_id');
            const $img = $('#' + targetKey + '_preview');
            const $clear = $('.hl-media-clear[data-target="'+targetKey+'"]');
            const $select = $('.hl-media-select[data-target="'+targetKey+'"]');

            $id.val(attachment.id);
            const url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
            $img.attr('src', url).show();
            $clear.show();
            $select.text('<?php echo esc_js(__('Change image', 'hl')); ?>');
        });

        frame.open();
    }

    $(document).on('click', '.hl-media-select', function (e) {
        e.preventDefault();
        openMediaFrame($(this).data('target'));
    });

    $(document).on('click', '.hl-media-clear', function (e) {
        e.preventDefault();
        const key = $(this).data('target');
        $('#' + key + '_id').val('0');
        $('#' + key + '_preview').hide().attr('src', '');
        $(this).hide();
        $('.hl-media-select[data-target="'+key+'"]').text('<?php echo esc_js(__('Select image', 'hl')); ?>');
    });
});
</script>
