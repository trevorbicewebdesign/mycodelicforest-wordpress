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
        <input type="hidden" name="action" value="camp_manager_save_receipt">
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
        <table class="widefat striped" style="margin-bottom: 10px; table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <!-- Hidden row template for JS to clone -->
                <tr id="item-row-template" class="item-row" style="display: none;">
                    <td><input type="text" name="items[__INDEX__][name]" style="width: 100%;" /></td>
                    <td>
                        <select name="items[__INDEX__][category]" style="width: 100%;">
                            <option value="">Please select a category</option>
                            <?php foreach ($this->core->getItemCategories() as $category): ?>
                                <option value="<?php echo esc_attr($category['id']); ?>">
                                    <?php echo esc_html(ucfirst($category['name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="items[__INDEX__][price]" style="width: 100%;" /></td>
                    <td><input type="number" name="items[__INDEX__][quantity]" value="1" style="width: 100%;" /></td>
                    <td><input type="text" name="items[__INDEX__][subtotal]" style="width: 100%;" /></td>
                    <td><input type="text" name="items[__INDEX__][tax]" style="width: 100%;" /></td>
                    <td><input type="text" name="items[__INDEX__][total]" style="width: 100%;" /></td>
                    <td><button type="button" class="remove-item button">Remove</button></td>
                </tr>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $i => $item): ?>
                        <tr class="item-row">
                            <td><input type="text" name="items[<?php echo $i; ?>][name]" value="<?php echo esc_attr($item->name ?? ''); ?>" style="width: 100%;" /></td>
                            <td>
                                <select name="items[<?php echo $i; ?>][category]" style="width: 100%;">
                                    <option value="">Please select a category</option>
                                    <?php foreach ($this->core->getItemCategories() as $category): ?>
                                        <option value="<?php echo esc_attr($category['id']); ?>"
                                            <?php selected(($item->category_id ?? '') === $category['id']); ?>>
                                            <?php echo esc_html(ucfirst($category['name'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="items[<?php echo $i; ?>][price]" value="<?php echo esc_attr($item->price ?? ''); ?>" style="width: 100%;" /></td>
                            <td><input type="number" name="items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr($item->quantity ?? 1); ?>" style="width: 100%;" /></td>
                            <td><input type="text" name="items[<?php echo $i; ?>][subtotal]" value="<?php echo esc_attr($item->subtotal ?? ''); ?>" style="width: 100%;" /></td>
                            <td><input type="text" name="items[<?php echo $i; ?>][tax]" value="<?php echo esc_attr($item->tax ?? ''); ?>" style="width: 100%;" /></td>
                            <td><input type="text" name="items[<?php echo $i; ?>][total]" value="<?php echo esc_attr($item->total ?? ''); ?>" style="width: 100%;" /></td>
                            <td><button type="button" class="remove-item button">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <p><button type="button" id="add-item" class="button">Add Item</button></p>

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
    jQuery(document).ready(function ($) {
        if (typeof ajaxurl === 'undefined') {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        }

        initReceiptForm();

        function initReceiptForm() {
            const $template = $('#item-row-template');
            let rowCount = $('table.widefat tbody tr.item-row').length;

            $('#add-item').on('click', function () {
                addItemRow();
            });

            $(document).on('click', '.remove-item', function () {
                $(this).closest('tr').remove();
            });

            $('#analyze-btn').on('click', function () {
                const fileInput = $('#receipt_image')[0];
                if (!fileInput.files.length) {
                    alert('Please choose an image first.');
                    return;
                }

                const formData = new FormData();
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
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#analyze-spinner').removeClass('is-active');
                        alert('AJAX error: ' + textStatus + ': ' + errorThrown);
                        console.error(jqXHR.responseText);
                    }
                });
            });

            window.populateFormWithReceipt = function (data) {
                $('input[name="store"]').val(data.store ?? '');
                $('input[name="date"]').val(data.date ?? '');
                $('input[name="subtotal"]').val(data.subtotal ?? '');
                $('input[name="tax"]').val(data.tax ?? '');
                $('input[name="shipping"]').val(data.shipping ?? '');
                $('input[name="total"]').val(data.total ?? '');

                const $tbody = $('table.widefat tbody');
                $tbody.find('tr.item-row').remove();

                if (Array.isArray(data.items)) {
                    data.items.forEach((item) => addItemRow(item));
                } else {
                    addItemRow();
                }
            };

            function addItemRow(item = {}) {
                const index = rowCount++;
                const $row = $template.clone().removeAttr('id').show();

                $row.find('[name]').each(function () {
                    const name = $(this).attr('name').replace('__INDEX__', index);
                    $(this).attr('name', name);

                    const match = name.match(/\[([a-z_]+)\]/);
                    const key = match ? match[1] : '';
                    const isSelect = $(this).is('select');

                    $(this).val(item[key] !== undefined ? item[key] : (key === 'quantity' ? 1 : ''));
                });

                $('table.widefat tbody').append($row);
            }
        }
    });
</script>
