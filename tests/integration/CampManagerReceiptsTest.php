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

        $CampManagerReceipts = $this->make('CampManagerReceipts', []);

        $results = $CampManagerReceipts->insert_receipt(
            '2023-10-01', // receipt_date (string)
            'Test Receipt', // receipt_description (string)
            100.00, // receipt_amount (float)
            0.00, // fill in with appropriate value or null if optional
            0.00, // fill in with appropriate value or null if optional
            0.00, // fill in with appropriate value or null if optional
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
    }

}


