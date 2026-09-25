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
        if (!current_user_can(CampManagerRoles::cap('roster'))) {
            wp_die('Unauthorized');
        }

        try {
              $member_id = $this->updateMember([
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'fname' => isset($_POST['member_fname']) ? sanitize_text_field($_POST['member_fname']) : '',
                'lname' => isset($_POST['member_lname']) ? sanitize_text_field($_POST['member_lname']) : '',
                'playaname' => isset($_POST['member_playaname']) ? sanitize_text_field($_POST['member_playaname']) : '',
                'email' => isset($_POST['member_email']) ? sanitize_email($_POST['member_email']) : '',
                'wpid' => !empty($_POST['wpid']) ? (int)$_POST['wpid'] : null,
                'low_income' => isset($_POST['member_low_income']) ? (int)$_POST['member_low_income'] : null,
                'fully_paid' => isset($_POST['member_fully_paid']) ? (int)$_POST['member_fully_paid'] : null,
                'season' => isset($_POST['season']) ? (int)$_POST['season'] : null,
                'member_status' => isset($_POST['member_status']) ? sanitize_text_field($_POST['member_status']) : '',
            ]);
            $this->saveSubmittedRoles((int) $member_id);
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-add-member&error=' . urlencode($e->getMessage())));
            exit;
        }
        // Stay on the member's edit form: for a new member that is the id we just created,
        // not the (empty) id from the request.
        wp_redirect(admin_url('admin.php?page=camp-manager-add-member&id=' . intval($member_id)));
        exit;
    }

    public function handle_member_save_close(){
        if (!current_user_can(CampManagerRoles::cap('roster'))) {
            wp_die('Unauthorized');
        }

        $member_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $data = [
            'id' => $member_id,
            'fname' => isset($_POST['member_fname']) ? sanitize_text_field($_POST['member_fname']) : '',
            'lname' => isset($_POST['member_lname']) ? sanitize_text_field($_POST['member_lname']) : '',
            'playaname' => isset($_POST['member_playaname']) ? sanitize_text_field($_POST['member_playaname']) : '',
            'email' => isset($_POST['member_email']) ? sanitize_email($_POST['member_email']) : '',
            'wpid' => !empty($_POST['wpid']) ? (int)$_POST['wpid'] : null,
            'low_income' => isset($_POST['member_low_income']) ? (int)$_POST['member_low_income'] : null,
            'fully_paid' => isset($_POST['member_fully_paid']) ? (int)$_POST['member_fully_paid'] : null,
            'season' => isset($_POST['season']) ? (int)$_POST['season'] : null,
            'member_status' => isset($_POST['member_status']) ? sanitize_text_field($_POST['member_status']) : '',
        ];

        try {
            $member_id = $this->updateMember($data);
            $this->saveSubmittedRoles((int) $member_id);
            wp_redirect(admin_url('admin.php?page=camp-manager-members&success=1'));
            exit;
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-add-member&error=' . urlencode($e->getMessage())));
            exit;
        }
    }

    /**
     * Roles submitted from the member form. Only admins may assign them (a role can grant
     * Camp Manager access), and only when the form actually carried the field, so a form
     * without it leaves the member's roles alone.
     */
    private function saveSubmittedRoles(int $member_id)
    {
        if (!$member_id || empty($_POST['member_roles_submitted']) || !current_user_can('manage_options')) {
            return;
        }
        (new CampManagerRoles())->setMemberRoles($member_id, isset($_POST['member_roles']) ? (array) $_POST['member_roles'] : []);
    }

    /** Season every roster count and list is scoped to (what the admin is viewing). */
    private function season(): int
    {
        return CampManagerSeason::selected();
    }

    public function countRosterMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE season = %d", $this->season());
        return $wpdb->get_var($query);
    }

    /** Member counts for the roster's status views (All / Confirmed / Dropped) in the viewed season. */
    public function countByStatus(): array
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status = 'Confirmed'), 0) AS confirmed,
                    COALESCE(SUM(status = 'Dropped'), 0) AS dropped
             FROM $table_name WHERE season = %d",
            $this->season()
        ), ARRAY_A);

        return [
            'all'       => (int) ($row['total'] ?? 0),
            'confirmed' => (int) ($row['confirmed'] ?? 0),
            'dropped'   => (int) ($row['dropped'] ?? 0),
        ];
    }

    /** The figures in the roster's "Season overview" box, for the viewed season. */
    public function seasonOverview(CampManagerLedger $ledger): array
    {
        return [
            'total'                 => (int) $this->countRosterMembers(),
            'confirmed'             => (int) $this->countConfirmedRosterMembers(),
            'unpaid'                => (int) $this->countUnpaidMembers(),
            'dues_collected'        => (float) $ledger->totalCampDues(),
            'dues_expected'         => (float) $this->expectedCampDuesRevenue(),
            'low_income'            => (int) $this->countLowIncomeMembers(),
            'low_income_dues_paid'  => (int) $this->countPaidLowIncomeCampDues(),
        ];
    }

    public function getRosterMembers(): array
    {
        // Get all members from mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE season = %d ORDER BY lname, fname", $this->season());
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
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE status = 'Confirmed' AND season = %d", $this->season());
        return $wpdb->get_var($query);
    }

    public function getConfirmedRosterMembers(): array
    {
        // Get all confirmed members from mf_roster
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE status = 'Confirmed' AND season = %d ORDER BY lname, fname", $this->season());
        $members = $wpdb->get_results($query, ARRAY_A);
        return $members ?: [];
    }

    public function countPaidCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE fully_paid = 1 AND season = %d", $this->season());
        return $wpdb->get_var($query);
    }

    public function countUnpaidCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        // Count where fully_paid is NULL or 0 and status is 'confirmed'
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE (fully_paid IS NULL OR fully_paid = 0) AND status = 'Confirmed' AND season = %d", $this->season());
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
            FROM $table_name WHERE season = %d";
        return $wpdb->get_var($wpdb->prepare($query, $this->season()));
    }
    
    public function countLowIncomeMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE low_income = 1 AND status = 'confirmed' AND season = %d", $this->season());
        return $wpdb->get_var($query);
    }

    public function countUnpaidMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        // Count where fully_paid is NULL or 0 and status is 'confirmed'
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE (fully_paid IS NULL OR fully_paid = 0) AND status = 'Confirmed' AND season = %d", $this->season());
        return $wpdb->get_var($query);
    }


    // ********************************* //

   

    public function countUnpaidLowIncomeMembers()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE low_income = 1 AND (fully_paid IS NULL OR fully_paid = 0) AND season = %d", $this->season());
        return $wpdb->get_var($query);
    }

    public function countPaidLowIncomeCampDues()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_roster";
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE fully_paid = 1 AND low_income = 1 AND season = %d", $this->season());
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
            'wpid' => (int)($memberData['wpid'] ?? 0),
            'low_income' => isset($memberData['low_income']) ? (int)$memberData['low_income'] : null,
            'fully_paid' => isset($memberData['fully_paid']) ? (int)$memberData['fully_paid'] : null,
            'fname' => sanitize_text_field($memberData['fname']),
            'lname' => sanitize_text_field($memberData['lname']),
            'playaname' => sanitize_text_field($memberData['playaname']),
            'email' => sanitize_email($memberData['email']),
            'status' => isset($memberData['member_status']) ? sanitize_text_field($memberData['member_status']) : '',
        ];

        // An existing member keeps their season unless a new one is given; a new member
        // defaults to the season being viewed.
        $season = !empty($memberData['season']) ? (int)$memberData['season'] : null;

        if (isset($memberData['id']) && !empty($memberData['id'])) {
            if ($season) {
                $data['season'] = $season;
            }
            $result = $wpdb->update($table_name, $data, ['id' => (int)$memberData['id']]);
            if ($result === false) {
                throw new \Exception("Failed to update member in roster: {$wpdb->last_error}");
            }
            return $memberData['id'];
        } else {
            $data['season'] = $season ?: $this->season();
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