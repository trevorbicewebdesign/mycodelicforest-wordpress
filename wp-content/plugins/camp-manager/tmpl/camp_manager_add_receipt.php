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
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Receipt' : 'Upload Receipt'; ?></h1>

    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($form_action); ?>" id="receipt-form">
        <input type="hidden" name="action"
            value="<?php echo $is_edit ? 'camp_manager_save_receipt' : 'camp_manager_upload_and_save_receipt'; ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="receipt_id" value="<?php echo esc_attr($receipt_id); ?>">
        <?php endif; ?>
        <input type='hidden' name='raw' value='<?php echo esc_attr($receipt->raw ?? '{}'); ?>'>

        <table class="form-table">
            <tr>
                <th><label for="receipt_image">Receipt Image</label></th>
                <td>
                    <input type="file" name="receipt_image" accept="image/*" id="receipt_image">
                    <button type="button" id="analyze-btn" class="button">Analyze Receipt</button>
                    <span id="analyze-spinner" class="spinner" style="float: none;"></span>
                </td>
            </tr>
            <tr>
                <th><label for="store">Store</label></th>
                <td><input type="text" name="store" class="regular-text"
                        value="<?php echo esc_attr(stripslashes($store)); ?>"></td>
            </tr>
            <tr>
                <th><label for="date">Date</label></th>
                <td>
                    <input type="text" name="date"
                        value="<?php echo esc_attr($date ? date('m/d/Y', strtotime($date)) : ''); ?>"
                        placeholder="mm/dd/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                </td>
            </tr>
        </table>

        <h2 style="margin-top: 40px;">Items</h2>
        <table class="widefat striped" style="margin-bottom: 30px; table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: right;">Item Name</th>
                    <th style="text-align: right;">Category</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Qty</th>
                    <th style="text-align: right;">Subtotal</th>

                </tr>
            </thead>
            <tbody>
                <?php
                // Always show at least one row if adding a new receipt (no items)
                if (empty($items)) {
                    $items = [
                        (object) [
                            'name' => '',
                            'category_id' => '',
                            'price' => '',
                            'quantity' => 1,
                            'subtotal' => ''
                        ]
                    ];
                }
                foreach ($items as $i => $item): ?>
                    <tr>
                        <td style="text-align: right;">
                            <input type="text" name="items[<?php echo $i; ?>][name]"
                                value="<?php echo esc_attr($item->name ?? ''); ?>" style="width: 100%;" />
                        </td>
                        <td style="text-align: right;">
                            <?php $categories = $this->core->getItemCategories(); ?>
                            <select name="items[<?php echo $i; ?>][category]" style="width: 100%;">
                                <option value="">Please select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo esc_attr($category['id']); ?>" <?php selected(($item->category_id ?? '') === $category['id']); ?>>
                                        <?php echo esc_html(ucfirst($category['name'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td style="text-align: right;">
                            <input type="text" name="items[<?php echo $i; ?>][price]"
                                value="<?php echo esc_attr($item->price ?? ''); ?>" style="width: 100%;" />
                        </td>
                        <td style="text-align: right;">
                            <input type="number" name="items[<?php echo $i; ?>][quantity]"
                                value="<?php echo esc_attr($item->quantity ?? 1); ?>" style="width: 100%;" />
                        </td>
                        <td style="text-align: right;">
                            <input type="text" name="items[<?php echo $i; ?>][subtotal]"
                                value="<?php echo esc_attr($item->subtotal ?? ''); ?>" style="width: 100%;" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="items-wrapper"></div>


        <!-- Totals -->
        <table style="width: 100%; max-width: 600px; margin-left: auto; font-size: 1.1em;">
            <tr>
                <td style="text-align: right; padding: 8px;"><strong>Subtotal:</strong></td>
                <td style="text-align: right; width: 150px;">
                    <input type="text" name="subtotal" value="<?php echo esc_attr($subtotal ?? ''); ?>"
                        class="small-text" style="width: 100%;" />
                </td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 8px;"><strong>Tax:</strong></td>
                <td style="text-align: right;">
                    <input type="text" name="tax" value="<?php echo esc_attr($tax ?? ''); ?>" class="small-text"
                        style="width: 100%;" />
                </td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 8px;"><strong>Shipping:</strong></td>
                <td style="text-align: right;">
                    <input type="text" name="shipping" value="<?php echo esc_attr($shipping ?? ''); ?>"
                        class="small-text" style="width: 100%;" />
                </td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 8px;"><strong>Total:</strong></td>
                <td style="text-align: right;">
                    <input type="text" name="total" value="<?php echo esc_attr($total ?? ''); ?>" class="small-text"
                        style="width: 100%;" />
                </td>
            </tr>
        </table>


        <?php submit_button($is_edit ? 'Update Receipt' : 'Save Receipt'); ?>
    </form>
</div>
<script type="text/javascript">
    // Ensure ajaxurl is defined for AJAX requests
    if (typeof ajaxurl === 'undefined') {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    jQuery(document).ready(function ($) {
        $('#analyze-btn').on('click', function () {
            var fileInput = $('#receipt_image')[0];
            if (!fileInput.files.length) {
                alert('Please choose an image first.');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'camp_manager_analyze_receipt');
            formData.append('receipt_image', fileInput.files[0]);

            $('#analyze-spinner').addClass('is-active');

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function (res) {
                    $('#analyze-spinner').removeClass('is-active');
                    if (res.success) {
                        populateFormWithReceipt(res.data);
                    } else {
                        alert('Error: ' + res.data);
                    }
                },
                error: function () {
                    $('#analyze-spinner').removeClass('is-active');
                    alert('Something went wrong.');
                }
            });
        });

        function populateFormWithReceipt(data) {
            $('input[name="store"]').val(data.store || '');
            $('input[name="date"]').val(data.date || '');
            $('input[name="subtotal"]').val(data.subtotal || '');
            $('input[name="tax"]').val(data.tax || '');
            $('input[name="shipping"]').val(data.shipping || '');
            $('input[name="total"]').val(data.total || '');

            const wrapper = $('#items-wrapper');
            wrapper.empty();

            if (Array.isArray(data.items)) {
                data.items.forEach((item, index) => {
                    wrapper.append(renderItemRow(item, index));
                });
            }
        }

        function renderItemRow(item, index) {
            return `
        <div class="item-row">
            <input type="text" name="items[${index}][name]" value="${item.name || ''}" />
            <input type="number" name="items[${index}][price]" value="${item.price || 0}" />
            <input type="number" name="items[${index}][quantity]" value="${item.quantity || 1}" />
            <input type="number" name="items[${index}][subtotal]" value="${item.subtotal || 0}" />
            <select name="items[${index}][category]">
                <option value="power">Power</option>
                <option value="sojourner">Sojourner</option>
                <option value="sound">Sound</option>
                <option value="misc">Misc</option>
            </select>
            <button type="button" class="remove-item">Remove</button>
        </div>`;
        }

        $(document).on('click', '.remove-item', function () {
            $(this).closest('.item-row').remove();
        });

        // Optionally add an "Add Item" button
    });

</script>