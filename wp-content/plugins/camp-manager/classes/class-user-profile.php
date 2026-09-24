<?php

/**
 * "Camp Roles" on the wp-admin user profile (Users -> Edit User): assign a WordPress user's
 * Camp Manager roles across seasons from one picker.
 *
 * Every option reads "YYYY Role" (2027 Treasurer), so typing the year narrows the list to
 * that season and typing on narrows it to the role. Roles are held through the roster, so a
 * role needs the user's roster entry for that season. Where nothing links them to a season
 * yet, picking a role auto-links the unlinked roster entry that matches them (by email, then
 * name, then playa name), writing only the WordPress user id on it; seasons with no entry to
 * link are listed disabled with a note. Assigning a role can grant Camp Manager access, so
 * editing stays admin-only, as on the role and member pages; everyone else sees the roles
 * read-only.
 */
class CampManagerUserProfile
{
    const FIELD = 'camp_manager_user_roles';
    const NOTICE = 'camp_manager_user_roles_notice';

    public function init()
    {
        add_action('show_user_profile', [$this, 'render']);
        add_action('edit_user_profile', [$this, 'render']);
        add_action('personal_options_update', [$this, 'save']);
        add_action('edit_user_profile_update', [$this, 'save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_notices', [$this, 'notices']);
    }

    public static function canAssign(): bool
    {
        return current_user_can('manage_options');
    }

    /** The same select2 the Camp Manager pages use, so the picker filters as you type. */
    public function enqueue($hook)
    {
        if (!in_array($hook, ['user-edit.php', 'profile.php'], true) || !self::canAssign()) {
            return;
        }
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
        wp_add_inline_script(
            'select2',
            "jQuery(function ($) { $('#" . self::FIELD . "').select2({ width: '25em', placeholder: 'Type a year, then a role' }); });"
        );
    }

    public function render($user)
    {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        $roles = new CampManagerRoles();
        $all = $roles->getAllRoles();
        $held = $roles->getUserRoleIds((int) $user->ID);
        $linked = $roles->rosterRowsForUser((int) $user->ID);
        $manage_url = admin_url('admin.php?page=camp-manager-roles');

        // For seasons nothing links them to yet: the roster entry a pick would auto-link, if any.
        $linkable = [];
        foreach (array_unique(array_column($all, 'season')) as $season) {
            if (!isset($linked[$season])) {
                $linkable[$season] = $roles->findUnlinkedRosterRow((int) $user->ID, $season);
            }
        }
        ?>
        <h3>Camp Roles</h3>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="<?php echo esc_attr(self::FIELD); ?>">Camp roles by season</label></th>
                <td>
                    <?php if (!$all): ?>
                        <p>No camp roles are set up yet.
                            <?php if (self::canAssign()): ?><a href="<?php echo esc_url($manage_url); ?>">Manage camp roles</a><?php endif; ?></p>
                    <?php elseif (self::canAssign()): ?>
                        <input type="hidden" name="<?php echo esc_attr(self::FIELD); ?>_submitted" value="1">
                        <select name="<?php echo esc_attr(self::FIELD); ?>[]" id="<?php echo esc_attr(self::FIELD); ?>" multiple style="min-width: 25em;">
                            <?php foreach ($all as $role): ?>
                                <?php
                                $season = $role['season'];
                                $is_held = in_array($role['id'], $held, true);
                                $note = '';
                                if (!$is_held && !isset($linked[$season])) {
                                    $note = !empty($linkable[$season])
                                        ? sprintf(' (links their %d roster entry, %s)', $season, trim($linkable[$season]['fname'] . ' ' . $linkable[$season]['lname']))
                                        : sprintf(' (not on the %d roster)', $season);
                                }
                                // A held role is never disabled: browsers leave disabled options out of the
                                // submission, which would silently drop it on save.
                                $can_pick = $is_held || isset($linked[$season]) || !empty($linkable[$season]);
                                ?>
                                <option value="<?php echo esc_attr($role['id']); ?>" <?php selected($is_held); disabled(!$can_pick); ?>>
                                    <?php echo esc_html($role['label'] . $note); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            Each option is the season followed by the role, so type a year to see that season's roles.
                            Roles are held through the roster. For a season this member isn't linked to yet, picking a
                            role links their existing roster entry for that year (matched by email, name or playa name)
                            and changes nothing else on it; a season with no entry to link can't be picked until they are
                            added to that year's roster. Holders can also be set from
                            <a href="<?php echo esc_url($manage_url); ?>">Camp Roles</a>.
                        </p>
                    <?php else: ?>
                        <?php
                        $labels = array_column(array_filter($all, function ($role) use ($held) {
                            return in_array($role['id'], $held, true);
                        }), 'label');
                        ?>
                        <?php echo $labels ? esc_html(implode(', ', $labels)) : '<em>None</em>'; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Saves the picker. Only when the form carried the field (so a form without it leaves the
     * roles alone) and only for admins, whatever the form claims. user-edit.php has already
     * checked its nonce before firing this action. What got auto-linked or skipped is shown
     * as a notice after the redirect.
     */
    public function save($user_id)
    {
        if (empty($_POST[self::FIELD . '_submitted']) || !self::canAssign() || !current_user_can('edit_user', $user_id)) {
            return;
        }
        $roles = new CampManagerRoles();
        $result = $roles->setUserRoles((int) $user_id, isset($_POST[self::FIELD]) ? (array) $_POST[self::FIELD] : []);

        $messages = [];
        foreach ($result['linked'] as $season => $row) {
            $messages[] = sprintf('Linked this user to the %d roster entry for %s.', $season, trim($row['fname'] . ' ' . $row['lname']));
        }
        if ($result['skipped']) {
            $labels = [];
            foreach ($roles->getAllRoles() as $role) {
                if (in_array($role['id'], $result['skipped'], true)) {
                    $labels[] = $role['label'];
                }
            }
            $messages[] = sprintf(
                'Could not assign %s: this user is not on the roster for that season. Add them there first.',
                implode(', ', $labels ?: ['some roles'])
            );
        }
        if ($messages) {
            set_transient(self::NOTICE . '_' . get_current_user_id(), $messages, MINUTE_IN_SECONDS);
        }
    }

    public function notices()
    {
        $key = self::NOTICE . '_' . get_current_user_id();
        $messages = get_transient($key);
        if (!$messages) {
            return;
        }
        delete_transient($key);
        echo '<div class="notice notice-info is-dismissible"><p><strong>Camp roles:</strong> '
            . implode('<br>', array_map('esc_html', (array) $messages)) . '</p></div>';
    }
}
