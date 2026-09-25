<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;
use lucatume\WPBrowser\Module\WPDb;

/**
 * Puts the seed database into a known starting point and adds seed data on top of it.
 *
 * The committed dump holds no people and no camp data (see bin/build-seed.sh): the only user
 * is the synthetic `seedadmin` (ID 1), and Camp Manager's tables are created empty when CI
 * activates the plugin. Tests never rely on anything else being there. Each Cest resets what
 * it touches, then creates exactly the users and rows it needs:
 *
 *     $I->resetSeedState();
 *     $this->admin = $I->createTestAdmin();
 *     $categories  = $I->createDefaultBudgetCategories();
 *     $item        = $I->createBudgetItem(['category_id' => $categories['Power']['id']]);
 *
 * Conventions (same as the Mothership repo's DbHelper):
 *  - createXData($data) merges caller data over the defaults and returns the row without
 *    touching the database; createX($data) inserts it and returns it with its `id`.
 *  - Defaults cover only what a row cannot exist without; anything else is left to the
 *    column default, so a test states the values it actually asserts on.
 *  - Nothing here reads a fixed ID: use the `id` that createX returns.
 */
class DbHelper extends Module
{
    /** The synthetic administrator that every seed has (created by bin/build-seed.sh). */
    public const SEED_ADMIN_ID = 1;

    /** Password of every user this helper creates. */
    public const PASSWORD = 'password123!test';

    /** The suite's WPDb module (the one the test itself uses, so both share one connection). */
    private function db(): WPDb
    {
        return $this->getModule(WPDb::class);
    }

    // ------------------------------------------------------------------ starting point

    /**
     * Back to the seed: no users but the seed admin, no Camp Manager rows, no form entries.
     */
    public function resetSeedState(): void
    {
        $this->resetUsers();
        $this->resetCampManagerTables();
        $this->resetFormEntries();
        $this->db()->dontHaveOptionInDatabase('camp_manager_season');
    }

    /** Deletes every user (and their meta) except the seed admin. */
    public function resetUsers(): void
    {
        $dbh = $this->db()->_getDbh();
        $users = $this->db()->grabPrefixedTableNameFor('users');
        $meta  = $this->db()->grabPrefixedTableNameFor('usermeta');
        $dbh->exec("DELETE FROM `{$meta}` WHERE user_id <> " . self::SEED_ADMIN_ID);
        $dbh->exec("DELETE FROM `{$users}` WHERE ID <> " . self::SEED_ADMIN_ID);
    }

    /**
     * Empties every Camp Manager table (mf_*). Auto-increment restarts, but tests must not
     * depend on that: use the ids the createX helpers return.
     */
    public function resetCampManagerTables(): void
    {
        $this->truncateMatching($this->db()->grabPrefixedTableNameFor('mf\_%'));
    }

    /** Empties Gravity Forms entries (registration and profile tests create them). */
    public function resetFormEntries(): void
    {
        foreach (['gf_entry', 'gf_entry_meta', 'gf_entry_notes'] as $table) {
            $this->truncateMatching($this->db()->grabPrefixedTableNameFor($table));
        }
    }

    // ------------------------------------------------------------------- season & users

    /**
     * Mirrors CampManagerSeason::current()'s fallback chain (the `camp_manager_season`
     * option, else MAX(season) on mf_roster, else this year). App code does not run in the
     * test process, so fixtures that must land in a season the app will display replicate
     * the same resolution instead of guessing a fixed year.
     */
    public function currentCampManagerSeason(): int
    {
        $option = (int) $this->db()->grabOptionFromDatabase('camp_manager_season');
        if ($option) {
            return $option;
        }
        $latest = (int) $this->db()->grabFromDatabase($this->db()->grabPrefixedTableNameFor('mf_roster'), 'MAX(season)');
        return $latest ?: (int) gmdate('Y');
    }

    /** The meta a fully completed member profile has (what the app treats as "complete"). */
    public function completeProfileMeta(string $first, string $last): array
    {
        return [
            'first_name' => $first,
            'last_name' => $last,
            'user_phone' => '(123) 456-7890',
            'address_1' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip' => '12345',
            'country' => 'United States',
            'user_about_me' => 'This is a test.',
            'playa_name' => 'TestBurner',
            'has_attended_burning_man' => 'No',
        ];
    }

    /**
     * Creates a user with no profile beyond what is passed in; any earlier user with the same
     * login is removed first (a leftover would make wp_signon() log in the oldest one).
     * $data takes wp-browser's haveUserInDatabase overrides (first_name, meta_input, ...).
     *
     * @return array The overrides used, plus `login`, `role`, `password` and `id`.
     */
    public function createUser(string $login, string $role, array $data = []): array
    {
        $table = $this->db()->grabPrefixedTableNameFor('users');
        foreach ($this->db()->grabColumnFromDatabase($table, 'ID', ['user_login' => $login]) as $staleId) {
            $this->db()->dontHaveUserInDatabase((int) $staleId);
        }

        $data += ['user_pass' => self::PASSWORD];
        $id = $this->db()->haveUserInDatabase($login, $role, $data);

        return ['id' => $id, 'login' => $login, 'role' => $role, 'password' => $data['user_pass']] + $data;
    }

