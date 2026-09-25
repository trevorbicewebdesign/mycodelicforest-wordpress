<?php

/**
 * Collapsible boxes for Camp Manager admin pages (the roster and dashboard overviews).
 *
 * These are regular WordPress postboxes, so core's postbox script handles the collapse toggle
 * and remembers, per user and per screen, which boxes were left closed. Core draws the toggle
 * arrow only inside .meta-box-sortables, which would also make the boxes draggable, so the
 * arrow and the rest of the box styling live here.
 *
 *   CampManagerPostbox::boot('camp-manager-roster');
 *   CampManagerPostbox::open('roster-overview', 'Season overview', 'camp-manager-roster');
 *   ... contents ...
 *   CampManagerPostbox::close();
 */
class CampManagerPostbox
{
    private static $booted = false;

    /** Loads the postbox script for this screen and prints the shared styles (once per request). */
    public static function boot(string $screen): void
    {
        wp_enqueue_script('postbox');
        wp_add_inline_script('postbox', 'jQuery(function () { postboxes.add_postbox_toggles(' . wp_json_encode($screen) . '); });');

        if (self::$booted) {
            return;
        }
        self::$booted = true;
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        self::styles();
    }

    /**
     * Opens a box. $args: 'class' extra classes for the box; 'header' trusted HTML shown in the
     * header beside the toggle (a link, say); 'padded' pads the contents.
     */
    public static function open(string $id, string $title, string $screen, array $args = []): void
    {
        $args += ['class' => '', 'header' => '', 'padded' => false];
        $closed_boxes = get_user_option('closedpostboxes_' . $screen);
        $closed = is_array($closed_boxes) && in_array($id, $closed_boxes, true);
        $classes = trim('postbox cm-postbox ' . ($args['padded'] ? 'cm-postbox--padded ' : '') . $args['class'] . ($closed ? ' closed' : ''));
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($classes); ?>">
            <div class="postbox-header">
                <h2 class="hndle is-non-sortable"><?php echo esc_html($title); ?></h2>
                <?php if ($args['header'] !== ''): ?>
                    <div class="cm-postbox__header-extra"><?php echo $args['header']; ?></div>
                <?php endif; ?>
                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="<?php echo $closed ? 'false' : 'true'; ?>">
                        <span class="screen-reader-text"><?php echo esc_html('Toggle panel: ' . $title); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            <div class="inside">
        <?php
    }

    public static function close(): void
    {
        echo '</div></div>';
    }

    private static function styles(): void
    {
        ?>
<style>
    .cm-postbox { margin-top: 0; }
    .cm-postbox .postbox-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #c3c4c7; }
    .cm-postbox .hndle { flex: 1; margin: 0; padding: 12px 16px; font-size: 15px; line-height: 1.4; }
    .cm-postbox.closed .postbox-header { border-bottom: 0; }
    .cm-postbox .cm-postbox__header-extra { padding: 0 8px; }
    .cm-postbox .inside { margin: 0; padding: 0; }
    .cm-postbox--padded .inside { padding: 12px 16px 16px; }
    .cm-postbox .toggle-indicator::before { content: "\f142"; display: inline-block; font: normal 20px/1 dashicons; -webkit-font-smoothing: antialiased; }
    .cm-postbox.closed .toggle-indicator::before { content: "\f140"; }
    .cm-postbox .handlediv { width: 36px; height: 36px; }

    .cm-stats { display: flex; flex-wrap: wrap; }
    .cm-stat { flex: 1 1 160px; padding: 14px 16px 12px; border-left: 1px solid #dcdcde; }
    .cm-stat:first-child { border-left: 0; }
    .cm-stat__label { display: block; color: #50575e; margin-bottom: 4px; }
    .cm-stat__value { display: block; font-size: 24px; line-height: 1.2; font-weight: 600; color: #1d2327; }
    .cm-stat__note { display: block; margin-top: 4px; color: #50575e; }
    .cm-stat-footer { margin: 0; padding: 10px 16px 14px; color: #50575e; }
</style>
        <?php
    }
}
