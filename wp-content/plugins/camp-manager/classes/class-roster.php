<?php

class CampManagerRoster
{

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function init()
    {
        // add action processing for `camp_manager_save_member`
        add_action('admin_post_camp_manager_save_member', [$this, 'handle_member_save']);
    }

    public function handle_member_save()
    {
        
        // Handle saving a member from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
              $this->addMember([
                'fname' => sanitize_text_field($_POST['member_fname']),
                'lname' => sanitize_text_field($_POST['member_lname']),
                'playaname' => sanitize_text_field($_POST['member_playaname']),
                'email' => sanitize_email($_POST['member_email']),
                //'wpid' => get_current_user_id(),
                'low_income' => isset($_POST['low_income']) ? (int)$_POST['low_income'] : null,
                'fully_paid' => isset($_POST['fully_paid']) ? (int)$_POST['fully_paid'] : null,
                'season' => isset($_POST['season']) ? (int)$_POST['season'] : null,
            ]);
            
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-roster&error=' . urlencode($e->getMessage())));
        }
        wp_redirect(admin_url('admin.php?page=camp-manager-roster&success=member_added'));
        exit;
    }

    public function countLowIncomeMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE low_income = 1";
        return $wpdb->get_var($query);
    }

    public function countRosterMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name";
        return $wpdb->get_var($query);
    }

    public function countPaidCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE fully_paid = 1";
        return $wpdb->get_var($query);
    }

    public function countUnpaidCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        // Count where fully_paid is NULL or 0
        $query = "SELECT COUNT(*) FROM $table_name WHERE fully_paid IS NULL OR fully_paid = 0";
        return $wpdb->get_var($query);
    }

    public function countPaidLowIncomeCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE fully_paid = 1 AND low_income = 1";
        return $wpdb->get_var($query);
    }

    public function getRosterMembers(): array
    {
        // Get all members from mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT * FROM $table_name ORDER BY lname, fname";
        $members = $wpdb->get_results($query, ARRAY_A);
        return $members ?: [];
    }

    public function getMemberById($memberId)
    {
        // Get a specific member by ID from mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", (int)$memberId);
        $member = $wpdb->get_row($query);
        return $member ?: null;
    }

    public function addMember($memberData)
    {
        // insert into mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $data = [
            'wpid' => $memberData['wpid'],
            'low_income' => isset($memberData['low_income']) ? (int)$memberData['low_income'] : null,
            'fully_paid' => isset($memberData['fully_paid']) ? (int)$memberData['fully_paid'] : null,
            'season' => 2025,
            'fname' => sanitize_text_field($memberData['fname']),
            'lname' => sanitize_text_field($memberData['lname']),
            'playaname' => sanitize_text_field($memberData['playaname']),
            'email' => sanitize_email($memberData['email']),
        ];

        

        $result = $wpdb->insert($table_name, $data);
        if ($result === false) {
            throw new \Exception("Failed to insert member into roster: {$wpdb->last_error}");
        }
        return $wpdb->insert_id; // Return the ID of the newly added member
    }

    public function removeMember($memberId)
    {
        // Delete from mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $result = $wpdb->delete($table_name, ['id' => (int)$memberId]);
        if ($result === false) {
            throw new \Exception("Failed to delete member from roster: {$wpdb->last_error}");
        }
        return $result; // Return the number of rows affected
    }
}