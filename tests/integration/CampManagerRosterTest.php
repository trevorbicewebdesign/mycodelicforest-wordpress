<?php

class CampManagerRosterTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-roster.php');
    }

    public function testAddUpdateAndRemoveMember()
    {
        global $wpdb;
        $wpdb->query('COMMIT');
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }

        $roster = $this->make('CampManagerRoster', []);

        $id = $roster->updateMember([
            'low_income'    => 0,
            'fully_paid'    => 1,
            'fname'         => 'John',
            'lname'         => 'Doe',
            'playaname'     => 'Johnny',
            'email'         => 'john.doe@example.com',
            'member_status' => 'confirmed',
        ]);
        $this->assertGreaterThan(0, (int) $id, 'updateMember() without an id should insert and return the new id');

        $member = $roster->getMemberById($id);
        $this->assertNotNull($member, 'Member was not inserted');
        $this->assertSame('John', $member->fname);
        $this->assertSame('Johnny', $member->playaname);
        $this->assertSame('john.doe@example.com', $member->email);

        // Update by id
        $sameId = $roster->updateMember([
            'id' => $id, 'fname' => 'Jane', 'lname' => 'Doe', 'playaname' => 'Janey', 'email' => 'jane.doe@example.com',
        ]);
        $this->assertEquals($id, $sameId);
        $this->assertSame('Jane', $roster->getMemberById($id)->fname);

        // Remove
        $roster->removeMember($id);
        $this->assertNull($roster->getMemberById($id));
    }

    public function testUpdateMemberPersistsWpid()
    {
        global $wpdb;
        $wpdb->query('COMMIT');
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }

        $userId = self::factory()->user->create();

        $roster = $this->make('CampManagerRoster', []);

        // Insert without a wpid: the column is NOT NULL with no default, so it should be stored as 0.
        $id = $roster->updateMember([
            'fname'         => 'Alex',
            'lname'         => 'Roe',
            'playaname'     => 'Rex',
            'email'         => 'alex.roe@example.com',
            'member_status' => 'confirmed',
        ]);
        $member = $roster->getMemberById($id);
        $this->assertSame(0, (int) $member->wpid, 'wpid should default to 0 when not provided');

        // Insert with a wpid.
        $withWpidId = $roster->updateMember([
            'fname'         => 'Sam',
            'lname'         => 'Roe',
            'playaname'     => 'Sammy',
            'email'         => 'sam.roe@example.com',
            'member_status' => 'confirmed',
            'wpid'          => $userId,
        ]);
        $this->assertSame($userId, (int) $roster->getMemberById($withWpidId)->wpid, 'wpid should be persisted on insert');

        // Update an existing member's wpid.
        $sameId = $roster->updateMember([
            'id'            => $id,
            'fname'         => 'Alex',
            'lname'         => 'Roe',
            'playaname'     => 'Rex',
            'email'         => 'alex.roe@example.com',
            'member_status' => 'confirmed',
            'wpid'          => $userId,
        ]);
        $this->assertEquals($id, $sameId);
        $this->assertSame($userId, (int) $roster->getMemberById($id)->wpid, 'wpid should be persisted on update');

        $roster->removeMember($id);
        $roster->removeMember($withWpidId);
    }
}
