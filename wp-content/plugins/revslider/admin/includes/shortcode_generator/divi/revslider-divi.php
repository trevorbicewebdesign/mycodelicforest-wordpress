<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Divi 4 Fallback
 */
add_action( 'divi_extensions_init', function() {
	if (defined('ET_CORE_VERSION') && version_compare(ET_CORE_VERSION, '5', ">=")) return;
	require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/divi4/includes/RevsliderDivi.php';
});

/**
 * Render a Divi 4 module on a page that has not been converted yet.
 *
 * Divi 4 registers each of its modules as an ordinary shortcode, so [revslider_divi ...] renders through
 * the module class. Under Divi 5 that class is not loaded - there is no Divi 4 element framework to
 * register it against - and a page still holding Divi 4 markup would print the shortcode as plain text
 * until someone opened it in the builder and saved it again. Saved content renders unchanged until it is
 * re-saved, whatever builder is active (CONTRACT R7).
 *
 * @param array $atts Divi 4 module attributes.
 *
 * @return string
 */
function revslider_divi_render_legacy_module( $atts ) {
	$atts      = is_array( $atts ) ? $atts : [];
	$shortcode = isset( $atts['shortcode'] ) ? (string) $atts['shortcode'] : '';

	//Divi keeps our shortcode inside one of its own attributes and encodes it on the way in - percent
	//escapes in the saved post, entities in the builder's data. Both of its own renderers decode first.
	$shortcode = trim(
		html_entity_decode(
			str_replace( [ '%91', '%93', '%22' ], [ '[', ']', '"' ], $shortcode ),
			ENT_QUOTES,
			'UTF-8'
		)
	);

	//A module placed before Divi 4 stored the shortcode has only its alias
	if ( '' === $shortcode && ! empty( $atts['alias'] ) ) {
		$shortcode = sprintf( '[sr7 alias="%s"][/sr7]', esc_attr( $atts['alias'] ) );
	}

	if ( '' === $shortcode ) {
		return '';
	}

	//the same wrapper the Divi 4 module draws, so the markup does not move when the page is converted
	$attributes = ' class="revslider"';
	$wrapper_id = isset( $atts['wrapperid'] ) ? trim( (string) $atts['wrapperid'] ) : '';

	if ( '' !== $wrapper_id ) {
		$attributes .= ' id="' . esc_attr( sanitize_html_class( $wrapper_id ) ) . '"';
	}

	return '<div' . $attributes . '>' . do_shortcode( $shortcode ) . '</div>';
}

//After every builder has had its turn: Divi 4's own module claims this shortcode when it loads, and only
//when it has not is there anything for us to answer.
add_action( 'wp_loaded', function() {
	if ( ! defined( 'ET_CORE_VERSION' ) || shortcode_exists( 'revslider_divi' ) ) {
		return;
	}

	add_shortcode( 'revslider_divi', 'revslider_divi_render_legacy_module' );
} );

/**
 * Proceed with Divi 5
 */
require RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules/Modules.php';

/**
 * Enqueue style and scripts of Module Extension Example for Visual Builder.
 *
 * @since ??
 * @return void
 */
function d5_revslider_module_enqueue_vb_scripts() {
	if ( et_builder_d5_enabled() && et_core_is_fb_enabled() ) {
		$plugin_dir_url = RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/divi/';

		\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
			[
				'name'   => 'd5-revslider-module-builder-bundle-script',
				'version' => '1.0.0',
				'script' => [
					'src' => "{$plugin_dir_url}scripts/bundle.js",
					'deps'               => [
						'divi-module-library',
						'divi-vendor-wp-hooks',
					],
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
				],
			]
		);

		\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
			[
				'name'   => 'd5-revslider-module-builder-vb-bundle-style',
				'version' => '1.0.0',
				'style' => [
					'src' => "{$plugin_dir_url}styles/vb-bundle.css",
					'deps'               => [],
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
				],
			]
		);
	}
}
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'd5_revslider_module_enqueue_vb_scripts' );


