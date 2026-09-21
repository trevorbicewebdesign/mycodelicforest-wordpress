<?php

class CampManagerReceiptsTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-receipts.php');
    }

    public function testUpsertAndGetReceipt()
    {
        global $wpdb;
        $wpdb->query('COMMIT');
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }

        // Stub::make() bypasses the constructor (it needs Core + ChatGPT collaborators);
        // upsert_receipt()/get_receipt() only touch $wpdb.
        $receipts = $this->make('CampManagerReceipts', []);

        $items = [
            ['name' => 'Test Item 1', 'price' => 50.00, 'quantity' => 1, 'subtotal' => 50.00, 'total' => 50.00],
            ['name' => 'Test Item 2', 'price' => 25.00, 'quantity' => 2, 'subtotal' => 50.00, 'total' => 50.00],
        ];

        $id = $receipts->upsert_receipt(
            0,             // receipt_id: 0 = insert
            null,          // cmid (roster member), none
            'Test Store',
            '2023-10-01',
            0,             // reimbursed
            100.00,        // subtotal
            0.00,          // tax
            0.00,          // shipping
            100.00,        // total
            $items,
            json_encode(['source' => 'integration-test']), // raw: JSON (json_valid CHECK on prod)
            null           // link
        );
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id, 'upsert_receipt() should return the new receipt id');

        $receipt = $receipts->get_receipt($id);
        $this->assertNotEmpty($receipt, 'Receipt was not inserted');
        $this->assertSame('Test Store', $receipt->store);
        $this->assertEquals(100.00, (float) $receipt->total);
        $this->assertCount(2, $receipt->items, 'Both line items should be stored');

        // Update path: same id, items replaced
        $sameId = $receipts->upsert_receipt($id, null, 'Test Store 2', '2023-10-02', 1, 50.00, 0.00, 0.00, 50.00,
            [['name' => 'Only Item', 'price' => 50.00, 'quantity' => 1, 'subtotal' => 50.00, 'total' => 50.00]], json_encode(['source' => 'update']), null);
        $this->assertSame($id, $sameId);
        $updated = $receipts->get_receipt($id);
        $this->assertSame('Test Store 2', $updated->store);
        $this->assertSame(1, (int) $updated->reimbursed);
        $this->assertCount(1, $updated->items);
    }
}
