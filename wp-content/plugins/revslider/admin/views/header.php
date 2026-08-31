<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

global $SR_GLOBALS;
$sr_af		= new RevSliderFunctionsAdmin();
$sr_addon	= RevSliderGlobals::instance()->get('RevSliderAddons');
$sr_slider	= new RevSliderSlider();
$sr_track	= new RevSliderTracking();
$sr_upd		= new RevSliderPluginUpdate();
$sr_updv6	= new RevSliderPluginUpdateV6();
$sr_ai		= RevSliderGlobals::instance()->get('RevSliderAI');
$sr_o		= RevSliderGlobals::instance()->get('RevSliderOptimizer');

$sr_valid				 = $sr_af->_truefalse($sr_af->get_options(['system', 'valid'], 'false'));
$sr_latest_version		 = $sr_af->get_options(['system', 'version'], RS_REVISION);
$sr_stable_version		 = $sr_af->get_options(['system', 'stable'], '4.2');
$sr_library_preloaded	 = $sr_af->get_options(['system', 'library_preloaded'], false);
$sr_emergency_update	 = ($sr_valid !== true && version_compare($sr_latest_version, $sr_stable_version, '<') === true) ? true : false;
$sr_latest_version		 = ($sr_valid !== true && version_compare($sr_latest_version, $sr_stable_version, '<') === true) ? $sr_stable_version : $sr_latest_version;
$sr_added_image_sizes	 = $sr_af->get_all_image_sizes();
$sr_image_meta_todo		 = $sr_af->get_options(['other', 'image-meta'], []);
$sr_color_picker_presets = RSColorpicker::get_color_presets();
$sr_backend_fonts		 = $sr_af->get_font_familys();
$wp_upload_dir			 = wp_upload_dir();
$sr_upload_url			 = $sr_af->get_val($wp_upload_dir, 'baseurl');
$sr_v6_exists			 = RevSliderPluginUpdateV6::do_v6_tables_exist();
$sr_v6_upgrade_needed	 = ($sr_v6_exists === true) ? $sr_updv6->slider_need_update_checks_v6() : [];
$sr_slider_update_needed = $sr_upd->slider_need_update_checks();
$sr_show_updated		 = $sr_af->get_options(['system', 'overlay'], '1.0.0');
$sr_js_modules			 = $this->get_val($SR_GLOBALS, 'modules', []);
$sr_js_modules[]		 = 'migration';
$sr_ai_new				 = $sr_ai->get_finished_background_jobs();
$sr_ai_pending			 = ! empty( $sr_ai->get_open_events() );
$sr_addon_min_ver		 = $sr_addon->check_addon_version();
$sr_wpml_languages		 = $sr_af->get_wpml_editor_languages();

if(version_compare(RS_REVISION, $sr_show_updated, '>')) $sr_af->update_option(['system', 'overlay'], RS_REVISION);

$sr_show_deregister		= $sr_af->_truefalse($sr_af->get_options(['system', 'deregister'], 'false'));
$sr_show_deregister_msg	= $sr_af->get_options(['system', 'deregister-msg']);

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<!-- GLOBAL VARIABLES -->
<script>	
	window.SrSp = window.SrSp || class SrSp extends HTMLElement {
		static get observedAttributes() {return ['h', 'w'];}

		constructor() {super();}

		connectedCallback() {this.updateDimensions();}

		attributeChangedCallback(name, oldValue, newValue) {this.updateDimensions();}

		updateDimensions() {
			const height = this.getAttribute('h');
			const width = this.getAttribute('w');

			if (width !== null) {
			this.style.display = 'inline-block';
			this.style.width = `${width}px`;
			this.style.height = height ? `${height}px` : 'auto';
			} else {
			this.style.display = 'block';
			this.style.height = height ? `${height}px` : 'auto';
			}
		}
	}
	if (!customElements.get('sr-sp')) {
		customElements.define('sr-sp', SrSp);
	}

	window.SR7			  ??= {};
	SR7.F				  ??= {};
	SR7.D				  ??= {};
	SR7.E				  ??= {gAddons:{}};
	SR7.E.php			  ??= {};
