<?php

class CampManagerRolesTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var CampManagerRoles */
    private $roles;

    protected function _before()
    {
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
        global $wpdb;
        $wpdb->query('COMMIT');
        // The role tables arrive with db version 3 (CampManagerSeason::upgrade()).
        require_once CAMPMANAGER_CORE_ABS_PATH . 'classes/class-install.php';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $installer = new CampManagerInstall();
        $installer->create_mf_roles_table();
        $installer->create_mf_role_members_table();
        update_option(CampManagerSeason::OPTION_DB_VERSION, CampManagerSeason::DB_VERSION);
        foreach (['mf_role_members', 'mf_roles', 'mf_roster'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        update_option(CampManagerSeason::OPTION_CURRENT, 2027);
        // camp-manager isn't in WPLoader's plugin list; it is activated inside a test, and WPTestCase
        // rolls back the hooks it added then, so register the capability filter for each test.
        if (!has_filter('user_has_cap', [CampManagerRoles::class, 'grantCaps'])) {
            add_filter('user_has_cap', [CampManagerRoles::class, 'grantCaps'], 10, 4);
        }
        CampManagerRoles::flushCache();
        $this->roles = new CampManagerRoles();
    }

    private function rosterMember(int $wpid, int $season, string $status = 'Confirmed', string $playaname = ''): int
    {
        return $this->tester->haveInDatabase('mf_roster', [
            'wpid' => $wpid, 'fname' => "Camper$wpid", 'lname' => 'Test', 'playaname' => $playaname,
            'status' => $status, 'season' => $season,
        ]);
    }

    public function testCurrentSeasonHolderGetsTheRolesAreas()
    {
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $treasurer = $this->roles->upsertRole(['name' => 'Treasurer', 'permissions' => ['finances', 'budgets', 'bogus'], 'season' => 2027]);
        $this->roles->setRoleMembers($treasurer, [$this->rosterMember($user, 2027)]);

        $this->assertSame(['finances', 'budgets'], $this->roles->getRole($treasurer)['permissions']);
        $this->assertTrue(user_can($user, 'camp_manager_finances'));
        $this->assertTrue(user_can($user, 'camp_manager_budgets'));
        $this->assertTrue(user_can($user, CampManagerRoles::CAP_ACCESS));
        $this->assertFalse(user_can($user, 'camp_manager_roster'));
        $this->assertFalse(user_can($user, 'manage_options'));
    }

    public function testLastSeasonsRoleGrantsNothing()
    {
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $treasurer = $this->roles->upsertRole(['name' => 'Treasurer', 'permissions' => ['finances'], 'season' => 2025]);
        $this->roles->setRoleMembers($treasurer, [$this->rosterMember($user, 2025)]);

        $this->assertFalse(user_can($user, 'camp_manager_finances'));
        $this->assertFalse(user_can($user, CampManagerRoles::CAP_ACCESS));
    }

    public function testDroppedHolderLosesAccess()
    {
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $lead = $this->roles->upsertRole(['name' => 'Camp Lead', 'permissions' => ['roster'], 'season' => 2027]);
        $this->roles->setRoleMembers($lead, [$this->rosterMember($user, 2027, 'Dropped')]);

        $this->assertFalse(user_can($user, 'camp_manager_roster'));
    }

    public function testAdminsHaveEveryArea()
    {
        $admin = self::factory()->user->create(['role' => 'administrator']);
        foreach (array_keys(CampManagerRoles::AREAS) as $area) {
            $this->assertTrue(user_can($admin, CampManagerRoles::cap($area)));
        }
    }

    public function testHoldersMustBeOnTheRolesOwnSeasonRoster()
    {
        $role = $this->roles->upsertRole(['name' => 'Archivist', 'season' => 2027]);
        $this_year = $this->rosterMember(0, 2027);
        $last_year = $this->rosterMember(0, 2025);

        $this->roles->setRoleMembers($role, [$this_year, $last_year]);

        $this->assertSame([$this_year], array_map('intval', array_column($this->roles->getRole($role)['members'], 'id')));
    }

    public function testRolesAreScopedToTheSeasonAndOrdered()
    {
        $this->roles->upsertRole(['name' => 'Treasurer', 'sort_order' => 20, 'season' => 2027]);
        $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2027]);
        $this->roles->upsertRole(['name' => 'Goblin', 'season' => 2025]);

        $this->assertSame(['Camp Lead', 'Treasurer'], array_column($this->roles->getRoles(2027), 'name'));
        $this->assertSame(['Goblin'], array_column($this->roles->getRoles(2025), 'name'));
        $this->assertContains(2025, CampManagerSeason::available());
    }

    public function testMemberRolesCanBeSetFromTheMemberSide()
    {
        $lead = $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2027]);
        $firelord = $this->roles->upsertRole(['name' => 'Firelord', 'sort_order' => 20, 'season' => 2027]);
        $last_year = $this->roles->upsertRole(['name' => 'Firelord', 'season' => 2025]);
        $member = $this->rosterMember(0, 2027, 'Confirmed', 'Ember');
        $other = $this->rosterMember(0, 2027);
        $this->roles->setRoleMembers($lead, [$other]);

        // Only roles from the member's own season stick; the other holder of Camp Lead is untouched.
        $this->roles->setMemberRoles($member, [$lead, $firelord, $last_year, 0]);

        $this->assertSame([$lead, $firelord], $this->roles->getMemberRoleIds($member));
        $this->assertSame([$other, $member], array_map('intval', array_column($this->roles->getRole($lead)['members'], 'id')));
        $this->assertSame(['Camp Lead', 'Firelord'], $this->roles->getRoleNamesByMember([$member])[$member]);

        // An empty selection clears them.
        $this->roles->setMemberRoles($member, []);

        $this->assertSame([], $this->roles->getMemberRoleIds($member));
        $this->assertSame([$other], array_map('intval', array_column($this->roles->getRole($lead)['members'], 'id')));
    }

    public function testEverySeasonWithARosterGetsACampLeadRole()
    {
        $this->rosterMember(0, 2013);
        $this->rosterMember(0, 2015);
        $this->roles->upsertRole(['name' => 'camp lead', 'season' => 2015]); // already there, any case
        $this->roles->upsertRole(['name' => 'Treasurer', 'sort_order' => 20, 'season' => 2019]);

        $added = $this->roles->ensureLeadRoleEverySeason();

        $this->assertSame([2013, 2019, 2027], $added);
        $this->assertSame(['Camp Lead'], array_column($this->roles->getRoles(2013), 'name'));
        $this->assertSame(['camp lead'], array_column($this->roles->getRoles(2015), 'name'));
        $this->assertSame(['Camp Lead', 'Treasurer'], array_column($this->roles->getRoles(2019), 'name'));
        $this->assertSame(array_keys(CampManagerRoles::AREAS), $this->roles->getRoles(2013)[0]['permissions']);
        $this->assertSame([], $this->roles->ensureLeadRoleEverySeason(), 'running it again adds nothing');
    }

    public function testBurnYearPageListsThatSeasonsCampLeads()
    {
        $user = self::factory()->user->create(['role' => 'subscriber', 'user_nicename' => 'sparkle']);
        $lead_2024 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2024]);
        $lead_2025 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2025]);
        $this->roles->setRoleMembers($lead_2024, [
            $this->rosterMember($user, 2024, 'Confirmed', 'Sparkle'),
            $this->rosterMember(0, 2024, 'Dropped', 'Gone'),
        ]);
        $this->roles->setRoleMembers($lead_2025, [$this->rosterMember(0, 2025, 'Confirmed', 'NextYear')]);

        $this->assertSame(['Sparkle'], array_column($this->roles->leadsForSeason(2024), 'playaname'));

        $history = MycodelicForestHistory::$instance ?: new MycodelicForestHistory();
        $post = self::factory()->post->create(['post_type' => 'camp_year', 'meta_input' => ['burn_year' => 2024]]);
        $html = $history->renderLeads($post);

        $this->assertStringContainsString('Camp Leads', $html);
        $this->assertStringContainsString('href="' . home_url('/profile/sparkle/') . '"', $html);
        $this->assertStringContainsString('Sparkle (Camper' . $user . ' Test)', $html);
        $this->assertStringNotContainsString('Gone', $html);
        $this->assertStringNotContainsString('NextYear', $html);

        $empty = self::factory()->post->create(['post_type' => 'camp_year', 'meta_input' => ['burn_year' => 2019]]);
        $this->assertStringContainsString('Not recorded for this year', $history->renderLeads($empty));
    }

    public function testDeletingRolesRemovesTheirHolders()
    {
        global $wpdb;
        $role = $this->roles->upsertRole(['name' => 'Firelord', 'season' => 2027]);
        $this->roles->setRoleMembers($role, [$this->rosterMember(0, 2027)]);

        $this->roles->deleteRoles([$role]);

        $this->assertNull($this->roles->getRole($role));
        $this->assertSame(0, (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mf_role_members WHERE role_id = %d", $role)));
    }

    public function testShortcodeListsRolesWithActiveHolders()
    {
        $role = $this->roles->upsertRole(['name' => 'Ticketmaster', 'description' => 'Finds <strong>tickets</strong>.', 'season' => 2027]);
        $this->roles->setRoleMembers($role, [
            $this->rosterMember(0, 2027, 'Confirmed', 'Sparkle'),
            $this->rosterMember(0, 2027, 'Dropped', 'Gone'),
        ]);
        $this->roles->upsertRole(['name' => 'Bike Master', 'season' => 2027]);

        $html = do_shortcode('[camp_manager_roles]');

        $this->assertStringContainsString('Ticketmaster', $html);
        $this->assertStringContainsString('<strong>tickets</strong>', $html);
        $this->assertStringContainsString('Sparkle', $html);
        $this->assertStringNotContainsString('Gone', $html);
        $this->assertStringContainsString('Open', $html);
        $this->assertStringNotContainsString('Sparkle', do_shortcode('[camp_manager_roles holders="no"]'));
    }
}
