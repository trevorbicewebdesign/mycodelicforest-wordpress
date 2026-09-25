<?php

$table = new CampManagerRolesTable($this->roles);
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .wp-list-table .column-sort_order  { width: 5%; }
    .wp-list-table .column-name        { width: 15%; }
    .wp-list-table .column-members     { width: 20%; }
    .wp-list-table .column-permissions { width: 20%; }
</style>
<?php
// The result of the last import (kept for a few minutes so it survives the redirect).
$import_key = 'camp_manager_roles_import_' . get_current_user_id();
$import = get_transient($import_key);
if ($import !== false) {
    delete_transient($import_key);
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Camp Roles</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-role'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <?php if (is_array($import) && !empty($import['error'])): ?>
        <div class="notice notice-error"><p><strong>Import failed:</strong> <?php echo esc_html($import['error']); ?></p></div>
    <?php elseif (is_array($import)): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php echo !empty($import['dry_run']) ? 'Preview, nothing was changed:' : 'Roles imported:'; ?></strong>
                <?php echo (int) $import['created']; ?> <?php echo !empty($import['dry_run']) ? 'would be created' : 'created'; ?>,
                <?php echo (int) $import['updated']; ?> <?php echo !empty($import['dry_run']) ? 'would be updated' : 'updated'; ?>,
                <?php echo (int) $import['unchanged']; ?> already the same.
            </p>
            <?php if (!empty($import['seasons'])): ?>
                <ul style="list-style: disc; margin-left: 20px;">
                    <?php foreach ($import['seasons'] as $year => $counts): ?>
                        <li><?php echo (int) $year; ?>: <?php echo (int) $counts['created']; ?> created, <?php echo (int) $counts['updated']; ?> updated, <?php echo (int) $counts['unchanged']; ?> unchanged</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php CampManagerSeason::renderSwitcher(); ?>
    <p class="description">
        Roles are set up each season and held by that season's roster members. Camp Manager access only
        applies to holders in the current season (<?php echo (int) CampManagerSeason::current(); ?>) who are
        linked to a WordPress user and not Dropped/No. Shown publicly with the <code>[camp_manager_roles]</code> shortcode.
    </p>
    <?php if (!$table->items): ?>
        <?php $previous = $this->roles->previousSeasonWithRoles(); ?>
        <?php if ($previous): ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 8px 0 16px;">
                <input type="hidden" name="action" value="camp_manager_copy_roles">
                <input type="hidden" name="from_season" value="<?php echo (int) $previous; ?>">
                <?php wp_nonce_field('camp_manager_copy_roles'); ?>
                <p>No roles for <?php echo (int) CampManagerSeason::selected(); ?> yet.
                    <button class="button">Copy the <?php echo (int) $previous; ?> roles</button>
                    <span class="description">Copies names, descriptions and access, not who held them.</span></p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
    <form method="post">
        <?php
        $table->display();
        ?>
    </form>

    <h2>Import / export</h2>
    <p class="description">
        Roles are data, not code, so they don't travel with a deploy. Export them to a file and import that file on another
        site (for example from a local copy to the live site). The file has each role's circle, description, access, order and
        history across seasons; who holds a role isn't included, since that belongs to each site's roster. Importing matches
        roles by season, circle and name, so it updates the roles that are already there and adds the rest, and never deletes.
    </p>
    <div style="display: flex; gap: 40px; flex-wrap: wrap;">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="camp_manager_export_roles">
            <?php wp_nonce_field('camp_manager_export_roles'); ?>
            <p>
                <label><input type="radio" name="export_scope" value="season" checked> The <?php echo (int) CampManagerSeason::selected(); ?> roles</label><br>
                <label><input type="radio" name="export_scope" value="all"> Every season</label>
            </p>
            <?php submit_button('Export roles', 'secondary', 'export_roles', false); ?>
        </form>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="camp_manager_import_roles">
            <?php wp_nonce_field('camp_manager_import_roles'); ?>
            <p>
                <input type="file" name="roles_file" accept=".json,application/json" required><br>
                <label><input type="checkbox" name="import_preview" value="1" checked> Preview only (show what would change)</label>
            </p>
            <?php submit_button('Import roles', 'secondary', 'import_roles', false); ?>
        </form>
    </div>
</div>