<?php foreach($sr_af->get_basic_metas() as $sr_meta => $sr_meta_val){ ?>
	SR7.E.php[<?php echo json_encode($sr_meta); ?>] = <?php echo json_encode($sr_meta_val); ?>;
<?php } ?>
	SR7.LIB				  ??= {};
	SR7.LIB.V6			  = <?php echo (empty($sr_v6_upgrade_needed)) ? '[]' : json_encode($sr_v6_upgrade_needed); ?>;
	SR7.E.nonce			  = '<?php echo wp_create_nonce('revslider_actions'); ?>';
	SR7.E.plugin_dir	  = 'revslider';
	SR7.E.allow_update	  = <?php echo ($sr_emergency_update === true) ? 'true' : 'false'; ?>;
	SR7.E.slug_path		  = '<?php echo str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG_PATH); ?>';
	SR7.E.slug			  = '<?php echo str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG); ?>';
	SR7.E.admin_url		  = '<?php echo admin_url('admin.php?page=revslider'); ?>';
	SR7.E.plugin_url	  = '<?php echo str_replace(["\n", "\r"], '', RS_PLUGIN_URL); ?>';
	SR7.E.preview_url	  = '<?php echo get_rest_url().'sliderrevolution/sliders/preview/'; ?>';
	SR7.E.wp_plugin_url   = '<?php echo str_replace(["\n", "\r"], '', WP_PLUGIN_URL) . "/"; ?>';
	SR7.E.wp_upload_url	  = '<?php echo str_replace(["\n", "\r"], '', $sr_upload_url) . "/"; ?>';
	SR7.E.revision		  = '<?php echo RS_REVISION; ?>';
	// Cache-bust token for dynamically loaded editor scripts (separate from .revision, which is used for version gating).
	// In WP_DEBUG (dev) it changes every load so JS edits are never served stale; in production it stays on RS_REVISION.
	SR7.E.assetver		  = '<?php echo (defined('WP_DEBUG') && WP_DEBUG) ? RS_REVISION . '.' . time() : RS_REVISION; ?>';
	// Uncompiled dev build? The "_DIST" build-staging folder exists in the dev tree but is
	// NOT part of a shipped/gulp-compiled release. Gates in-progress dev-only editor
	// features (e.g. the new preset browser) so they are automatically OFF in production.
	SR7.E.dev			  = <?php echo file_exists(RS_PLUGIN_PATH . '_DIST') ? 'true' : 'false'; ?>;
	SR7.E.rtl			  = <?php echo (method_exists($this, 'use_rtl_assets') ? $this->use_rtl_assets() : is_rtl()) ? 'true' : 'false'; ?>;
	<?php if(is_rtl() && method_exists($this, 'use_rtl_assets') && !$this->use_rtl_assets()){ ?>
	//"Use Editor in LTR Mode" global setting: pin the SR7 UI to LTR inside the RTL admin
	document.body.classList.add('sr--force--ltr');
	<?php } ?>
	SR7.E.ajaxurl		  = '<?php echo admin_url('admin-ajax.php'); ?>';
	SR7.E.resturl		  = '<?php echo get_rest_url(); ?>';
	SR7.E.latest_revision = '<?php echo $sr_latest_version; ?>';
	SR7.E.registered	  = <?php echo ($sr_valid !== true) ? 'false' : 'true'; ?>;
	SR7.E.deregister	  = <?php echo ($sr_show_deregister === true) ? 'true' : 'false'; ?>;
	SR7.E.deregister_msg  = '<?php echo ($sr_show_deregister === true) ? $sr_show_deregister_msg : ''; ?>';
	SR7.E.updated		  = <?php echo (version_compare(RS_REVISION, $sr_show_updated, '>')) ? 'true' : 'false'; ?>;
	SR7.E.woocommerce	  = <?php echo (class_exists('WooCommerce') || class_exists('Woocommerce')) ? 'true' : 'false'; ?>;
	SR7.E.backend		  = true;
	SR7.E.libs 			  = ['tpgsap'];
	SR7.E.css 			  = ['csslp','cssbtns','cssfilters','cssnav','cssmedia'];
	SR7.E.modules 		  = ['<?php echo implode("','", $sr_js_modules); ?>'];
	SR7.E.resources		  = {};
	<?php if(!empty($sr_wpml_languages)){ ?>SR7.E.wpml			  = {languages: <?php echo wp_json_encode($sr_wpml_languages); ?>};<?php } ?>
	SR7.E.ai			  = {new: <?php echo (empty($sr_ai_new)) ? '[]' : json_encode($sr_ai_new); ?>, pending: <?php echo ($sr_ai_pending === true) ? 'true' : 'false'; ?>};
	SR7.E.addonsMinVer	  = <?php echo (empty($sr_addon_min_ver)) ? '{}' : json_encode($sr_addon_min_ver); ?>;
	SR7.E.library_preload = <?php echo ($sr_library_preloaded) ? 'false' : 'true'; ?>;
	SR7.E.optimizer		  = <?php echo $sr_o->is_enabled() && $sr_o->verify_webp() ? 'true' : 'false'; ?>;
	<?php if($sr_slider_update_needed == true){ ?>
	if (window.SR7?.B?.silentUpdate) SR7.B.silentUpdate(); else document.addEventListener('tools_all_ready',SR7.B.silentUpdate);
	<?php } ?>
</script>

<?php
do_action('revslider_header_content', $sr_af);
?>

<?php
//add custom fonts that have backend set to true

foreach($sr_backend_fonts ?? [] as $sr_bf){
	if($sr_bf['type'] === 'custom' && isset($sr_bf['url']) && isset($sr_bf['backend']) && $sr_bf['backend'] === true){
		echo '<link href="'.esc_html($sr_bf['url']).'" rel="stylesheet" property="stylesheet" media="all" type="text/css" >'."\n";
	}
}
?>
