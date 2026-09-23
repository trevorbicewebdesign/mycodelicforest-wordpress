<?php

$role_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$role = $role_id ? $this->roles->getRole($role_id) : null;

$is_edit = $role !== null;
$season = $is_edit ? (int) $role['season'] : CampManagerSeason::selected();
$holder_ids = $is_edit ? array_map('intval', array_column($role['members'], 'id')) : [];

// Holders come from the role's own season roster.
global $wpdb;
$season_roster = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}mf_roster WHERE season = %d ORDER BY lname, fname",
    $season
), ARRAY_A) ?: [];
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Camp Role' : 'Add New Camp Role'; ?></h1>
    <hr/>
    <?php if (!empty($_GET['error'])): ?>
        <div class="notice notice-error inline"><p><?php echo esc_html(wp_unslash($_GET['error'])); ?></p></div>
    <?php elseif (!empty($_GET['success'])): ?>
        <div class="notice notice-success inline"><p>Role saved.</p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="camp_manager_save_role">
        <?php wp_nonce_field('camp_manager_save_role'); ?>
        <?php if ($is_edit): ?>
            <input type="hidden" name="role_id" value="<?php echo esc_attr($role['id']); ?>">
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th>Season</th>
                <td><strong><?php echo (int) $season; ?></strong></td>
            </tr>
            <tr>
                <th><label for="role_name">Name</label></th>
                <td><input type="text" name="role_name" id="role_name" class="regular-text" value="<?php echo esc_attr($role['name'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <th><label for="role_description">Description</label></th>
                <td>
                    <textarea name="role_description" id="role_description" rows="5" class="large-text"><?php echo esc_textarea($role['description'] ?? ''); ?></textarea>
                    <p class="description">Shown on the public Camp Roles page. Basic HTML such as &lt;strong&gt; is allowed.</p>
                </td>
            </tr>
            <tr>
                <th><label for="role_sort_order">Order</label></th>
                <td><input type="number" name="role_sort_order" id="role_sort_order" class="small-text" value="<?php echo (int) ($role['sort_order'] ?? 0); ?>"></td>
            </tr>
            <tr>
                <th><label for="role_members">Held By</label></th>
                <td>
                    <select name="role_members[]" id="role_members" multiple style="min-width: 25em;">
                        <?php foreach ($season_roster as $member): ?>
                            <option value="<?php echo esc_attr($member['id']); ?>" <?php selected(in_array((int) $member['id'], $holder_ids, true)); ?>>
                                <?php echo esc_html(trim($member['fname'] . ' ' . $member['lname']) . (!empty($member['playaname']) ? " ({$member['playaname']})" : '') . ($member['status'] ? " – {$member['status']}" : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$season_roster): ?>
                        <p class="description">Nobody is on the <?php echo (int) $season; ?> roster yet.</p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Camp Manager Access</th>
                <td>
                    <fieldset>
                        <?php foreach (CampManagerRoles::AREAS as $area => $label): ?>
                            <label style="display: block; margin-bottom: 4px;">
                                <input type="checkbox" name="role_permissions[]" value="<?php echo esc_attr($area); ?>" <?php checked(in_array($area, $role['permissions'] ?? [], true)); ?>>
                                <?php echo esc_html($label); ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="description">
                        Holders who are linked to a WordPress user can use these parts of Camp Manager while this is the current season.
                        Admins always have full access.
                    </p>
                </td>
            </tr>
        </table>
        <div style="display: flex; gap: 10px;">
            <?php submit_button('Save Role', 'secondary', 'save_role', false); ?>
            <?php submit_button('Save & Close Role', 'primary', 'save_close_role', false); ?>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=camp-manager-roles')); ?>">Close</a>
        </div>
    </form>
</div>
<script type="text/javascript">
jQuery(function ($) {
    if ($.fn.select2) {
        $('#role_members').select2({ placeholder: 'Choose roster members', width: '25em' });
    }
});
</script>
