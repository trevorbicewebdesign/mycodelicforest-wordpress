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

    public function testUpdateLedger()
    {
        $ledger_id = $this->tester->haveInDatabase('wp_mf_ledger', [
            'amount' => 50.00,
            'note' => 'Initial Ledger Entry',
            'date' => '2023-09-01',
        ]);

        $CampManagerLedger = $this->make('CampManagerLedger', []);
        
        $data = [
            'amount' => 100.00,
            'note' => 'Test Ledger Entry',
            'date' => '2023-10-01',
            'line_items' => []
        ];
        
        $CampManagerLedger->updateLedger($ledger_id, $data);

    }

    public function testInsertLedgerLineItem()
    {
        $ledger_id = $this->tester->haveInDatabase('wp_mf_ledger', [
            'amount' => 50.00,
            'note' => 'Initial Ledger Entry',
            'date' => '2023-09-01',
        ]);

        $CampManagerLedger = $this->make('CampManagerLedger', []);
        
        $data = [
            'amount' => 25.00,
            'note' => 'Test Line Item',
            'date' => '2023-10-01',
            'receipt_id' => null,
            'type' => 'Expense'
        ];
        
        $CampManagerLedger->insertLedgerLineItem($ledger_id, $data);
    }

}