    /** `testadmin`: an administrator with a complete profile. */
    public function createTestAdmin(array $data = []): array
    {
        return $this->createUser('testadmin', 'administrator', $this->withProfile('Test', 'Admin', $data));
    }

    /** `testuser`: a subscriber with a complete profile. */
    public function createTestUser(array $data = []): array
    {
        return $this->createUser('testuser', 'subscriber', $this->withProfile('Test', 'User', $data));
    }

    // -------------------------------------------------------------------- camp manager

    public function createBudgetCategoryData(array $data = []): array
    {
        return $data + [
            'name' => 'Test Category',
            'description' => '',
            'season' => $this->currentCampManagerSeason(),
        ];
    }

    public function createBudgetCategory(array $data = []): array
    {
        return $this->insert('mf_budget_category', $this->createBudgetCategoryData($data));
    }

    /**
     * The two categories the budget pages are built around, keyed by name:
     * ['Power' => [... 'id' => n], 'Sojourner' => [... 'id' => m]].
     */
    public function createDefaultBudgetCategories(): array
    {
        return [
            'Power' => $this->createBudgetCategory(['name' => 'Power']),
            'Sojourner' => $this->createBudgetCategory(['name' => 'Sojourner']),
        ];
    }

    public function createBudgetItemData(array $data = []): array
    {
        return $data + ['name' => 'Test Budget Item'];
    }

    /** `category_id` is required by the app; without one the item goes in a new category. */
    public function createBudgetItem(array $data = []): array
    {
        $data = $this->createBudgetItemData($data);
        $data['category_id'] ??= $this->createBudgetCategory()['id'];
        return $this->insert('mf_budget_items', $data);
    }

    public function createLedgerEntryData(array $data = []): array
    {
        return $data + [
            'note' => 'Test Ledger Item',
            'amount' => 200.00,
            'date' => date('Y-m-d H:i:s'),
            'link' => 'https://www.paypal.com/activity/payment/TEST0000000000000',
            'season' => $this->currentCampManagerSeason(),
        ];
    }

    public function createLedgerEntry(array $data = []): array
    {
        return $this->insert('mf_ledger', $this->createLedgerEntryData($data));
    }

    /** `ledger_id` is required. */
    public function createLedgerLineItem(array $data): array
    {
        return $this->insert('mf_ledger_line_items', $data + [
            'receipt_id' => 0,
            'name' => '',
            'cmid' => 0,
            'amount' => 0.00,
            'type' => 'Expense',
        ]);
    }

    public function createReceiptData(array $data = []): array
    {
        return $data + [
            'store' => 'Test Store',
            'date' => date('Y-m-d H:i:s'),
            'season' => $this->currentCampManagerSeason(),
        ];
    }

    public function createReceipt(array $data = []): array
    {
        return $this->insert('mf_receipts', $this->createReceiptData($data));
    }

    /** `receipt_id` is required. */
    public function createReceiptItem(array $data): array
    {
        return $this->insert('mf_receipt_items', $data + ['name' => 'Test Receipt Item']);
    }

    public function createRosterMemberData(array $data = []): array
    {
        return $data + [
            'wpid' => 0,
            'season' => $this->currentCampManagerSeason(),
            'fname' => 'Test',
            'lname' => 'Member',
            'playaname' => 'TestPlaya',
            'email' => 'test.member@seed.test',
        ];
    }

    public function createRosterMember(array $data = []): array
    {
        return $this->insert('mf_roster', $this->createRosterMemberData($data));
    }

    // ---------------------------------------------------------------------------- internals

    /** Complete profile for $first/$last, overridable by $data (its meta_input merges in). */
    private function withProfile(string $first, string $last, array $data): array
    {
        $meta = ($data['meta_input'] ?? []) + $this->completeProfileMeta($first, $last);
        unset($data['meta_input']);
        return $data + ['first_name' => $first, 'last_name' => $last, 'meta_input' => $meta];
    }

    /** Inserts into a table given without its prefix; returns the row with its `id`. */
    private function insert(string $table, array $data): array
    {
        $id = $this->db()->haveInDatabase($this->db()->grabPrefixedTableNameFor($table), $data);
        return $data + ['id' => $id];
    }

    /** TRUNCATEs every existing table matching a LIKE pattern (none is fine: plugin inactive). */
    private function truncateMatching(string $likePattern): void
    {
        $dbh = $this->db()->_getDbh();
        $stmt = $dbh->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?');
        $stmt->execute([$likePattern]);
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if (!$tables) {
            return;
        }
        $dbh->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $dbh->exec("TRUNCATE TABLE `{$table}`");
        }
        $dbh->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}
