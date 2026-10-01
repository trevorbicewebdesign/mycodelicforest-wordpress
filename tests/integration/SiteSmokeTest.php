<?php

/**
 * Proves the harness works and that the site's own stack loads: WordPress boots into the test
 * database (never the site's), the theme and plugins are active, and nothing they register is
 * missing. This is the test an update PR has to keep green. Run it first on a new site.
 */
class SiteSmokeTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /** Plugins the integration suite loads (tests/integration.suite.yml). */
    private const PLUGINS = [
        'mycodelic-forest/mycodelic-forest.php',
    ];

    public function testTheTestDatabaseIsInUseAndNotTheSites()
    {
        global $wpdb;

        $this->assertSame('test', $wpdb->get_var('SELECT DATABASE()'), 'tests must never run against the site database');
        $this->assertSame('test_', $wpdb->prefix);
    }

    public function testWordPressIsTheVersionTheRepoPins()
    {
        $pinned = null;
        foreach (json_decode((string) file_get_contents(ABSPATH . 'composer.lock'), true)['packages'] as $package) {
            if ($package['name'] === 'roots/wordpress-no-content') {
                $pinned = ltrim($package['version'], 'v');
            }
        }

        $this->assertNotNull($pinned, 'composer.lock does not lock roots/wordpress-no-content');
        $this->assertSame($pinned, get_bloginfo('version'), 'core differs from composer.lock: run bin/update-local.sh');
    }

    public function testTheThemeIsActive()
    {
        $this->assertSame('spectra-one', get_stylesheet());
        $this->assertTrue(wp_get_theme()->exists(), 'the active theme is missing from wp-content/themes');
        $this->assertEmpty(wp_get_theme()->errors() ?: [], 'the active theme reports errors');
    }

    public function testEveryPluginIsActive()
    {
        foreach (self::PLUGINS as $plugin) {
            $this->assertFileExists(WP_PLUGIN_DIR . '/' . $plugin);
            $this->assertTrue(is_plugin_active($plugin), "{$plugin} is not active");
        }
    }

    public function testAPostCanBeCreatedAndRendered()
    {
        $id = self::factory()->post->create(['post_title' => 'Smoke test post', 'post_content' => '<!-- wp:paragraph --><p>Hello</p><!-- /wp:paragraph -->']);

        $this->assertSame('Smoke test post', get_the_title($id));
        // Any attributes: WordPress 7 adds class="wp-block-paragraph", earlier versions add none.
        $this->assertMatchesRegularExpression('#<p\b[^>]*>Hello</p>#', apply_filters('the_content', get_post($id)->post_content));
    }
}
