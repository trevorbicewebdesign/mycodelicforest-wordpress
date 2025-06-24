<?php

class CampManagerReceiptsTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-receipts.php');
        
    }

    public function testInsertReceipt()
    {
        global $wpdb;
        // commit the  database changes
        $wpdb->query('COMMIT');
        // Activate the plugin if not already activated
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }   

        $CampManagerReceipts = $this->make('CampManagerReceipts', []);

        $results = $CampManagerReceipts->insert_receipt(
            'Test Store', // store (string)
            '2023-10-01', // receipt_date (string)
            100.00, // receipt_description (string)
            1.00, // receipt_amount (float)
            0.00, // fill in with appropriate value or null if optional
            101.00, // fill in with appropriate value or null if optional
            [
                [
                    'name' => 'Test Item 1',
                    'price' => 50.00,
                ],
                [
                    'name' => 'Test Item 2',
                    'price' => 50.00,
                ]
            ]
        );
        codecept_debug($results);

        // Check that the table mf_receipts exists
        $table_name = $wpdb->prefix . 'mf_receipts';
        // $this->assertTrue($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name, 'Table mf_receipts does not exist');
        // Check that the receipt was inserted
        $receipt = $CampManagerReceipts->get_receipt($results['id']);
        codecept_debug($receipt);
        $this->assertNotEmpty($receipt, 'Receipt was not inserted');
        
    }

}


