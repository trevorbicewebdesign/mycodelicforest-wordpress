<?php

class MycodelicForestHistoryTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var MycodelicForestHistory */
    private $history;

    protected function _before()
    {
        if (!get_role('mycodelic_forest_member')) {
            add_role('mycodelic_forest_member', 'Mycodelic Forest Member', ['read' => true]);
        }
        $this->history = MycodelicForestHistory::$instance ?: new MycodelicForestHistory();
        // The plugin's hooks don't survive WPTestCase's rollback, so register the filter under test.
        if (!has_filter('render_block', [$this->history, 'hideMembersOnlyBlocks'])) {
            add_filter('render_block', [$this->history, 'hideMembersOnlyBlocks'], 10, 2);
        }
    }

    private function renderFooterAboutUsLinks(): array
    {
        $footer = file_get_contents(WP_CONTENT_DIR . '/themes/mycodelic-forest-child/parts/footer.html');
        $html = do_blocks($footer);
        // The label sits either right inside the <a> or in a nested __label span, by WP version.
        preg_match_all('/wp-block-navigation-item__(?:content|label)"[^>]*>\s*([^<]+?)\s*</', $html, $m);
        return array_values(array_intersect($m[1], ['About Us', 'History', 'Roster']));
    }

    public function testMembersOnlyBlocksRenderForMembersAndEditorsOnly()
    {
        $block = '<!-- wp:paragraph {"className":"mf-members-only other"} --><p class="mf-members-only other">Secret</p><!-- /wp:paragraph -->'
            . '<!-- wp:paragraph {"className":"mf-members-only-not"} --><p>Public</p><!-- /wp:paragraph -->';

        wp_set_current_user(0);
        $this->assertStringNotContainsString('Secret', do_blocks($block));
        $this->assertStringContainsString('Public', do_blocks($block), 'only the exact class counts');

        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        $this->assertStringNotContainsString('Secret', do_blocks($block));

        wp_set_current_user(self::factory()->user->create(['role' => 'mycodelic_forest_member']));
        $this->assertStringContainsString('Secret', do_blocks($block));

        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));
        $this->assertStringContainsString('Secret', do_blocks($block));
    }

    public function testFooterShowsHistoryAndRosterToMembersOnly()
    {
        wp_set_current_user(0);
        $this->assertSame(['About Us'], $this->renderFooterAboutUsLinks());

        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        $this->assertSame(['About Us'], $this->renderFooterAboutUsLinks(), 'logging in is not enough');

        wp_set_current_user(self::factory()->user->create(['role' => 'mycodelic_forest_member']));
        $this->assertSame(['About Us', 'History', 'Roster'], $this->renderFooterAboutUsLinks());
    }
}
