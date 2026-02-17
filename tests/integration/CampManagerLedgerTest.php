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

    public function testGetLedgerReturnsNullForMissing()
    {
        $CampManagerLedger = $this->make('CampManagerLedger', []);
        $ledger = $CampManagerLedger->getLedger(9999999); // unlikely to exist
        $this->assertNull($ledger);
    }

    public function testGetLedger()
    {
        $ledger_id = $this->tester->haveInDatabase('mf_ledger', [
            'amount' => 50.00,
            'note' => 'Test Ledger Entry',
            'date' => '2023-09-01 00:00:00',
        ]);
        global $wpdb;
        $wpdb->query('COMMIT');
        $CampManagerLedger = $this->make('CampManagerLedger', []);
        $ledger = $CampManagerLedger->getLedger($ledger_id);

        $this->assertNotEmpty($ledger, 'Ledger entry was not found');
        $this->assertEquals($ledger_id, $ledger->id, 'Ledger ID does not match');
        $this->assertEquals(50.00, $ledger->amount, 'Ledger amount does not match');
        $this->assertEquals('Test Ledger Entry', $ledger->note, 'Ledger note does not match');
        $this->assertEquals('2023-09-01 00:00:00', $ledger->date, 'Ledger date does not match');
        $this->assertIsArray($ledger->line_items, 'Ledger line items should be an array');
        $this->assertCount(0, $ledger->line_items, 'Ledger line items should be empty');
    }

    public function testSaveLedgerInsertAndUpdate()
    {
        $CampManagerLedger = $this->make('CampManagerLedger', []);

        // INSERT
        $data = [
            'ledger_id' => 0,
            'amount' => 111.11,
            'note' => 'Insert Test',
            'date' => '2025-07-16',
            'link' => 'https://camp.org/doc/111',
            'line_items' => [],
        ];
        $ledger_id = $CampManagerLedger->saveLedger($data);
        $this->assertIsNumeric($ledger_id, 'Insert did not return a valid ID');
        $ledger = $CampManagerLedger->getLedger($ledger_id);
        $this->assertEquals('Insert Test', $ledger->note);

        // UPDATE
        $data = [
            'ledger_id' => $ledger_id,
            'amount' => 222.22,
            'note' => 'Update Test',
            'date' => '2025-08-01',
            'link' => 'https://camp.org/doc/222',
            'line_items' => [],
        ];
        $ledger_id2 = $CampManagerLedger->saveLedger($data);
        $this->assertEquals($ledger_id, $ledger_id2, 'Update did not return same ledger_id');
        $ledger = $CampManagerLedger->getLedger($ledger_id);
        $this->assertEquals('Update Test', $ledger->note);
        $this->assertEquals(222.22, $ledger->amount);
        $this->assertEquals('2025-08-01 00:00:00', $ledger->date);
        $this->assertEquals('https://camp.org/doc/222', $ledger->link);
    }

    public function testSaveLedgerLineItems_InsertUpdateDelete()
    {
        $ledger_id = $this->tester->haveInDatabase('mf_ledger', [
            'amount' => 30,
            'note' => 'Line Item Parent',
            'date' => '2025-07-16',
        ]);
        $CampManagerLedger = $this->make('CampManagerLedger', []);
        // Insert items
        $items = [
            (object)[
                'id' => 0,
                'note' => 'Item1',
                'amount' => 15,
                'receipt_id' => null,
                'type' => 'Camp Dues'
            ],
            (object)[
                'id' => 0,
                'note' => 'Item2',
                'amount' => 15,
                'receipt_id' => null,
                'type' => 'Donation'
            ],
        ];
        $CampManagerLedger->saveLedgerLineItems($ledger_id, $items);
        $li = $CampManagerLedger->getLedgerLineItems($ledger_id);
        $this->assertCount(2, $li);

        // Update one, delete one, add one new
        $item1_id = $li[0]->id;
        $items2 = [
            (object)[
                'id' => $item1_id,
                'note' => 'Item1-updated',
                'amount' => 16,
                'receipt_id' => null,
                'type' => 'Camp Dues'
            ],
            (object)[
                'id' => 0,
                'note' => 'Item3',
                'amount' => 25,
                'receipt_id' => null,
                'type' => 'Sold Asset'
            ],
        ];
        $CampManagerLedger->saveLedgerLineItems($ledger_id, $items2);
        $li2 = $CampManagerLedger->getLedgerLineItems($ledger_id);
        $this->assertCount(2, $li2, 'Should have 2 line items after update/delete');
        $notes = array_map(fn($x) => $x->note, $li2);
        $this->assertContains('Item1-updated', $notes);
        $this->assertContains('Item3', $notes);
        $this->assertNotContains('Item2', $notes);
    }

    public function testNormalizeLedgerLineItems()
    {
        $CampManagerLedger = $this->make('CampManagerLedger', []);

        // Happy path
        $ids = [1, 2];
        $notes = ['foo', 'bar'];
        $amounts = ['123.45', '0'];
        $receipt_ids = ['1', '2'];
        $types = ['Donation', ''];
        $result = $CampManagerLedger->normalizeLedgerLineItems($ids, $notes, $amounts, $receipt_ids, $types);
        $this->assertCount(1, $result, 'Should skip empty/irrelevant items');
        $this->assertEquals('foo', $result[0]->note);

        // Edge: Mismatched length, missing values
        $ids = [1];
        $notes = [];
        $amounts = [0];
        $receipt_ids = [];
        $types = [''];
        $result = $CampManagerLedger->normalizeLedgerLineItems($ids, $notes, $amounts, $receipt_ids, $types);
        $this->assertCount(0, $result, 'Should skip items with 0 amount and empty type');
    }

    public function testTotalsAndStartingBalance()
    {
        $CampManagerLedger = $this->make('CampManagerLedger', []);

        // Starting balance
        $this->assertEquals(2037.80, $CampManagerLedger->startingBalance());

        // Add money in/out, various types
        $this->tester->haveInDatabase('mf_ledger', [
            'amount' => 500.00,
            'note' => 'Money In',
            'date' => '2025-07-16',
        ]);
        $this->tester->haveInDatabase('mf_ledger', [
            'amount' => -100.00,
            'note' => 'Money Out',
            'date' => '2025-07-16',
        ]);
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => 1,
            'amount' => 150.00,
            'note' => 'Donation',
            'type' => 'Donation'
        ]);
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => 1,
            'amount' => 200.00,
            'note' => 'Sold Asset',
            'type' => 'Sold Asset'
        ]);
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => 1,
            'amount' => 250.00,
            'note' => 'Camp Dues',
            'type' => 'Camp Dues'
        ]);
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => 1,
            'amount' => 80.00,
            'note' => 'Partial Camp Dues',
            'type' => 'Partial Camp Dues'
        ]);
        global $wpdb;
        $wpdb->query('COMMIT');

        $this->assertEquals(500.00, $CampManagerLedger->totalMoneyIn());
        $this->assertEquals(100.00, $CampManagerLedger->totalMoneyOut());
        $this->assertEquals(150.00, $CampManagerLedger->totalDonations());
        $this->assertEquals(200.00, $CampManagerLedger->totalAssetsSold());
        $this->assertEquals(330.00, $CampManagerLedger->totalCampDues());
    }

    public function testSumUserCampDues()
    {
        $CampManagerLedger = $this->make('CampManagerLedger', []);

        // No dues
        $this->assertEquals(0, $CampManagerLedger->sumUserCampDues(55));

        // Add some for cmid=123
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => 2,
            'amount' => 80.00,
            'note' => 'Camp Dues',
            'type' => 'Camp Dues',
            'cmid' => 123
        ]);
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => 2,
            'amount' => 55.00,
            'note' => 'Partial Dues',
            'type' => 'Partial Camp Dues',
            'cmid' => 123
        ]);
        global $wpdb;
        $wpdb->query('COMMIT');
        $this->assertEquals(135.00, $CampManagerLedger->sumUserCampDues(123));
    }
}
