<?php

// Check if the user has permission to manage options
if (!current_user_can(CampManagerRoles::cap('roster'))) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $member = $this->roster->getMemberById($id);
}

// we need to determine if we are editing or adding a new member
$is_edit = $id > 0;

// Prefill values if editing
$fname = $is_edit && isset($member->fname) ? esc_attr($member->fname) : '';
$lname = $is_edit && isset($member->lname) ? esc_attr($member->lname) : '';
$playaname = $is_edit && isset($member->playaname) ? esc_attr($member->playaname) : '';
$email = $is_edit && isset($member->email) ? esc_attr($member->email) : '';
$wpid = $is_edit && isset($member->wpid) ? esc_attr($member->wpid) : '';

// Camp roles for the member's season. Assigning them grants Camp Manager access, so like the
// role pages this stays admin-only; roster editors just see who holds what.
$roles_obj = new CampManagerRoles();
$member_season = $is_edit && !empty($member->season) ? (int) $member->season : CampManagerSeason::selected();
$season_roles = $roles_obj->getRoles($member_season);
$member_role_ids = $is_edit ? $roles_obj->getMemberRoleIds($id) : [];
$can_assign_roles = current_user_can('manage_options');

?>
<style>
    .row {
        display: flex;
        gap: 20px;
    }
    .col-md-6 {
        flex: 1;
    }
