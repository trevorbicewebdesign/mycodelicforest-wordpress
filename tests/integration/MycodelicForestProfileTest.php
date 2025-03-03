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
        $phone_number = str_replace("+1 ","", $faker->phoneNumber());
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
        
    }

}


