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
        add_action('admin_post_camp_manager_save_and_close_member', [$this, 'handle_member_save_close']);
    }

    public function handle_member_save()
    {
        
        // Handle saving a member from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
              $this->updateMember([
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'fname' => isset($_POST['member_fname']) ? sanitize_text_field($_POST['member_fname']) : '',
                'lname' => isset($_POST['member_lname']) ? sanitize_text_field($_POST['member_lname']) : '',
                'playaname' => isset($_POST['member_playaname']) ? sanitize_text_field($_POST['member_playaname']) : '',
                'email' => isset($_POST['member_email']) ? sanitize_email($_POST['member_email']) : '',
                //'wpid' => get_current_user_id(),
                'low_income' => isset($_POST['member_low_income']) ? (int)$_POST['member_low_income'] : null,
                'fully_paid' => isset($_POST['member_fully_paid']) ? (int)$_POST['member_fully_paid'] : null,
                'season' => isset($_POST['season']) ? (int)$_POST['season'] : null,
                'member_status' => isset($_POST['member_status']) ? sanitize_text_field($_POST['member_status']) : '',
            ]);
            
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-add-member&error=' . urlencode($e->getMessage())));
        }
        wp_redirect(admin_url('admin.php?page=camp-manager-add-member&id=' . (isset($_POST['id']) ? intval($_POST['id']) : 0) ));
        exit;
    }

    public function handle_member_save_close(){
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $member_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $data = [
            'id' => $member_id,
            'fname' => isset($_POST['member_fname']) ? sanitize_text_field($_POST['member_fname']) : '',
            'lname' => isset($_POST['member_lname']) ? sanitize_text_field($_POST['member_lname']) : '',
            'playaname' => isset($_POST['member_playaname']) ? sanitize_text_field($_POST['member_playaname']) : '',
            'email' => isset($_POST['member_email']) ? sanitize_email($_POST['member_email']) : '',
            //'wpid' => get_current_user_id(),
            'low_income' => isset($_POST['member_low_income']) ? (int)$_POST['member_low_income'] : null,
            'fully_paid' => isset($_POST['member_fully_paid']) ? (int)$_POST['member_fully_paid'] : null,
            'season' => isset($_POST['season']) ? (int)$_POST['season'] : null,
            'member_status' => isset($_POST['member_status']) ? sanitize_text_field($_POST['member_status']) : '',
        ];

        try {
            $this->updateMember($data);
            wp_redirect(admin_url('admin.php?page=camp-manager-members&success=1'));
            exit;
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-add-member&error=' . urlencode($e->getMessage())));
            exit;
        }
    }

    public function countRosterMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name";
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

    public function countConfirmedRosterMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE status = 'Confirmed'";
        return $wpdb->get_var($query);
    }

    public function getConfirmedRosterMembers(): array
    {
        // Get all confirmed members from mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT * FROM $table_name WHERE status = 'Confirmed' ORDER BY lname, fname";
        $members = $wpdb->get_results($query, ARRAY_A);
        return $members ?: [];
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
        // Count where fully_paid is NULL or 0 and status is 'confirmed'
        $query = "SELECT COUNT(*) FROM $table_name WHERE (fully_paid IS NULL OR fully_paid = 0) AND status = 'Confirmed'";
        return $wpdb->get_var($query);
    }

    public function totalUnpaidCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        // Count where fully_paid is NULL or 0 and status is 'confirmed'
        $query = "SELECT 
            SUM(
            CASE 
                WHEN (fully_paid IS NULL OR fully_paid = 0) AND status = 'Confirmed' AND low_income = 1 THEN 250
                WHEN (fully_paid IS NULL OR fully_paid = 0) AND status = 'Confirmed' AND (low_income IS NULL OR low_income = 0) THEN 350
                ELSE 0
            END
            ) 
            FROM $table_name";
        return $wpdb->get_var($query);
    }
    
    public function countLowIncomeMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE low_income = 1 AND status = 'confirmed'";
        return $wpdb->get_var($query);
    }

    public function countUnpaidMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        // Count where fully_paid is NULL or 0 and status is 'confirmed'
        $query = "SELECT COUNT(*) FROM $table_name WHERE (fully_paid IS NULL OR fully_paid = 0) AND status = 'Confirmed'";
        return $wpdb->get_var($query);
    }


    // ********************************* //

   

    public function countUnpaidLowIncomeMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE low_income = 1 AND (fully_paid IS NULL OR fully_paid = 0)";
        return $wpdb->get_var($query);
    }

    public function countPaidLowIncomeCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = "SELECT COUNT(*) FROM $table_name WHERE fully_paid = 1 AND low_income = 1";
        return $wpdb->get_var($query);
    }   

    // figure out how many regular and low income campers there are left to pay dues
    // use that information at 350 for regular and 250 for low income to calculate expected revenue remaining

    public function remainingCampDues(): float
    {
        $unpaid_members = $this->countUnpaidMembers();
        $unpaid_low_income_members = $this->countUnpaidLowIncomeMembers();
        $unpaid_full_price_members = $unpaid_members - $unpaid_low_income_members;

        $normal_camp_dues = 350;
        $low_income_camp_dues = 250;

        $normal_remaining_dues = $unpaid_full_price_members * $normal_camp_dues;
        $low_income_remaining_dues = $unpaid_low_income_members * $low_income_camp_dues;
        return $normal_remaining_dues + $low_income_remaining_dues;
    }

    public function expectedCampDuesRevenue(): float
    {
        $total_members = $this->countRosterMembers();
        $total_low_income = $this->countLowIncomeMembers();

        $normal_camp_dues = 350;
        $low_income_camp_dues = 250;

        return ($total_members - $total_low_income) * $normal_camp_dues + $total_low_income * $low_income_camp_dues;
    }

    // Should update or insert a member if the id is null
    public function updateMember($memberData)
    {
        // insert or update into mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $data = [
            // 'wpid' => $memberData['wpid'],
            'low_income' => isset($memberData['low_income']) ? (int)$memberData['low_income'] : null,
            'fully_paid' => isset($memberData['fully_paid']) ? (int)$memberData['fully_paid'] : null,
            'season' => 2025,
            'fname' => sanitize_text_field($memberData['fname']),
            'lname' => sanitize_text_field($memberData['lname']),
            'playaname' => sanitize_text_field($memberData['playaname']),
            'email' => sanitize_email($memberData['email']),
            'status' => isset($memberData['member_status']) ? sanitize_text_field($memberData['member_status']) : '',
        ];

        if (isset($memberData['id']) && !empty($memberData['id'])) {
            $result = $wpdb->update($table_name, $data, ['id' => (int)$memberData['id']]);
            if ($result === false) {
                throw new \Exception("Failed to update member in roster: {$wpdb->last_error}");
            }
            return $memberData['id'];
        } else {
            $result = $wpdb->insert($table_name, $data);
            if ($result === false) {
                throw new \Exception("Failed to insert member in roster: {$wpdb->last_error}");
            }
            return $wpdb->insert_id;
        }
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