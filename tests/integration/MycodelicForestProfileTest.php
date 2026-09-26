<?php

class MycodelicForestProfileTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

     protected $wpid;
    protected function _before()
    {

        $faker = Faker\Factory::create();
        $user_login = $faker->userName();
        // Faker's US phone formats include "1-979-962-6478", "001-234-567-8901" and
        // "x1234" extensions, none of which count as a complete US number, so the old
        // random value made this suite flaky. Generate a valid (###) ###-#### instead.
        $phone_number = $faker->numerify('(###) ###-####');
        $user_data = [
            'user_login' => $user_login,
            'user_email' => "{$user_login}@mailinator.com",
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'user_phone' => $phone_number,
            'address_1' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'zip' => $faker->postcode(),
            'country' => 'United States',
            'playa_name' => "Acid " . $faker->word(),
            'about_me' => $faker->sentence(),
            'has_attended_burning_man' => 'Yes',
            'years_attended' => '["2024"]',
        ];


        codecept_debug($user_data);

        $this->wpid = wp_insert_user([
            'user_login' => $user_data['user_login'],
            'user_pass' => 'password',
            'user_email' => $user_data['user_email']
        ]);
        
        update_user_meta($this->wpid, 'first_name', $user_data['first_name']);
        update_user_meta($this->wpid, 'last_name', $user_data['last_name']);
        update_user_meta($this->wpid, 'user_phone', $user_data['user_phone']);
        update_user_meta($this->wpid, 'address_1', $user_data['address_1']);
        update_user_meta($this->wpid, 'city', $user_data['city']);
        update_user_meta($this->wpid, 'state', $user_data['state']);
        update_user_meta($this->wpid, 'zip', $user_data['zip']);
        update_user_meta($this->wpid, 'country', 'United States');
        update_user_meta($this->wpid, 'user_about_me', $user_data['about_me']);
        update_user_meta($this->wpid, 'playa_name', $user_data['playa_name']);
        update_user_meta($this->wpid, 'has_attended_burning_man', $user_data['has_attended_burning_man']);
        update_user_meta($this->wpid, 'years_attended', $user_data['years_attended']);

    }

    public function testProfileCompleteTrue()
    {
        $MycodelicForestProfile = $this->make('MycodelicForestProfile', []);

        $results = $MycodelicForestProfile->profileComplete($this->wpid);
        codecept_debug($results);

        $this->assertTrue($results);
    }

    public function testProfileCompleteFalse()
    {
        $MycodelicForestProfile = $this->make('MycodelicForestProfile', []);

        $results = $MycodelicForestProfile->profileComplete(1);
        codecept_debug($results);

        $this->assertFalse($results);
    }

    public function testGetProfile()
    {
        $MycodelicForestProfile = $this->make('MycodelicForestProfile', []);

        codecept_debug($this->wpid);

        wp_set_current_user($this->wpid);

        codecept_debug("WPID = {$this->wpid}");

        $results = $MycodelicForestProfile->get_profile($this->wpid);
        codecept_debug($results);

        $this->assertArrayHasKey('first_name', $results);
        $this->assertArrayHasKey('last_name', $results);
        $this->assertArrayHasKey('user_phone', $results);
        $this->assertArrayHasKey('address_1', $results);
        $this->assertArrayHasKey('city', $results);
        $this->assertArrayHasKey('state', $results);
        $this->assertArrayHasKey('zip', $results);
        $this->assertArrayHasKey('country', $results);
        $this->assertArrayHasKey('user_about_me', $results);
        $this->assertArrayHasKey('playa_name', $results);
        $this->assertArrayHasKey('has_attended_burning_man', $results);
        $this->assertArrayHasKey('years_attended', $results);
        $this->assertArrayHasKey('sponsor_user_id', $results);
    }

    // ------------------------------------------------------------------------------ sponsor

    private function profile(): MycodelicForestProfile
    {
        return new MycodelicForestProfile(new MycodelicForestMessages(), new MycodelicForestCiviCRM());
    }

    public function testSponsorIsSavedOnlyWhenItIsAnotherExistingMember()
    {
        $profile = $this->profile();
        $sponsor = self::factory()->user->create(['role' => 'subscriber']);

        $profile->update_extra_fields($this->wpid, ['sponsor_user_id' => (string) $sponsor]);
        $this->assertSame((string) $sponsor, get_user_meta($this->wpid, 'sponsor_user_id', true));
        $this->assertSame($sponsor, $profile->get_sponsor($this->wpid)->ID);
        $this->assertSame((string) $sponsor, $profile->get_profile($this->wpid)['sponsor_user_id']);

        $profile->update_extra_fields($this->wpid, ['sponsor_user_id' => (string) $this->wpid]);
        $this->assertSame('', get_user_meta($this->wpid, 'sponsor_user_id', true), 'Nobody sponsors themselves');

        $profile->update_extra_fields($this->wpid, ['sponsor_user_id' => (string) $sponsor]);
        $profile->update_extra_fields($this->wpid, ['sponsor_user_id' => '999999']);
        $this->assertSame('', get_user_meta($this->wpid, 'sponsor_user_id', true), 'An unknown user clears it');

        $profile->update_extra_fields($this->wpid, ['sponsor_user_id' => (string) $sponsor]);
        $profile->update_extra_fields($this->wpid, ['playa_name' => 'Untouched']);
        $this->assertSame((string) $sponsor, get_user_meta($this->wpid, 'sponsor_user_id', true), 'A save without the field (the front-end profile form) leaves it alone');

        $profile->update_extra_fields($this->wpid, ['sponsor_user_id' => '']);
        $this->assertSame('', get_user_meta($this->wpid, 'sponsor_user_id', true), '"None" clears it');
        $this->assertNull($profile->get_sponsor($this->wpid));
    }

    public function testSponsorPickerListsOtherMembersByNameAndKeepsTheChoice()
    {
        $profile = $this->profile();
        $zed = self::factory()->user->create(['role' => 'subscriber', 'first_name' => 'Zed', 'last_name' => 'Last']);
        $amy = self::factory()->user->create(['role' => 'subscriber', 'first_name' => 'Amy', 'last_name' => 'First']);
        update_user_meta($amy, 'playa_name', 'Sparkle');
        update_user_meta($this->wpid, 'sponsor_user_id', $amy);

        $options = $profile->sponsor_options($this->wpid);
        $this->assertArrayNotHasKey($this->wpid, $options, 'Not yourself');
        $this->assertSame('Amy First (Sparkle)', $options[$amy]);
        $this->assertSame('Zed Last', $options[$zed]);
        $this->assertLessThan(array_search($zed, array_keys($options), true), array_search($amy, array_keys($options), true), 'By name');

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        ob_start();
        $profile->show_extra_fields(get_userdata($this->wpid));
        $html = ob_get_clean();
        $this->assertStringContainsString('<label for="sponsor_user_id">Sponsor</label>', $html);
        $this->assertMatchesRegularExpression('#<select name="sponsor_user_id" id="sponsor_user_id">\s*<option value="">None</option>#', $html);
        $this->assertMatchesRegularExpression('#<option value="' . $amy . '"\s+selected=\'selected\'>\s*Amy First \(Sparkle\)#', $html);
        $this->assertStringContainsString('The member who invited you to camp with us.', $html);
    }

    public function testProfilePageSaysWhoInvitedTheMember()
    {
        $profile = $this->profile();
        $sponsor = self::factory()->user->create(['role' => 'subscriber', 'first_name' => 'Amy', 'last_name' => 'First', 'user_nicename' => 'amy-first']);
        update_user_meta($sponsor, 'playa_name', 'Sparkle');
        update_user_meta($this->wpid, 'sponsor_user_id', $sponsor);

        $this->go_to(home_url('/?author=' . $this->wpid));
        $this->assertSame(
            'Invited by <a href="' . home_url('/profile/amy-first/') . '">Amy First (Sparkle)</a>',
            $profile->resolve_profile_binding(['key' => 'sponsor'])
        );

        $this->go_to(home_url('/?author=' . $sponsor));
        $this->assertSame('', $profile->resolve_profile_binding(['key' => 'sponsor']), 'Nothing when no sponsor is recorded');
    }
}