</style>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Member' : 'Add New Member'; ?></h1>
    <hr />
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="member-form">
        <input type="hidden" name="action" value="camp_manager_save_member" id="action-field">

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
        <?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <table class="form-table">
                    <tr>
                        <th><label for="season">Season</label></th>
                        <td>
                            <input type="number" name="season" id="season" class="small-text" min="2000" max="2100" required
                                value="<?php echo esc_attr($is_edit && !empty($member->season) ? $member->season : CampManagerSeason::selected()); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_fname">First Name</label></th>
                        <td>
                            <input type="text" name="member_fname" id="member_fname" class="regular-text" required
                                value="<?php echo $fname; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_lname">Last Name</label></th>
                        <td>
                            <input type="text" name="member_lname" id="member_lname" class="regular-text" required
                                value="<?php echo $lname; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_playaname">Playa Name</label></th>
                        <td>
                            <input type="text" name="member_playaname" id="member_playaname" class="regular-text"
                                value="<?php echo $playaname; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_email">Email</label></th>
                        <td>
                            <input type="email" name="member_email" id="member_email" class="regular-text"  
                                value="<?php echo $email; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wpid">wpid</label></th>
                        <td>
                            <?php
                            // Get all WordPress users
                            $users = get_users([
                                'fields' => ['ID', 'display_name', 'user_login', 'user_email']
                            ]);
                            ?>
                            <select name="wpid" id="wpid" class="regular-text">
                                <option value="">Select a WordPress user</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($wpid == $user->ID); ?>>
                                        <?php echo esc_html($user->display_name . " ({$user->user_login} - {$user->user_email})"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    <tr>
                        <th><label for="member_low_income">Low Income</label></th>
                        <td>
                            <input type="checkbox" name="member_low_income" id="member_low_income" value="1" <?php checked($is_edit && !empty($member->low_income)); ?>>
                            <label for="member_low_income">Check if low income</label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_fully_paid">Fully Paid</label></th>
                        <td>
                            <input type="checkbox" name="member_fully_paid" id="member_fully_paid" value="1" <?php checked($is_edit && !empty($member->fully_paid)); ?>>
                            <label for="member_fully_paid">Check if fully paid</label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_status">Status</label></th>
                        <td>
                            <select name="member_status" id="member_status">
                                <option value="">Select a status</option>
                                <option value="Confirmed" <?php selected($is_edit && isset($member->status) && $member->status == 'Confirmed'); ?>>Confirmed</option>
                                <option value="Very Maybe" <?php selected($is_edit && isset($member->status) && $member->status == 'Very Maybe'); ?>>Very Maybe</option>
                                <option value="Maybe" <?php selected($is_edit && isset($member->status) && $member->status == 'Maybe'); ?>>Maybe</option>
                                <option value="No" <?php selected($is_edit && isset($member->status) && $member->status == 'No'); ?>>No</option>
                                <option value="Dropped" <?php selected($is_edit && isset($member->status) && $member->status == 'Dropped'); ?>>Dropped</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_roles">Camp Roles</label></th>
                        <td>
                            <?php if ($can_assign_roles): ?>
                                <input type="hidden" name="member_roles_submitted" value="1">
                                <select name="member_roles[]" id="member_roles" multiple style="min-width: 25em;">
                                    <?php foreach ($season_roles as $role): ?>
                                        <option value="<?php echo esc_attr($role['id']); ?>" <?php selected(in_array((int) $role['id'], $member_role_ids, true)); ?>>
                                            <?php echo esc_html($role['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$season_roles): ?>
                                    <p class="description">No roles are set up for <?php echo (int) $member_season; ?> yet.
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=camp-manager-roles')); ?>">Manage camp roles</a></p>
                                <?php else: ?>
                                    <p class="description">Roles for the <?php echo (int) $member_season; ?> season. Holders can also be set from each role's edit page.</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php
                                $held = array_filter($season_roles, function ($role) use ($member_role_ids) {
                                    return in_array((int) $role['id'], $member_role_ids, true);
                                });
                                ?>
                                <?php echo $held ? esc_html(implode(', ', array_column($held, 'name'))) : '<em>None</em>'; ?>
                                <p class="description">Only administrators can assign camp roles.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="member_sponsor">Sponsor</label></th>
                        <td>
                            <?php
                            $roster_members = $this->roster->getRosterMembers();
                            ?>
                            <select name="member_sponsor" id="member_sponsor">
                                <option value="">Select a sponsor</option>
                                <?php
                                foreach ($roster_members as $roster_member) {
                                    $selected = ($is_edit && isset($member->sponsor_cmd) && $member->sponsor_cmd == $roster_member['id']) ? 'selected' : '';
                                    $display_name = esc_html($roster_member['fname'] . ' ' . $roster_member['lname'] . (!empty($roster_member['playaname']) ? " ({$roster_member['playaname']})" : ''));
                                    echo "<option value=\"" . esc_attr($roster_member['id']) . "\" $selected>$display_name</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <div style="display: flex; gap: 10px;">
                    <?php submit_button('Save Member', 'secondary', 'save_member', false, array('id' => 'save-btn')); ?>
                    <?php submit_button('Save & Close Member', 'primary', 'save_close_member', false, array('id' => 'save-close-btn')); ?>
                    <?php submit_button('Close Member', 'secondary', 'close_member', false, array('id' => 'close-btn', 'formnovalidate' => true)); ?>
                </div>

                <?php
                $return_url = '';
                if (!empty($_REQUEST['return'])) {
                    $return_url = base64_decode(sanitize_text_field($_REQUEST['return']));
                }
                ?>
                <input type="hidden" name="return_url" value="<?php echo esc_url( base64_encode( $return_url ? $return_url : "") ) ; ?>">                    
        </form>
    </div>
    <div class="col-md-6">
        
    </div>
</div>

</div>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    if ($.fn.select2 && $('#member_roles').length) {
        $('#member_roles').select2({ placeholder: 'Choose camp roles', width: '25em' });
    }

    // Set correct action on button click
    $('#save-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_member');
    });

    $('#save-close-btn').on('click', function() {
        $('#action-field').val('camp_manager_save_and_close_member');
    });

    // Track initial state for dirty check
    let initialForm = $('#member-form').serialize();
    $('#close-btn').on('click', function(e) {
        if ($('#member-form').serialize() !== initialForm) {
            if (!confirm('You have unsaved changes. Are you sure you want to close?')) {
                e.preventDefault();
                return false;
            }
        }
        var returnUrl = $('input[name="return_url"]').val();
        if (returnUrl) {
            window.location.href = decodeURIComponent(atob(returnUrl));
        } else {
            window.location.href = '<?php echo esc_url(admin_url('admin.php?page=camp-manager-members')); ?>';
        }
        e.preventDefault();
    });
});
</script>