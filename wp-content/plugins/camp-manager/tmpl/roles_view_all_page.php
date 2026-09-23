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
<div class="wrap">
    <h1 class="wp-heading-inline">Camp Roles</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-role'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
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
</div>
