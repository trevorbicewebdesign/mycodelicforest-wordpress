<?php

class CampManagerLedgerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-ledger.php');
        
    }

    public function testSaveLedger()
    {
        $ledger_id = $this->tester->haveInDatabase('wp_mf_ledger', [
            'amount' => 50.00,
            'note' => 'Initial Ledger Entry',
            'date' => '2023-09-01',
        ]);

        $CampManagerLedger = $this->make('CampManagerLedger', []);
        
        $data = [
            'ledger_id' => $ledger_id,
            'amount' => 100.00,
            'note' => 'Test Ledger Entry',
            'date' => '2023-10-01',
            'line_items' => [
                (object)[
                    'id' => 0,
                    'note' => 'Item 1',
                    'amount' => 350.00,
                    'receipt_id' => null,
                    'type' => 'Camp Dues'
                ],
                (object)[
                    'id' => 0,
                    'note' => 'Item 2',
                    'amount' => 250.00,
                    'receipt_id' => null,
                    'type' => 'Camp Dues'
                ]
            ]
        ];

        codecept_debug( $data );

        $results = $CampManagerLedger->saveLedger($data);

        codecept_debug($results);

    }

    public function testSaveLedgerLineItems()
    {
        $ledger_id = $this->tester->haveInDatabase('wp_mf_ledger', [
            'amount' => 50.00,
            'note' => 'Initial Ledger Entry',
            'date' => '2023-09-01',
        ]);

        $CampManagerLedger = $this->make('CampManagerLedger', []);
        
        $data = [
            (object)[
                'id' => 0,
                'note' => 'Item 1',
                'amount' => 350.00,
                'receipt_id' => null,
                'type' => 'Camp Dues'
            ],
            (object)[
                'id' => 0,
                'note' => 'Item 2',
                'amount' => 250.00,
                'receipt_id' => null,
                'type' => 'Camp Dues'
            ]
        ];

        codecept_debug( $data );

        $results = $CampManagerLedger->saveLedgerLineItems($ledger_id, $data);

        codecept_debug($results);

    }

}