<?php

class CampManagerLedgerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-ledger.php');

        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
    }

    public function testGetLedger()
    {
        $ledger_id = $this->tester->haveInDatabase('mf_ledger', [
            'amount' => 50.00,
            'note' => 'Test Ledger Entry',
            'date' => '2023-09-01 00:00:00',
        ]);

        global $wpdb;
        // commit the  database changes
        $wpdb->query('COMMIT');

        codecept_debug("Ledger Id = $ledger_id");

        $CampManagerLedger = $this->make('CampManagerLedger', []);

        $ledger = $CampManagerLedger->getLedger($ledger_id);

        codecept_debug($ledger);

        $this->assertNotEmpty($ledger, 'Ledger entry was not found');
        $this->assertEquals($ledger_id, $ledger->id, 'Ledger ID does not match');
        $this->assertEquals(50.00, $ledger->amount, 'Ledger amount does not match');
        $this->assertEquals('Test Ledger Entry', $ledger->note, 'Ledger note does not match');
        $this->assertEquals('2023-09-01 00:00:00', $ledger->date, 'Ledger date does not match');
        $this->assertIsArray($ledger->line_items, 'Ledger line items should be an array');
        $this->assertCount(0, $ledger->line_items, 'Ledger line items should be empty');
        
    }   

    public function testSaveLedger()
    {
        $ledger_id = $this->tester->haveInDatabase('mf_ledger', [
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
        $ledger_id = $this->tester->haveInDatabase('mf_ledger', [
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

        $ledger = $CampManagerLedger->getLedger($ledger_id);

        codecept_debug($ledger);

    }

}