/**
 * Enqueue scripts for Divi Builder App Frame
 *
 * @since ??
 * @return void
 */
function d5_revslider_module_enqueue_vb_app_scripts() {
	if ( et_builder_d5_enabled() && et_core_is_fb_enabled() ) {
		RevSliderShortcodeWizard::add_scripts(false, true);
	}
}
add_action( 'divi_visual_builder_assets_before_enqueue_app_window_scripts', 'd5_revslider_module_enqueue_vb_app_scripts' );

/**
 * Register the authenticated Divi 5 live-preview endpoint.
 *
 * Divi's canvas renders extension modules in React, so shortcode-backed modules need a small REST bridge
 * to obtain the same markup that WordPress renders on the front end. The route accepts Slider Revolution
 * shortcodes only and is restricted to users who can edit builder content.
 *
 * @return void
 */
function d5_revslider_module_register_preview_route() {
	// Both Divi builders draw their canvas themselves and fetch the rendered module through here, and this
	// file is loaded on every request of every site. A route that cannot be reached is still a route that
	// answers, so it is only declared where Divi is.
	if ( ! defined( 'ET_CORE_VERSION' ) ) {
		return;
	}

	register_rest_route(
		'revslider/v1',
		'/divi-preview',
		[
			'methods'             => \WP_REST_Server::CREATABLE,
			'permission_callback' => static function() {
				return current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' );
			},
			'callback'            => 'd5_revslider_module_render_preview',
			'args'                => [
				'shortcode'  => [
					'type'    => 'string',
					'default' => '',
				],
				'alias'      => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'instanceId' => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		]
	);
}
add_action( 'rest_api_init', 'd5_revslider_module_register_preview_route' );

/**
 * Normalize and validate a Slider Revolution preview shortcode.
 *
 * @param string $shortcode Saved Divi shortcode attribute.
 * @param string $alias     Saved module alias used by converted/legacy modules without a shortcode attr.
 *
 * @return string|\WP_Error
 */
function d5_revslider_module_get_preview_shortcode( string $shortcode, string $alias ) {
	$shortcode = trim(
		html_entity_decode(
			str_replace( [ '&#91;', '&#93;' ], [ '[', ']' ], wp_unslash( $shortcode ) ),
			ENT_QUOTES,
			'UTF-8'
		)
	);

	if ( '' === $shortcode && '' !== $alias ) {
		$shortcode = sprintf( '[sr7 alias="%s"][/sr7]', esc_attr( $alias ) );
	}

	if ( '' === $shortcode ) {
		return new \WP_Error( 'revslider_divi_preview_empty', __( 'No slider is selected.', 'revslider' ), [ 'status' => 400 ] );
	}

	$pattern = get_shortcode_regex( [ 'sr7', 'rev_slider' ] );
	$matches = [];

	if ( 1 !== preg_match( '/^' . $pattern . '$/s', $shortcode, $matches ) ) {
		return new \WP_Error( 'revslider_divi_preview_invalid', __( 'Invalid Slider Revolution shortcode.', 'revslider' ), [ 'status' => 400 ] );
	}

	$shortcode_attrs = shortcode_parse_atts( $matches[3] ?? '' );

	if ( is_array( $shortcode_attrs ) && 'modal' === strtolower( (string) ( $shortcode_attrs['usage'] ?? '' ) ) ) {
		return new \WP_Error( 'revslider_divi_preview_modal', __( 'Modal modules use the builder card preview.', 'revslider' ), [ 'status' => 400 ] );
	}

	return $shortcode;
}

/**
 * Render Slider Revolution markup for one Divi 5 canvas module.
 *
 * @param \WP_REST_Request $request REST request.
 *
 * @return \WP_REST_Response|\WP_Error
 */
function d5_revslider_module_render_preview( \WP_REST_Request $request ) {
	$shortcode = d5_revslider_module_get_preview_shortcode(
		(string) $request->get_param( 'shortcode' ),
		(string) $request->get_param( 'alias' )
	);

	if ( is_wp_error( $shortcode ) ) {
		return $shortcode;
	}

	// Each REST request starts the SR7 serial at one. Add the stable Divi element identity so two copies of
	// the same slider on the canvas never receive the same DOM/runtime id.
	$instance_id = (string) $request->get_param( 'instanceId' );
	$id_suffix   = substr( md5( '' !== $instance_id ? $instance_id : wp_generate_uuid4() ), 0, 12 );
	$id_filter   = static function( $html_id, $output = null ) use ( $id_suffix ) {
		return $html_id . '_divi_' . $id_suffix;
	};

	global $SR_GLOBALS;
	$was_loaded_by_editor           = $SR_GLOBALS['loaded_by_editor'] ?? false;
	$SR_GLOBALS['loaded_by_editor'] = true;
	$rendered                       = '';
	$cache                          = RevSliderGlobals::instance()->get( 'RevSliderCache' );
	$was_cache_enabled              = is_object( $cache ) && isset( $cache->cache_enabled ) ? $cache->cache_enabled : null;

	// A cached slider was rendered before the per-Divi id filter existed and would reintroduce duplicate
	// runtime ids. Builder previews are transient, so render them uncached and leave the site setting intact.
	if ( null !== $was_cache_enabled ) {
		$cache->cache_enabled = false;
	}

	add_filter( 'revslider_set_html_id', $id_filter, PHP_INT_MAX, 2 );

	try {
		$rendered = (string) do_shortcode( $shortcode );
		$fonts    = RevSliderGlobals::instance()->get( 'RevSliderFonts' );

		if ( is_object( $fonts ) && method_exists( $fonts, 'load_google_fonts' ) ) {
			$font_buffer_level = ob_get_level();
			ob_start();

			try {
				$fonts->load_google_fonts();
				$rendered .= (string) ob_get_clean();
			} finally {
				if ( ob_get_level() > $font_buffer_level ) {
					ob_end_clean();
				}
			}
		}
	} finally {
		remove_filter( 'revslider_set_html_id', $id_filter, PHP_INT_MAX );
		$SR_GLOBALS['loaded_by_editor'] = $was_loaded_by_editor;

		if ( null !== $was_cache_enabled ) {
			$cache->cache_enabled = $was_cache_enabled;
		}
	}

	return rest_ensure_response( [ 'html' => $rendered ] );
}


/**
 * Check if the request is for Divi Builder
 * @return bool are we inside the Divi 4 or Divi 5 builder?
 */
function revslider_divi_is_builder_request() {
	$is_divi_4_live_builder = !empty($_GET['et_fb']);
	$is_divi_5_builder      = function_exists('et_builder_d5_enabled')
		&& et_builder_d5_enabled()
		&& function_exists('et_core_is_fb_enabled')
		&& et_core_is_fb_enabled();

	return $is_divi_4_live_builder || $is_divi_5_builder;
}

/**
 * Enqueue style and scripts of Module Extension Example
 *
 * @since ??
 * @return void
 */
function d5_revslider_module_enqueue_frontend_scripts() {
	if (! revslider_divi_is_builder_request()) {
		return;
	}

	$plugin_dir_url = RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/divi/';
	wp_enqueue_style( 'd5-revslider-module-builder-bundle-style', "{$plugin_dir_url}styles/bundle.css", array(), '1.0.0' );
	wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/tools.js'), false);

	//Divi 5 asks through @divi/rest, which knows where to call and carries the nonce. Divi 4's builder has
	//no such helper, so the same two values are handed to it directly.
	wp_localize_script('revbuilder-backend', 'SR7DiviPreview', [
		'url'	=> esc_url_raw( rest_url( 'revslider/v1/divi-preview' ) ),
		'nonce'	=> wp_create_nonce( 'wp_rest' )
	]);
}
add_action( 'wp_enqueue_scripts', 'd5_revslider_module_enqueue_frontend_scripts' );
