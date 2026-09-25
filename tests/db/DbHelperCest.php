<?php

declare(strict_types=1);

namespace Tests\Db;

use Tests\Support\DbTester;

/**
 * The seed-data helpers themselves. Browserless: MySQL only, against the prepared seed
 * database (dump imported, camp-manager activated) as the acceptance suite finds it.
 */
class DbHelperCest
{
    public function _before(DbTester $I): void
    {
        $I->resetSeedState();
    }

    public function _after(DbTester $I): void
    {
        $I->resetSeedState();
    }

    public function theSeedHasOneUserAndNoCampData(DbTester $I): void
    {
        $users = $I->grabColumnFromDatabase($I->grabPrefixedTableNameFor('users'), 'user_login');
        $I->assertSame(['seedadmin'], $users);
        foreach (['mf_roster', 'mf_ledger', 'mf_receipts', 'mf_budget_category', 'mf_budget_items'] as $table) {
            $I->seeNumRecords(0, $I->grabPrefixedTableNameFor($table));
        }
    }

    public function resetSeedStateRemovesWhatTestsAdded(DbTester $I): void
    {
        $I->createTestAdmin();
        $I->createTestUser();
        $I->createRosterMember();
        $I->createLedgerEntry();
        $I->haveOptionInDatabase('camp_manager_season', 2031);

        $I->resetSeedState();

        $I->assertSame(['seedadmin'], $I->grabColumnFromDatabase($I->grabPrefixedTableNameFor('users'), 'user_login'));
        $I->dontSeeInDatabase($I->grabPrefixedTableNameFor('usermeta'), ['meta_key' => 'playa_name']);
        $I->seeNumRecords(0, $I->grabPrefixedTableNameFor('mf_roster'));
        $I->seeNumRecords(0, $I->grabPrefixedTableNameFor('mf_ledger'));
        $I->assertSame((int) gmdate('Y'), $I->currentCampManagerSeason(), 'season option is cleared');
    }

    public function createTestAdminMakesACompleteAdministrator(DbTester $I): void
    {
        $admin = $I->createTestAdmin();

        $I->assertSame('testadmin', $admin['login']);
        $I->assertGreaterThan(1, $admin['id']);
        $I->seeUserInDatabase(['ID' => $admin['id'], 'user_login' => 'testadmin']);
        $I->seeUserMetaInDatabase(['user_id' => $admin['id'], 'meta_key' => 'first_name', 'meta_value' => 'Test']);
        $I->seeUserMetaInDatabase(['user_id' => $admin['id'], 'meta_key' => 'last_name', 'meta_value' => 'Admin']);
        $I->seeUserMetaInDatabase(['user_id' => $admin['id'], 'meta_key' => 'playa_name', 'meta_value' => 'TestBurner']);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('usermeta'), [
            'user_id' => $admin['id'], 'meta_key' => 'wp_capabilities', 'meta_value like' => '%administrator%',
        ]);
    }

    public function createUserReplacesAnEarlierUserWithTheSameLogin(DbTester $I): void
    {
        $first = $I->createTestUser();
        $second = $I->createTestUser();

        $I->assertNotSame($first['id'], $second['id']);
        $I->seeNumRecords(1, $I->grabPrefixedTableNameFor('users'), ['user_login' => 'testuser']);
    }

    public function profileMetaCanBeOverriddenAndExtended(DbTester $I): void
    {
        $user = $I->createTestUser(['meta_input' => ['city' => 'Nowhere', 'years_attended' => '["2024"]']]);

        $I->seeUserMetaInDatabase(['user_id' => $user['id'], 'meta_key' => 'city', 'meta_value' => 'Nowhere']);
        $I->seeUserMetaInDatabase(['user_id' => $user['id'], 'meta_key' => 'years_attended', 'meta_value' => '["2024"]']);
        $I->seeUserMetaInDatabase(['user_id' => $user['id'], 'meta_key' => 'address_1', 'meta_value' => '123 Main St']);
    }

    public function createUserAddsNoProfileUnlessAsked(DbTester $I): void
    {
        $user = $I->createUser('halfdone', 'subscriber', ['meta_input' => ['first_name' => 'Half']]);

        $I->seeUserMetaInDatabase(['user_id' => $user['id'], 'meta_key' => 'first_name', 'meta_value' => 'Half']);
        $I->dontSeeUserMetaInDatabase(['user_id' => $user['id'], 'meta_key' => 'address_1']);
    }

    public function budgetCategoriesGetTheirOwnIdsAndTheCurrentSeason(DbTester $I): void
    {
        $categories = $I->createDefaultBudgetCategories();

        $I->assertSame(['Power', 'Sojourner'], array_keys($categories));
        $I->assertNotSame($categories['Power']['id'], $categories['Sojourner']['id']);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_budget_category'), [
            'id' => $categories['Sojourner']['id'], 'name' => 'Sojourner', 'season' => $I->currentCampManagerSeason(),
        ]);
    }

    public function budgetItemsFallBackToANewCategory(DbTester $I): void
    {
        $power = $I->createBudgetCategory(['name' => 'Power']);
        $withCategory = $I->createBudgetItem(['category_id' => $power['id'], 'price' => 100]);
        $withoutCategory = $I->createBudgetItem();

        $I->assertSame($power['id'], $withCategory['category_id']);
        $I->assertNotSame($power['id'], $withoutCategory['category_id']);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_budget_items'), ['id' => $withoutCategory['id'], 'name' => 'Test Budget Item']);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_budget_category'), ['id' => $withoutCategory['category_id']]);
    }

    public function ledgerReceiptAndRosterRowsAreLinkedByTheirReturnedIds(DbTester $I): void
    {
        $ledger = $I->createLedgerEntry(['amount' => 50.00]);
        $line = $I->createLedgerLineItem(['ledger_id' => $ledger['id'], 'amount' => 50.00]);
        $receipt = $I->createReceipt(['total' => 110.00]);
        $item = $I->createReceiptItem(['receipt_id' => $receipt['id'], 'price' => 100]);
        $member = $I->createRosterMember(['fname' => 'Alice']);

        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_ledger'), ['id' => $ledger['id'], 'season' => $I->currentCampManagerSeason()]);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_ledger_line_items'), ['id' => $line['id'], 'ledger_id' => $ledger['id'], 'type' => 'Expense']);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_receipts'), ['id' => $receipt['id'], 'store' => 'Test Store']);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_receipt_items'), ['id' => $item['id'], 'receipt_id' => $receipt['id']]);
        $I->seeInDatabase($I->grabPrefixedTableNameFor('mf_roster'), ['id' => $member['id'], 'fname' => 'Alice', 'lname' => 'Member', 'wpid' => 0]);
    }

    public function dataHelpersBuildRowsWithoutTouchingTheDatabase(DbTester $I): void
    {
        $data = $I->createRosterMemberData(['fname' => 'Ghost']);

        $I->assertSame('Ghost', $data['fname']);
        $I->assertArrayNotHasKey('id', $data);
        $I->seeNumRecords(0, $I->grabPrefixedTableNameFor('mf_roster'));
    }

    public function currentSeasonFollowsTheAppsFallbackChain(DbTester $I): void
    {
        $I->assertSame((int) gmdate('Y'), $I->currentCampManagerSeason(), 'empty roster: this year');

        $I->createRosterMember(['season' => 2019]);
        $I->createRosterMember(['season' => 2022]);
        $I->assertSame(2022, $I->currentCampManagerSeason(), 'newest roster season');

        $I->haveOptionInDatabase('camp_manager_season', 2030);
        $I->assertSame(2030, $I->currentCampManagerSeason(), 'the option wins');
    }
}
