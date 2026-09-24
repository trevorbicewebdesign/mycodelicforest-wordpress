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

    public function testProfileListsEveryRoleWithTheYearsHeld()
    {
        $user = self::factory()->user->create(['role' => 'subscriber', 'user_nicename' => 'sparkle']);
        $lead_2024 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2024]);
        $lead_2025 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2025]);
        $treasurer_2025 = $this->roles->upsertRole(['name' => 'Treasurer', 'sort_order' => 20, 'season' => 2025]);
        $lead_2023 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2023]);
        $cook_2027 = $this->roles->upsertRole(['name' => 'Cook', 'sort_order' => 5, 'season' => 2027]);
        $member_2025 = $this->rosterMember($user, 2025);
        $this->roles->setRoleMembers($lead_2024, [$this->rosterMember($user, 2024)]);
        $this->roles->setRoleMembers($lead_2025, [$member_2025]);
        $this->roles->setRoleMembers($treasurer_2025, [$member_2025]);
        $this->roles->setRoleMembers($lead_2023, [$this->rosterMember($user, 2023, 'Dropped')]);
        $this->roles->setRoleMembers($cook_2027, [$this->rosterMember($user, 2027)]);

        $this->assertSame(
            ['Camp Lead' => [2025, 2024], 'Cook' => [2027], 'Treasurer' => [2025]],
            $this->roles->rolesByYearForUser($user),
            'Camp Lead first, then by sort order; newest season first; dropped seasons left out'
        );
        $this->assertSame([], $this->roles->rolesByYearForUser(0));

        $profile = new MycodelicForestProfile(new MycodelicForestMessages(), new MycodelicForestCiviCRM());
        $this->go_to(home_url('/?author=' . $user));
        $html = $profile->resolve_profile_binding(['key' => 'roles_list']);

        $this->assertStringContainsString('Camp Lead', $html);
        $this->assertStringContainsString('Treasurer', $html);
        $this->assertStringContainsString('href="' . home_url('/history/2024/') . '"', $html);
        $this->assertStringContainsString('href="' . home_url('/history/2025/') . '"', $html);
        $this->assertStringNotContainsString('2023', $html);

        $nobody = self::factory()->user->create(['role' => 'subscriber']);
        $this->go_to(home_url('/?author=' . $nobody));
        $this->assertSame('No roles yet.', $profile->resolve_profile_binding(['key' => 'roles_list']));
    }

    public function testHeroBadgeOnlyForThisSeasonsCampLead()
    {
        $profile = new MycodelicForestProfile(new MycodelicForestMessages(), new MycodelicForestCiviCRM());
        $badge_block = ['blockName' => 'core/paragraph', 'attrs' => ['className' => 'mf-camp-lead-badge']];
        $lead_now = self::factory()->user->create(['role' => 'subscriber']);
        $lead_before = self::factory()->user->create(['role' => 'subscriber']);
        $lead_2027 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2027]);
        $lead_2025 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2025]);
        $this->roles->setRoleMembers($lead_2027, [$this->rosterMember($lead_now, 2027)]);
        $this->roles->setRoleMembers($lead_2025, [$this->rosterMember($lead_before, 2025)]);

        $this->go_to(home_url('/?author=' . $lead_now));
        $html = $profile->resolve_profile_binding(['key' => 'camp_lead_badge']);
        $this->assertStringContainsString('2027 Camp Lead', $html);
        $this->assertSame('<p>kept</p>', $profile->maybe_hide_camp_lead_badge('<p>kept</p>', $badge_block));

        $this->go_to(home_url('/?author=' . $lead_before));
        $this->assertSame('', $profile->resolve_profile_binding(['key' => 'camp_lead_badge']), 'last season only');
        $this->assertSame('', $profile->maybe_hide_camp_lead_badge('<p>gone</p>', $badge_block));
        $this->assertSame('<p>other</p>', $profile->maybe_hide_camp_lead_badge('<p>other</p>', ['attrs' => []]), 'other blocks untouched');
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

    public function testAUsersRolesFollowTheirRosterRowsAcrossSeasons()
    {
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $other = self::factory()->user->create(['role' => 'subscriber']);
        $lead27 = $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2027]);
        $fire27 = $this->roles->upsertRole(['name' => 'Firelord', 'sort_order' => 20, 'season' => 2027]);
        $lead25 = $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2025]);
        $lead24 = $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2024]);
        $row27 = $this->rosterMember($user, 2027);
        $row25 = $this->rosterMember($user, 2025);
        $other27 = $this->rosterMember($other, 2027);
        $this->roles->setRoleMembers($lead27, [$other27]);

        // Options are "YYYY Role", newest season first.
        $this->assertSame(['2027 Camp Lead', '2027 Firelord', '2025 Camp Lead', '2024 Camp Lead'], array_column($this->roles->getAllRoles(), 'label'));
        $this->assertSame([2027, 2025], array_keys($this->roles->rosterRowsForUser($user)));

        // 2024 has no roster row for them, so that role is skipped; 0 is ignored.
        $result = $this->roles->setUserRoles($user, [$lead25, $lead27, $lead24, 0]);

        $this->assertSame(['skipped' => [$lead24], 'linked' => []], $result);
        $this->assertSame([$lead27, $lead25], $this->roles->getUserRoleIds($user));
        $this->assertSame([$lead27], $this->roles->getMemberRoleIds($row27));
        $this->assertSame([$lead25], $this->roles->getMemberRoleIds($row25));
        $this->assertSame([$lead27], $this->roles->getMemberRoleIds($other27), 'the other holder is untouched');

        // Leaving a season out clears it; a user with no roster rows holds nothing and changes nothing.
        $this->roles->setUserRoles($user, [$fire27]);

        $this->assertSame([$fire27], $this->roles->getUserRoleIds($user));
        $this->assertSame([], $this->roles->getMemberRoleIds($row25));
        $this->assertSame([$lead27], $this->roles->setUserRoles(self::factory()->user->create(), [$lead27])['skipped']);
        $this->assertSame([$lead27], $this->roles->getMemberRoleIds($other27));
    }

    public function testDuplicateRosterRowsInASeasonResolveToTheActiveOne()
    {
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $lead = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2027]);
        $dropped = $this->rosterMember($user, 2027, 'Dropped');
        $active = $this->rosterMember($user, 2027);
        $this->roles->setRoleMembers($lead, [$dropped]);

        $this->assertSame((string) $active, $this->roles->rosterRowsForUser($user)[2027]['id']);
        $this->assertSame([$lead], $this->roles->getUserRoleIds($user), 'held through either row');

        $this->roles->setUserRoles($user, [$lead]);

        $this->assertSame([$lead], $this->roles->getMemberRoleIds($active));
        $this->assertSame([], $this->roles->getMemberRoleIds($dropped), 'the stale row is cleared');
    }

    public function testUserProfileFieldOffersYearPrefixedRolesAndSavesForAdminsOnly()
    {
        $admin = self::factory()->user->create(['role' => 'administrator']);
        $member = self::factory()->user->create(['role' => 'subscriber']);
        $lead27 = $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2027]);
        $fire27 = $this->roles->upsertRole(['name' => 'Firelord', 'sort_order' => 20, 'season' => 2027]);
        $lead24 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2024]);
        $this->roles->setMemberRoles($this->rosterMember($member, 2027), [$lead27]);
        $profile = new CampManagerUserProfile();

        wp_set_current_user($admin);
        ob_start();
        $profile->render(get_userdata($member));
        $html = ob_get_clean();

        $this->assertStringContainsString('<h3>Camp Roles</h3>', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $lead27 . '"[^>]*selected[^>]*>\s*2027 Camp Lead\s*</', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $fire27 . '"(?![^>]*selected)(?![^>]*disabled)[^>]*>\s*2027 Firelord\s*</', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $lead24 . '"[^>]*disabled[^>]*>\s*2024 Camp Lead \(not on the 2024 roster\)/', $html);

        // Saving swaps the 2027 role; the 2024 one can't be held and is ignored.
        $_POST = [CampManagerUserProfile::FIELD . '_submitted' => '1', CampManagerUserProfile::FIELD => [(string) $fire27, (string) $lead24]];
        $profile->save($member);
        $this->assertSame([$fire27], $this->roles->getUserRoleIds($member));

        // A form without the field leaves roles alone.
        $_POST = [];
        $profile->save($member);
        $this->assertSame([$fire27], $this->roles->getUserRoleIds($member));

        // Non-admins see their roles read-only and can't change them, whatever they post.
        wp_set_current_user($member);
        ob_start();
        $profile->render(get_userdata($member));
        $html = ob_get_clean();
        $this->assertStringContainsString('2027 Firelord', $html);
        $this->assertStringNotContainsString('<select', $html);

        $_POST = [CampManagerUserProfile::FIELD . '_submitted' => '1', CampManagerUserProfile::FIELD => [(string) $lead27]];
        $profile->save($member);
        $this->assertSame([$fire27], $this->roles->getUserRoleIds($member));
        $_POST = [];
    }


    public function testAssigningARoleAutoLinksTheUsersUnlinkedRosterEntry()
    {
        // Users outlive the test (the suite commits), so emails must be unique per run.
        $email = 'wizard-' . uniqid() . '@example.org';
        $user = self::factory()->user->create(['role' => 'subscriber', 'user_email' => $email]);
        update_user_meta($user, 'first_name', 'Sandor');
        update_user_meta($user, 'last_name', 'Stockfleth');
        update_user_meta($user, 'playa_name', 'Wizard');
        $stranger = self::factory()->user->create(['role' => 'subscriber']);
        $lead = [];
        foreach ([2019, 2018, 2017, 2016] as $season) {
            $lead[$season] = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => $season]);
        }
        // 2019: by email (case-insensitive), even though the names differ; a name-only match loses to it.
        $by_name_2019 = $this->tester->haveInDatabase('mf_roster', ['wpid' => 0, 'fname' => 'sandor', 'lname' => 'STOCKFLETH', 'email' => 'other@example.org', 'status' => 'Confirmed', 'season' => 2019]);
        $by_email = $this->tester->haveInDatabase('mf_roster', ['wpid' => null, 'fname' => 'Sandy', 'lname' => 'S', 'playaname' => '', 'email' => strtoupper($email), 'status' => 'Dropped', 'season' => 2019, 'low_income' => 1]);
        // 2018: by first and last name. 2017: by playa name. 2016: nothing to link.
        $by_name = $this->tester->haveInDatabase('mf_roster', ['wpid' => 0, 'fname' => 'SANDOR', 'lname' => 'stockfleth', 'email' => '', 'status' => 'Confirmed', 'season' => 2018]);
        $by_playa = $this->tester->haveInDatabase('mf_roster', ['wpid' => 0, 'fname' => '', 'lname' => '', 'playaname' => 'wizard', 'email' => '', 'status' => 'Confirmed', 'season' => 2017]);
        // Rows already linked to someone else are never taken, whatever they match.
        $taken = $this->tester->haveInDatabase('mf_roster', ['wpid' => $stranger, 'fname' => 'Sandor', 'lname' => 'Stockfleth', 'email' => $email, 'status' => 'Confirmed', 'season' => 2016]);

        $this->assertSame((string) $by_email, $this->roles->findUnlinkedRosterRow($user, 2019)['id']);
        $this->assertNull($this->roles->findUnlinkedRosterRow($user, 2016));

        $result = $this->roles->setUserRoles($user, [$lead[2019], $lead[2018], $lead[2017], $lead[2016]]);

        $this->assertSame([$lead[2016]], $result['skipped']);
        $this->assertSame([2019, 2018, 2017], array_keys($result['linked']));
        $this->assertSame([$lead[2019], $lead[2018], $lead[2017]], $this->roles->getUserRoleIds($user));
        $this->assertSame([$lead[2019]], $this->roles->getMemberRoleIds($by_email));
        $this->assertSame([], $this->roles->getMemberRoleIds($by_name_2019));
        $this->assertSame([$lead[2018]], $this->roles->getMemberRoleIds($by_name));
        $this->assertSame([$lead[2017]], $this->roles->getMemberRoleIds($by_playa));

        // Linking writes the user id and nothing else on the entry.
        $this->tester->seeInDatabase('mf_roster', ['id' => $by_email, 'wpid' => $user, 'fname' => 'Sandy', 'lname' => 'S', 'playaname' => '', 'email' => strtoupper($email), 'status' => 'Dropped', 'low_income' => 1]);
        $this->tester->seeInDatabase('mf_roster', ['id' => $by_name_2019, 'wpid' => 0]);
        $this->tester->seeInDatabase('mf_roster', ['id' => $taken, 'wpid' => $stranger]);
        $this->assertSame([2019, 2018, 2017], array_keys($this->roles->rosterRowsForUser($user)));

        // Once linked, a later save finds the row without linking again.
        $result = $this->roles->setUserRoles($user, [$lead[2019]]);
        $this->assertSame(['skipped' => [], 'linked' => []], $result);
        $this->assertSame([$lead[2019]], $this->roles->getUserRoleIds($user));
    }

    public function testUserProfileFieldExplainsAutoLinkingAndReportsIt()
    {
        $admin = self::factory()->user->create(['role' => 'administrator']);
        $email = 'ember-' . uniqid() . '@example.org';
        $member = self::factory()->user->create(['role' => 'subscriber', 'user_email' => $email]);
        $lead19 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2019]);
        $lead16 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2016]);
        $this->tester->haveInDatabase('mf_roster', ['wpid' => 0, 'fname' => 'Em', 'lname' => 'Ber', 'email' => $email, 'status' => 'Confirmed', 'season' => 2019]);
        $profile = new CampManagerUserProfile();
        wp_set_current_user($admin);

        ob_start();
        $profile->render(get_userdata($member));
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression('/<option value="' . $lead19 . '"(?![^>]*disabled)[^>]*>\s*2019 Camp Lead \(links their 2019 roster entry, Em Ber\)/', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $lead16 . '"[^>]*disabled[^>]*>\s*2016 Camp Lead \(not on the 2016 roster\)/', $html);

        $_POST = [CampManagerUserProfile::FIELD . '_submitted' => '1', CampManagerUserProfile::FIELD => [(string) $lead19, (string) $lead16]];
        $profile->save($member);
        $_POST = [];

        $this->assertSame([$lead19], $this->roles->getUserRoleIds($member));
        ob_start();
        $profile->notices();
        $notice = ob_get_clean();
        $this->assertStringContainsString('Linked this user to the 2019 roster entry for Em Ber.', $notice);
        $this->assertStringContainsString('Could not assign 2016 Camp Lead: this user is not on the roster for that season.', $notice);

        // The notice shows once.
        ob_start();
        $profile->notices();
        $this->assertSame('', ob_get_clean());
        // And the linked season now reads as plain.
        ob_start();
        $profile->render(get_userdata($member));
        $html = ob_get_clean();
        $this->assertMatchesRegularExpression('/<option value="' . $lead19 . '"[^>]*selected[^>]*>\s*2019 Camp Lead\s*</', $html);
    }

}
