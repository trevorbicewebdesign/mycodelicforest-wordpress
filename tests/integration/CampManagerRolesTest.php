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

    public function testNewerDefaultRolesAreAddedToASeasonOnce()
    {
        $required = ['Circle Lead', 'Leave No Trace Lead', 'Sustainability Lead', 'R.I.D.E. Lead'];
        $this->roles->upsertRole(['name' => 'Treasurer', 'sort_order' => 20, 'season' => 2027]);
        $this->roles->upsertRole(['name' => 'sustainability lead', 'sort_order' => 30, 'season' => 2027]); // any case

        $added = $this->roles->addMissingDefaultRoles(2027, $required);

        $this->assertSame(['Circle Lead', 'Leave No Trace Lead', 'R.I.D.E. Lead'], $added);
        $this->assertSame(
            ['Treasurer', 'sustainability lead', 'Circle Lead', 'Leave No Trace Lead', 'R.I.D.E. Lead'],
            array_column($this->roles->getRoles(2027), 'name')
        );
        $this->assertSame([], $this->roles->addMissingDefaultRoles(2027, $required), 'running it again adds nothing');
        $this->assertSame([], $this->roles->getRoles(2025), 'other seasons are left alone');
    }

    public function testNewerDefaultRolesGoBackToTheSeasonsFrom2022()
    {
        $this->rosterMember(0, 2021);
        $this->rosterMember(0, 2022);
        $this->rosterMember(0, 2025);
        $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2022]);

        $added = $this->roles->addMissingDefaultRolesSince(2022, ['Circle Lead', 'Sustainability Lead']);

        $this->assertSame([2022, 2025, 2027], array_keys($added));
        $this->assertSame(['Circle Lead', 'Sustainability Lead'], $added[2025]);
        $this->assertSame(['Camp Lead', 'Circle Lead', 'Sustainability Lead'], array_column($this->roles->getRoles(2022), 'name'));
        $this->assertSame([], $this->roles->getRoles(2021), 'seasons before 2022 are left alone');
        $lineages = array_unique(array_column(array_filter(
            array_merge($this->roles->getRoles(2022), $this->roles->getRoles(2025), $this->roles->getRoles(2027)),
            function ($r) { return $r['name'] === 'Circle Lead'; }
        ), 'lineage_id'));
        $this->assertCount(1, $lineages, 'the same role in every season shares one lineage');
        $this->assertSame([], $this->roles->addMissingDefaultRolesSince(2022, ['Circle Lead', 'Sustainability Lead']));
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


    public function testARenamedRoleStaysTheSameRoleAcrossSeasons()
    {
        $goblin23 = $this->roles->upsertRole(['name' => 'Goblin', 'sort_order' => 30, 'season' => 2023]);
        $goblin24 = $this->roles->upsertRole(['name' => 'GOBLIN', 'sort_order' => 30, 'season' => 2024]);
        $treasurer25 = $this->roles->upsertRole(['name' => 'Treasurer', 'sort_order' => 20, 'season' => 2025]);
        $lead25 = $this->roles->upsertRole(['name' => 'Camp Lead', 'sort_order' => 10, 'season' => 2025]);

        // Same-named roles join on their own (case-insensitive); a new name starts its own history.
        $this->assertSame($goblin23, $this->roles->lineageOf($goblin24));
        $this->assertSame($treasurer25, $this->roles->lineageOf($treasurer25));
        $this->assertSame([], $this->roles->getRole($goblin24)['also_known_as']);
        $this->assertSame(
            ['2024 GOBLIN', '2023 Goblin'],
            array_column($this->roles->lineageOptions($treasurer25, 2025), 'label'),
            'only roles outside this one\'s history, from other seasons'
        );

        // Declaring the Treasurer the same role as the Goblin merges the histories.
        $this->roles->upsertRole(['name' => 'Treasurer', 'sort_order' => 20, 'same_as' => $goblin24], $treasurer25);

        $this->assertSame($goblin23, $this->roles->lineageOf($treasurer25));
        $this->assertSame(['GOBLIN' => [2024], 'Goblin' => [2023]], $this->roles->getRole($treasurer25)['also_known_as'], 'newest first');
        $this->assertSame(['Treasurer' => [2025]], $this->roles->getRole($goblin23)['also_known_as']);
        $this->assertSame([], array_column($this->roles->lineageOptions($treasurer25, 2025), 'label'), 'nothing left outside its history but same-season roles');
        $this->assertSame('Treasurer', $this->roles->lineages()[$goblin23][0]['name'], 'the newest row names the role');

        // Copying a season's roles keeps each copy the same role as its original.
        $this->roles->copyRoles(2025, 2027);
        $copies = $this->roles->getRoles(2027);
        $this->assertSame(['Camp Lead', 'Treasurer'], array_column($copies, 'name'));
        $this->assertSame($goblin23, $this->roles->lineageOf((int) $copies[1]['id']));
        $this->assertSame($lead25, $this->roles->lineageOf((int) $copies[0]['id']));

        // A member who was the 2023 Goblin and the 2025 Treasurer held one role, under its current name.
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $this->roles->setRoleMembers($goblin23, [$this->rosterMember($user, 2023)]);
        $this->roles->setRoleMembers($treasurer25, [$this->rosterMember($user, 2025)]);
        $this->assertSame(['Treasurer' => [2025, 2023]], $this->roles->rolesByYearForUser($user));
        $this->assertSame(['Treasurer' => ['Goblin' => [2023]]], $this->roles->formerNamesForUser($user));

        $profile = new MycodelicForestProfile(new MycodelicForestMessages(), new MycodelicForestCiviCRM());
        $this->go_to(home_url('/?author=' . $user));
        $html = $profile->resolve_profile_binding(['key' => 'roles_list']);
        $this->assertStringContainsString('Treasurer', $html);
        $this->assertStringContainsString('as Goblin in 2023', $html);
        $this->assertStringContainsString('href="' . home_url('/history/2023/') . '"', $html);

        // Cutting the 2023 Goblin back out leaves the others together, rooted at their earliest row.
        $this->roles->upsertRole(['name' => 'Goblin', 'sort_order' => 30, 'same_as' => 'new'], $goblin23);

        $this->assertSame($goblin23, $this->roles->lineageOf($goblin23));
        $this->assertSame($goblin24, $this->roles->lineageOf($goblin24));
        $this->assertSame($goblin24, $this->roles->lineageOf($treasurer25));
        $this->assertSame(['Treasurer' => [2025], 'Goblin' => [2023]], $this->roles->rolesByYearForUser($user));
        $this->assertSame([], $this->roles->formerNamesForUser($user));

        // Deleting a lineage's earliest row re-roots the rest.
        $this->roles->deleteRoles([$goblin24]);
        $this->assertSame($treasurer25, $this->roles->lineageOf($treasurer25));
        $this->assertSame($treasurer25, $this->roles->lineageOf((int) $copies[1]['id']));
    }

    public function testCampLeadsAreFoundThroughTheRolesHistory()
    {
        $lead24 = $this->roles->upsertRole(['name' => 'Camp Lead', 'season' => 2024]);
        $poobah25 = $this->roles->upsertRole(['name' => 'Grand Poobah', 'season' => 2025, 'same_as' => $lead24]);
        $user = self::factory()->user->create(['role' => 'subscriber']);
        $this->roles->setRoleMembers($poobah25, [$this->rosterMember($user, 2025, 'Confirmed', 'Sparkle')]);

        $this->assertSame(['Sparkle'], array_column($this->roles->leadsForSeason(2025), 'playaname'));
        $this->assertSame(['Grand Poobah' => [2025]], $this->roles->rolesByYearForUser($user));
    }

    public function testBackfillJoinsSameNamedRolesIntoOneHistory()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_roles";
        $wpdb->insert($table, ['name' => 'Treasurer', 'season' => 2024]);
        $a = (int) $wpdb->insert_id;
        $wpdb->insert($table, ['name' => 'treasurer', 'season' => 2025]);
        $b = (int) $wpdb->insert_id;
        $wpdb->insert($table, ['name' => 'Goblin', 'season' => 2023]);
        $c = (int) $wpdb->insert_id;

        $this->roles->backfillLineages();

        $this->assertSame($a, $this->roles->lineageOf($a));
        $this->assertSame($a, $this->roles->lineageOf($b));
        $this->assertSame($c, $this->roles->lineageOf($c));
    }

    // ********************************* //
    // Circles: roles inside roles

    private function circle(string $name, int $season = 2027, int $parent = 0, int $order = 0): int
    {
        return $this->roles->upsertRole(['name' => $name, 'season' => $season, 'parent_id' => $parent, 'sort_order' => $order]);
    }

    public function testRolesAreListedAsATreeWithTheirCircleFirst()
    {
        $anchor = $this->circle('Anchor Circle', 2027, 0, 1);
        $lead = $this->circle('Camp Lead', 2027, $anchor, 10);
        $placement = $this->circle('Placement', 2027, $anchor, 50);
        $qm = $this->circle('Quartermaster', 2027, $placement, 10);
        $this->circle('Loose Role', 2027, 0, 5);

        $roles = $this->roles->getRoles(2027);

        $this->assertSame(['Anchor Circle', 'Camp Lead', 'Placement', 'Quartermaster', 'Loose Role'], array_column($roles, 'name'));
        $this->assertSame([0, 1, 1, 2, 0], array_column($roles, 'depth'));
        $this->assertSame('Anchor Circle › Placement › Quartermaster', $roles[3]['path']);
        $this->assertSame([true, false, true, false, false], array_column($roles, 'is_circle'));
        $this->assertSame([null, $anchor, $anchor, $placement, null], array_column($roles, 'parent_id'));
    }

    public function testARoleCanOnlySitInACircleFromItsOwnSeasonAndNeverInsideItself()
    {
        $a = $this->circle('A');
        $b = $this->circle('B', 2027, $a);
        $c = $this->circle('C', 2027, $b);
        $other = $this->circle('Other', 2025);

        foreach ([[$a, $a], [$a, $c], [$b, $c]] as [$role, $parent]) {
            try {
                $this->roles->upsertRole(['name' => 'A', 'parent_id' => $parent], $role);
                $this->fail('a loop was accepted');
            } catch (\Exception $e) {
                $this->assertStringContainsString('inside', $e->getMessage());
            }
        }
        try {
            $this->roles->upsertRole(['name' => 'C', 'parent_id' => $other], $c);
            $this->fail('a circle from another season was accepted');
        } catch (\Exception $e) {
            $this->assertStringContainsString('own season', $e->getMessage());
        }

        $this->roles->upsertRole(['name' => 'C', 'parent_id' => $a], $c); // moving up is fine
        $this->assertSame($a, $this->roles->getRole($c)['parent_id']);
        $this->roles->upsertRole(['name' => 'C', 'description' => 'no parent given'], $c); // key left out: unchanged
        $this->assertSame($a, $this->roles->getRole($c)['parent_id']);
        $this->roles->upsertRole(['name' => 'C', 'parent_id' => 0], $c);
        $this->assertNull($this->roles->getRole($c)['parent_id']);
        $this->assertSame([$a, $b, $c], array_column($this->roles->parentOptions(2027, null), 'id'));
        $this->assertSame([$c], array_column($this->roles->parentOptions(2027, $a), 'id'), 'not itself or what is inside it');
    }

    public function testEveryCircleHasItsOwnCircleLeadWithItsOwnHistory()
    {
        $placement25 = $this->circle('Placement', 2025);
        $lead25 = $this->circle('Circle Lead', 2025, $placement25);
        $comms25 = $this->circle('Communications', 2025);
        $comms_lead25 = $this->circle('Circle Lead', 2025, $comms25);
        $placement27 = $this->circle('Placement', 2027);
        $lead27 = $this->circle('Circle Lead', 2027, $placement27);
        $comms27 = $this->circle('Communications', 2027);
        $comms_lead27 = $this->circle('Circle Lead', 2027, $comms27);

        $this->assertSame($this->roles->lineageOf($lead25), $this->roles->lineageOf($lead27));
        $this->assertSame($this->roles->lineageOf($comms_lead25), $this->roles->lineageOf($comms_lead27));
        $this->assertNotSame($this->roles->lineageOf($lead27), $this->roles->lineageOf($comms_lead27));

        $titles = array_column($this->roles->getRoles(2027), 'title', 'id');
        $this->assertSame('Circle Lead (Placement)', $titles[$lead27]);
        $this->assertSame('Circle Lead (Communications)', $titles[$comms_lead27]);
        $this->assertSame('Placement', $titles[$placement27], 'a name that is unique stays as it is');
    }

    public function testARoleThatMovedIntoACircleKeepsItsHistory()
    {
        $old = $this->circle('Quartermaster', 2025);
        $placement = $this->circle('Placement', 2027);
        $new = $this->circle('Quartermaster', 2027, $placement);

        $this->assertSame($this->roles->lineageOf($old), $this->roles->lineageOf($new));
    }

    public function testHoldersOfSeveralCircleLeadsAreKeptApartOnTheirProfile()
    {
        $wpid = self::factory()->user->create();
        $placement = $this->circle('Placement');
        $comms = $this->circle('Communications');
        $lead = $this->circle('Circle Lead', 2027, $placement);
        $this->circle('Circle Lead', 2027, $comms);
        $camp_lead = $this->circle('Camp Lead');
        $member = $this->rosterMember($wpid, 2027);
        $this->roles->setRoleMembers($lead, [$member]);
        $this->roles->setRoleMembers($camp_lead, [$member]);

        $this->assertSame(['Camp Lead' => [2027], 'Circle Lead (Placement)' => [2027]], $this->roles->rolesByYearForUser($wpid));
        $this->assertSame([$member => ['Circle Lead (Placement)', 'Camp Lead']], array_map(
            function ($names) { rsort($names); return $names; },
            $this->roles->getRoleNamesByMember([$member])
        ));
    }

    public function testCopyingRolesKeepsTheCircles()
    {
        $anchor = $this->circle('Anchor', 2025);
        $placement = $this->circle('Placement', 2025, $anchor);
        $this->circle('Circle Lead', 2025, $placement);
        $this->circle('Circle Lead', 2025, $anchor);

        $this->roles->copyRoles(2025, 2027);

        $roles = $this->roles->getRoles(2027);
        $this->assertSame(['Anchor', 'Circle Lead', 'Placement', 'Circle Lead'], array_column($roles, 'name'));
        $this->assertSame([0, 1, 1, 2], array_column($roles, 'depth'));
        $this->assertSame('Anchor › Placement › Circle Lead', $roles[3]['path']);
        $this->assertSame(0, count(array_intersect(array_column($roles, 'id'), array_column($this->roles->getRoles(2025), 'id'))), 'copies, not the originals');
    }

    public function testDeletingACircleLeavesItsRolesAtTheTopLevel()
    {
        $circle = $this->circle('Placement');
        $qm = $this->circle('Quartermaster', 2027, $circle);

        $this->roles->deleteRoles([$circle]);

        $this->assertNull($this->roles->getRole($qm)['parent_id']);
        $this->assertSame(['Quartermaster'], array_column($this->roles->getRoles(2027), 'name'));
    }

    public function testARoleWhoseCircleIsMissingIsStillListed()
    {
        global $wpdb;
        $lost = $this->circle('Lost');
        $wpdb->update("{$wpdb->prefix}mf_roles", ['parent_id' => 999999], ['id' => $lost]);
        $a = $this->circle('A');
        $b = $this->circle('B', 2027, $a);
        $wpdb->update("{$wpdb->prefix}mf_roles", ['parent_id' => $b], ['id' => $a]); // a loop

        $names = array_column($this->roles->getRoles(2027), 'name');
        sort($names);

        $this->assertSame(['A', 'B', 'Lost'], $names);
    }

    public function testShortcodeNestsRolesInsideTheirCircle()
    {
        $anchor = $this->circle('Anchor Circle');
        $placement = $this->circle('Placement', 2027, $anchor);
        $qm = $this->circle('Quartermaster', 2027, $placement);
        $this->roles->setRoleMembers($qm, [$this->rosterMember(0, 2027, 'Confirmed', 'Sparkle')]);

        $html = do_shortcode('[camp_manager_roles]');

        $this->assertMatchesRegularExpression('#Anchor Circle.*camp-manager-circle-roles.*Placement.*camp-manager-circle-roles.*Quartermaster.*Sparkle#s', $html);
        $this->assertSame(2, substr_count($html, 'camp-manager-circle-roles'), 'only circles hold a block of roles');
        $this->assertSame(2, substr_count($html, '(circle)'));
        $this->assertStringNotContainsString('Open', $html, 'a circle is not shown as an unfilled role');
    }

    public function testTheDefaultStructureIsAppliedToASeasonOnce()
    {
        $lead = $this->circle('Camp Lead', 2027, 0, 10);
        $qm = $this->circle('Quartermaster', 2027, 0, 20);
        $treasurer = $this->circle('Treasurer', 2027, 0, 30);
        $kept = $this->circle('Welcome Wagon', 2027, $treasurer, 40); // already placed by hand: left alone
        $old_circle_lead = $this->circle('Circle Lead', 2025);
        $circle_lead = $this->circle('Circle Lead', 2027, 0, 50);
        $member = $this->rosterMember(0, 2027);
        $this->roles->setRoleMembers($qm, [$member]);

        $created = $this->roles->applyDefaultStructure(2027);

        $roles = array_column($this->roles->getRoles(2027), null, 'id');
        $by_path = array_column($roles, 'id', 'path');
        $anchor = (int) $by_path['Mycodelic Forest Anchor Circle'];
        $this->assertSame($anchor, $roles[$lead]['parent_id']);
        $this->assertSame($anchor, $roles[$circle_lead]['parent_id'], 'the existing Circle Lead is the anchor circle\'s');
        $this->assertSame(5, (int) $roles[$circle_lead]['sort_order'], 'and comes first in it');
        $this->assertSame($treasurer, $roles[$kept]['parent_id']);
        $placement = (int) $by_path['Mycodelic Forest Anchor Circle › Placement'];
        $this->assertSame($placement, $roles[$qm]['parent_id']);
        $this->assertSame([$member], array_map('intval', array_column($roles[$qm]['members'], 'id')), 'holders are not touched');
        $this->assertNotContains('Camp Lead', $created);
        $this->assertNotContains('Quartermaster', $created);
        $this->assertContains('Webmaster', $created);

        foreach (['Communications', 'Placement', 'Sojourner'] as $circle) {
            $this->assertArrayHasKey("Mycodelic Forest Anchor Circle › $circle › Circle Lead", $by_path);
        }
        $this->assertSame(['Circle Lead', 'Webmaster'], array_values(array_filter(array_column(
            array_filter($roles, function ($r) use ($by_path) { return $r['parent_id'] === (int) $by_path['Mycodelic Forest Anchor Circle › Communications']; }),
            'name'
        ), function ($n) { return in_array($n, ['Circle Lead', 'Webmaster'], true); })));

        $lineages = [];
        foreach (array_filter($roles, function ($r) { return $r['name'] === 'Circle Lead'; }) as $r) {
            $lineages[] = $this->roles->lineageOf($r['id']);
        }
        $this->assertCount(4, array_unique($lineages), 'each circle has its own Circle Lead');
        $this->assertContains($this->roles->lineageOf($old_circle_lead), $lineages, 'the anchor circle\'s Circle Lead continues the old one');

        $this->assertSame([], $this->roles->applyDefaultStructure(2027), 'running it again adds nothing');
        $this->assertCount(count($roles), $this->roles->getRoles(2027));
    }

    // ********************************* //
    // Import / export

    private function structure(int $season): array
    {
        return array_map(function ($r) {
            $permissions = $r['permissions'];
            sort($permissions);
            return [$r['path'], $r['description'], $permissions, (int) $r['sort_order']];
        }, $this->roles->getRoles($season));
    }

    private function clearRoles()
    {
        global $wpdb;
        foreach (['mf_role_members', 'mf_roles'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        CampManagerRoles::flushCache();
    }

    public function testRolesSurviveAnExportAndImportWithTheirCirclesAndHistory()
    {
        $goblin = $this->roles->upsertRole(['name' => 'Goblin', 'description' => 'Money', 'permissions' => ['finances'], 'season' => 2023]);
        $anchor = $this->circle('Anchor', 2025, 0, 1);
        $treasurer = $this->roles->upsertRole(['name' => 'Treasurer', 'description' => 'Manages <strong>money</strong>', 'permissions' => ['finances', 'budgets'], 'sort_order' => 30, 'season' => 2025, 'parent_id' => $anchor, 'same_as' => $goblin]);
        $placement = $this->circle('Placement', 2025, $anchor, 50);
        $lead = $this->circle('Circle Lead', 2025, $placement, 5);
        $comms = $this->circle('Communications', 2025, $anchor, 60);
        $comms_lead = $this->circle('Circle Lead', 2025, $comms, 5);
        $member = $this->rosterMember(0, 2025);
        $this->roles->setRoleMembers($treasurer, [$member]);
        $before = [$this->structure(2023), $this->structure(2025)];

        $json = wp_json_encode($this->roles->exportRoles());
        $this->assertStringNotContainsString('Camper', $json, 'holders are not exported');
        $this->clearRoles();
        $result = $this->roles->importRolesFromJson($json);

        $this->assertSame(7, $result['created']);
        $this->assertSame([$before[0], $before[1]], [$this->structure(2023), $this->structure(2025)]);
        $imported = array_column($this->roles->getRoles(2025), null, 'path');
        $this->assertSame($this->roles->lineageOf((int) $this->roles->getRoles(2023)[0]['id']), $this->roles->lineageOf((int) $imported['Anchor › Treasurer']['id']), 'the rename is still one role');
        $this->assertNotSame(
            $this->roles->lineageOf((int) $imported['Anchor › Placement › Circle Lead']['id']),
            $this->roles->lineageOf((int) $imported['Anchor › Communications › Circle Lead']['id'])
        );
        $this->assertSame([], $imported['Anchor › Treasurer']['members'], 'holders come from the site\'s own roster');
    }

    public function testImportingAgainChangesNothingAndTheFileUpdatesWhatIsThere()
    {
        $anchor = $this->circle('Anchor', 2027);
        $qm = $this->roles->upsertRole(['name' => 'Quartermaster', 'description' => 'Old', 'season' => 2027, 'parent_id' => $anchor]);
        $keep = $this->circle('Not In The File', 2027);
        $member = $this->rosterMember(0, 2027);
        $this->roles->setRoleMembers($qm, [$member]);
        $file = $this->roles->exportRoles(2027);

        $again = $this->roles->importRoles($file);
        $this->assertSame([0, 0, 3], [$again['created'], $again['updated'], $again['unchanged']]);

        foreach ($file['seasons'][2027] as &$role) {
            if ($role['name'] === 'Quartermaster') {
                $role['description'] = 'Lays out camp';
                $role['permissions'] = ['inventory', 'bogus'];
            }
        }
        unset($role);
        $file['seasons'][2027][] = ['id' => 99, 'parent' => null, 'lineage' => 99, 'name' => 'Brand New', 'description' => '', 'permissions' => [], 'sort_order' => 0];
        $result = $this->roles->importRoles($file);

        $this->assertSame([1, 1, 2], [$result['created'], $result['updated'], $result['unchanged']]);
        $roles = array_column($this->roles->getRoles(2027), null, 'name');
        $this->assertSame('Lays out camp', $roles['Quartermaster']['description']);
        $this->assertSame(['inventory'], $roles['Quartermaster']['permissions'], 'unknown access is dropped');
        $this->assertSame((int) $qm, (int) $roles['Quartermaster']['id'], 'updated in place');
        $this->assertSame([$member], array_map('intval', array_column($roles['Quartermaster']['members'], 'id')), 'holders are untouched');
        $this->assertArrayHasKey('Not In The File', $roles, 'nothing is deleted');
    }

    public function testAPreviewChangesNothing()
    {
        $this->circle('Anchor', 2027);
        $file = $this->roles->exportRoles(2027);
        $file['seasons'][2027][] = ['id' => 5, 'parent' => $file['seasons'][2027][0]['id'], 'lineage' => 5, 'name' => 'Webmaster', 'description' => '', 'permissions' => [], 'sort_order' => 0];

        $preview = $this->roles->importRoles($file, true);

        $this->assertTrue($preview['dry_run']);
        $this->assertSame([1, 0, 1], [$preview['created'], $preview['updated'], $preview['unchanged']]);
        $this->assertSame(['Anchor'], array_column($this->roles->getRoles(2027), 'name'));
    }

    public function testAnImportedSeasonOnlyNeedsToMatchByNameAndCircle()
    {
        // The same names in different circles are different roles, and a role in the file
        // whose circle is missing here is placed in the circle the file describes.
        $comms = $this->circle('Communications', 2027);
        $this->circle('Circle Lead', 2027, $comms);
        $file = ['format' => 'camp-manager-roles', 'version' => 1, 'seasons' => ['2027' => [
            ['id' => 1, 'parent' => null, 'name' => 'Communications'],
            ['id' => 2, 'parent' => 1, 'name' => 'Circle Lead'],
            ['id' => 3, 'parent' => null, 'name' => 'Placement'],
            ['id' => 4, 'parent' => 3, 'name' => 'Circle Lead'],
        ]]];

        $result = $this->roles->importRoles($file);

        $this->assertSame([2, 0], [$result['created'], $result['updated']]);
        $this->assertSame(['Communications', 'Communications › Circle Lead', 'Placement', 'Placement › Circle Lead'], array_column($this->roles->getRoles(2027), 'path'));
    }

    public function testABadRolesFileIsRefusedBeforeAnythingIsChanged()
    {
        $good = ['id' => 1, 'parent' => null, 'name' => 'Fine'];
        $bad = [
            'not json'                 => 'nope',
            'wrong format'             => ['format' => 'something-else', 'version' => 1, 'seasons' => []],
            'wrong version'            => ['format' => 'camp-manager-roles', 'version' => 2, 'seasons' => []],
            'no name'                  => ['format' => 'camp-manager-roles', 'version' => 1, 'seasons' => ['2027' => [$good, ['id' => 2, 'name' => ' ']]]],
            'duplicate id'             => ['format' => 'camp-manager-roles', 'version' => 1, 'seasons' => ['2027' => [$good, ['id' => 1, 'name' => 'Twin']]]],
            'circle that is not there' => ['format' => 'camp-manager-roles', 'version' => 1, 'seasons' => ['2027' => [$good, ['id' => 2, 'parent' => 9, 'name' => 'Orphan']]]],
            'circles in each other'    => ['format' => 'camp-manager-roles', 'version' => 1, 'seasons' => ['2027' => [['id' => 1, 'parent' => 2, 'name' => 'A'], ['id' => 2, 'parent' => 1, 'name' => 'B']]]],
            'not a year'               => ['format' => 'camp-manager-roles', 'version' => 1, 'seasons' => ['soon' => [$good]]],
        ];
        foreach ($bad as $why => $data) {
            try {
                is_array($data) ? $this->roles->importRoles($data) : $this->roles->importRolesFromJson($data);
                $this->fail("accepted a file with $why");
            } catch (\InvalidArgumentException $e) {
                $this->assertNotSame('', $e->getMessage(), $why);
            }
        }
        $this->assertSame([], $this->roles->getRoles(2027), 'a refused file adds nothing, even the roles before the bad one');
    }

}
