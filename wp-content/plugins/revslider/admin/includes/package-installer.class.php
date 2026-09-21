<?php
/**
 * Template Package installer — turns a manifest into pages, modules, media, menus and effects.
 *
 * Shaped deliberately like RevSliderPageEffects: core owns the framework and the phases, and anything that
 * knows something core does not registers a HANDLER for its own node type, rather than patching this class.
 *
 * THE RESOLUTION MAP is the whole trick. A manifest names nothing real: it says "the module `hero`", "the
 * page `about`", "the image `media/x.jpg`". Every phase writes what it created into $map and later phases
 * read it back, which is what lets the same package install onto a site that already has 40 sliders.
 *
 * Runs one phase per request (see step()): a package with five pages and forty images will not finish
 * inside one PHP timeout.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Installs a parsed package manifest, one phase at a time.
 */
class RevSliderPackageInstaller extends RevSliderFunctions {

	/**
	 * The page template a Slider Revolution page gets: the theme steps aside and the modules are the
	 * page. Exactly what create_slider_page() has always set, so a package builds the same page the
	 * "create blank page" tickbox next to a template does.
	 */
	/** The phases that make modules. What is left when a package is installed without its pages. */
	const MODULE_PHASES = ['modules', 'fixids'];

	const BLANK_TEMPLATE = '../public/views/revslider-page-template.php';


	/** transient holding the install state between phase requests */
	const STATE_PREFIX	= 'rs_pkg_install_';
	const STATE_TTL		= 3600;

	/** option holding one receipt per installed package uid */
	const RECEIPTS		= 'revslider_package_receipts';

	/** attachment meta: the sha1 of the packaged file, so a reinstall reuses it instead of duplicating */
	const ASSET_SHA1	= '_sr7_pkg_sha1';

	/** tokens the installer substitutes inside markup it cannot walk (raw nodes, block html) */
	const TOKEN_MEDIA	= '#%%SR7_MEDIA:([a-zA-Z0-9._/-]+)%%#';
	const TOKEN_MODULE	= '#%%SR7_MODULE:([a-zA-Z0-9_-]+)%%#';
	const TOKEN_PAGE	= '#%%SR7_PAGE:([a-zA-Z0-9_-]+)%%#';

	/** @var array<string,array> registered node handlers */
	private static $handlers = [];

	/**
	 * Register a handler for a manifest node type.
	 *
	 * Core registers the built-in types on first use. An addon registers under its own key — for a page
	 * effect that is 'provider:<slug>', which the providers phase looks up before falling back to the
	 * generic Page Effects path. Registering a handler for a type core already owns REPLACES it, which is
	 * how an addon takes over a node core only half understands.
	 *
	 * @param string $type
	 * @param array  $args validate|install|remap|uninstall callables, plus an optional 'label'
	 * @return bool
	 */
	public static function register_handler($type, $args){
		$type = sanitize_key(str_replace(':', '_', $type));
		if($type === '' || !is_array($args)) return false;

		self::$handlers[$type] = array_merge([
			'label'		=> $type,
			'validate'	=> null,	// callable($node, $state): true|WP_Error — dry-run, must not write
			'install'	=> null,	// callable($node, $state): array  — returns refs to merge into the map
			'uninstall'	=> null		// callable($receipt_entry): void — v1.1
		], $args);

		return true;
	}

	/** @return array|false */
	public static function get_handler($type){
		$type = sanitize_key(str_replace(':', '_', $type));

		return isset(self::$handlers[$type]) ? self::$handlers[$type] : false;
	}

	/** @return array<string,array> */
	public static function get_handlers(){ return self::$handlers; }


	/*******************
	 * DRY RUN         *
	 *******************/

	/**
	 * Report what installing this package would do, and everything that would go wrong, WITHOUT writing
	 * anything. Nobody should discover a slug collision or a silently changed front page by finding it in
	 * their live site afterwards.
	 *
	 * @param array $manifest a manifest that already came through RevSliderPackageManifest::parse()
	 * @return array
	 */
	public function dry_run($manifest, $modules_only = false){
		$report = [
			'ok'		=> true,
			'errors'	=> [],
			'warnings'	=> [],
			'creates'	=> [
				//Modules only: the pages, their pictures and their menus are never made, so they are not
				//counted or warned about either. Promising them and then not making them is worse than
				//either one on its own.
				'pages'		=> $modules_only ? 0 : count($this->get_val($manifest, 'pages', [])),
				'modules'	=> count($this->get_val($manifest, 'modules', [])),
				'media'		=> $modules_only ? 0 : count($this->get_val($manifest, 'assets', [])),
				'menus'		=> $modules_only ? 0 : count($this->get_val($manifest, 'menus', []))
			],
			//Add-On slugs this package needs that are not active yet. The INSTALLER does not fetch them —
			//the library JS already has a working path for that (SR7.B.tlibItems.installAddons →
			//SR7.B.addons.fix), and it runs before begin(). This list is what it feeds that path.
			'addons'	=> [],
			'references'=> $this->get_val($manifest, 'references', [])
		];

		$this->check_requirements($manifest, $report);
		$this->check_modules($manifest, $report);

		if(!$modules_only){
			$this->check_pages($manifest, $report);
			$this->check_menus($manifest, $report);
			$this->check_providers($manifest, $report);
			$this->check_options($manifest, $report);
		}

		$this->check_reinstall($manifest, $report);

		$report['ok'] = empty($report['errors']);

		return $report;
	}

	/** @return void */
	private function check_requirements($manifest, &$report){
		$need = $this->get_val($manifest, ['requires', 'revslider'], '');
		if($need !== '' && version_compare(RS_REVISION, $need, '<')){
			$report['errors'][] = sprintf(__('This package needs Slider Revolution %1$s — this site runs %2$s.', 'revslider'), $need, RS_REVISION);
		}

		if(!function_exists('is_plugin_active')) require_once(ABSPATH . 'wp-admin/includes/plugin.php');

		foreach($this->get_val($manifest, ['requires', 'addons'], []) as $slug){
			//A missing Add-On is a warning, not an error: the library installs it before it calls begin(),
			//through the same path the "Install Package & Addons" button has always used.
			if(is_plugin_active($slug . '/' . $slug . '.php')) continue;

			$report['addons'][]   = $slug;
			$report['warnings'][] = sprintf(__('Add-On "%s" will be installed and activated.', 'revslider'), $this->addon_label($slug));
		}

		foreach($this->get_val($manifest, ['requires', 'plugins'], []) as $p){
			$slug = $this->get_val($p, 'slug');
			$name = $this->get_val($p, 'name', $slug);
			if($this->plugin_present($slug)) continue;

			//Third-party plugins are never installed for the user — declared and detected only.
			if($this->get_val($p, 'required', false)){
				$report['errors'][] = sprintf(__('This package needs the "%s" plugin, which is not installed.', 'revslider'), $name);
			}else{
				$report['warnings'][] = sprintf(__('Some content needs the "%s" plugin, which is not installed.', 'revslider'), $name);
			}
		}
	}

	/** @return void */
	private function check_modules($manifest, &$report){
		foreach($this->get_val($manifest, 'modules', []) as $mod){
			//A module authored for the package but never bound to a catalogue row: legal in a manifest,
			//not installable. This is the check that catches a half-finished package before it ships.
			if($this->get_val($mod, 'uid', '') === ''){
				$report['errors'][] = sprintf(__('The package refers to a module ("%s") that has no template attached yet.', 'revslider'), $this->get_val($mod, 'ref'));
			}
		}
	}

	/** @return void */
	private function check_pages($manifest, &$report){
		foreach($this->get_val($manifest, 'pages', []) as $page){
			$slug = $this->get_val($page, 'slug');
			if($slug === '') continue;

			$existing = get_page_by_path($slug, OBJECT, $this->get_val($page, 'post_type', 'page'));
			if($existing){
				$report['warnings'][] = sprintf(__('A page with the address "%1$s" already exists — "%2$s" will be created as a separate page.', 'revslider'), $slug, $this->get_val($page, 'title'));
			}

			$tpl = $this->get_val($page, 'template');
			if($tpl !== '' && !$this->template_allowed($tpl)){
				$report['errors'][] = sprintf(__('Page "%1$s" asks for a page template that is not available here: %2$s', 'revslider'), $this->get_val($page, 'title'), $tpl);
			}
		}
	}

	/** @return void */
	private function check_menus($manifest, &$report){
		$locations = get_registered_nav_menus();

		foreach($this->get_val($manifest, 'menus', []) as $menu){
			$loc = $this->get_val($menu, 'location');
			if($loc !== '' && !isset($locations[$loc])){
				$report['warnings'][] = sprintf(__('Your theme has no "%1$s" menu position — the "%2$s" menu will be created but not assigned.', 'revslider'), $loc, $this->get_val($menu, 'name'));
			}
		}
	}

	/** @return void */
	private function check_providers($manifest, &$report){
		$types = class_exists('RevSliderPageEffects') ? RevSliderPageEffects::get_types() : [];

		foreach($this->collect_providers($manifest) as $slug){
			if(isset($types[$slug]) || self::get_handler('provider_' . $slug) !== false) continue;

			//The addon that owns this effect is missing. The page still installs; the effect does not.
			$report['warnings'][] = sprintf(__('The "%s" effect is not available here — that part of the design will be skipped.', 'revslider'), $slug);
		}
	}

	/** @return void */
	private function check_options($manifest, &$report){
		$options = $this->get_val($manifest, 'options', []);
		if(empty($options['front_page'])) return;

		$current = get_option('show_on_front');
		if($current !== 'page' || intval(get_option('page_on_front')) > 0){
			$report['warnings'][] = __('This package sets your site\'s front page. Your current front page setting will be replaced.', 'revslider');
		}
	}

	/** @return void */
	private function check_reinstall($manifest, &$report){
		$receipt = $this->get_receipt($this->get_val($manifest, 'uid'));
		if(empty($receipt)) return;

		$report['warnings'][] = sprintf(
			__('This package is already installed (version %s). Installing again creates a second copy of its pages and modules.', 'revslider'),
			$this->get_val($receipt, 'version', '?')
		);
	}


	/*******************
	 * PHASED INSTALL  *
	 *******************/

	/**
	 * Open an install. Extracts the package zip, stores the state, and hands back the phase list the
	 * client then drives one step() at a time.
	 *
	 * @param array  $manifest parsed manifest
	 * @param string $zip_path the downloaded package zip
	 * @param bool   $modules_only install the modules and their folder, nothing else
	 * @return array|WP_Error ['install_id' => string, 'phases' => array]
	 */
	public function begin($manifest, $zip_path, $modules_only = false){
		if(!current_user_can('edit_theme_options') || !current_user_can('publish_pages')){
			return new WP_Error('forbidden', __('You do not have permission to install a template package.', 'revslider'));
		}

		$dry = $this->dry_run($manifest, $modules_only);
		if(!$dry['ok']) return new WP_Error('package_invalid', implode(' ', $dry['errors']));

		$dir = $this->extract($zip_path);
		if(is_wp_error($dir)) return $dir;

		$install_id = 'i' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 16);

		$state = [
			'install_id'	=> $install_id,
			'manifest'		=> $manifest,
			'dir'			=> $dir,
			'phases'		=> $this->install_phases($manifest, $modules_only),
			'done'			=> [],
			'map'			=> ['media' => [], 'modules' => [], 'pages' => [], 'effects' => [], 'slides' => []],
			//effects found while serialising each page, keyed by page ref, installed once the posts exist
			'pending'		=> [],
			//the accumulated old→new id map from every module import, for the fixids phase
			'import_map'	=> [],
			'receipt'		=> [
				'uid'		=> $this->get_val($manifest, 'uid'),
				'title'		=> $this->get_val($manifest, 'title'),
				'version'	=> $this->get_val($manifest, 'version'),
				'date'		=> current_time('mysql'),
				'posts'		=> [],
				'modules'	=> [],
				'media'		=> [],		// created by this install — candidates for removal
				'media_used'=> [],		// …plus the ones it deduped onto, which it must NOT remove
				'menus'		=> [],
				//Credits travel with the receipt, not just the install dialog. Some image licences want
				//attribution for as long as the picture is on the site, and by then the package zip is
				//long gone — this is the only place left that still knows.
				'references'=> $this->get_val($manifest, 'references', [])
			],
			'warnings'		=> $dry['warnings']
		];

		$this->save_state($state);

		return ['install_id' => $install_id, 'phases' => $state['phases'], 'warnings' => $dry['warnings']];
	}

	/**
	 * Run the next outstanding phase.
	 *
	 * @param string $install_id
	 * @return array|WP_Error ['phase' => string, 'done' => bool, 'progress' => float, 'warnings' => array]
	 */
	public function step($install_id){
		$state = $this->load_state($install_id);
		if(is_wp_error($state)) return $state;

		$phase = '';
		foreach($state['phases'] as $p){
			if(!in_array($p, $state['done'], true)){ $phase = $p; break; }
		}

		if($phase === ''){
			$this->finish($state);

			return [
				'phase'		=> '',
				'done'		=> true,
				'progress'	=> 1.0,
				'warnings'	=> $state['warnings'],
				'receipt'	=> $state['receipt'],
				//Computed now rather than stored: a receipt outlives the permalinks in it.
				'pages'		=> $this->page_links($state['receipt']),
				//What the overview needs to show the folder and its modules without being reloaded.
				'library'	=> $this->library_items($state['receipt'])
			];
		}

		$method = 'phase_' . $phase;
		if(method_exists($this, $method)) $this->{$method}($state);

		$state['done'][] = $phase;
		$this->save_state($state);

		return [
			'phase'		=> $phase,
			'done'		=> false,
			'progress'	=> count($state['done']) / max(1, count($state['phases'])),
			'warnings'	=> $state['warnings']
		];
	}

	/**
	 * Which phases this install runs.
	 *
	 * Modules only keeps the two that make a module: `modules` imports them and files them into the
	 * folder, `fixids` rewrites the slide ids their own actions point at. Everything else builds the site
	 * around them and is left out.
	 *
	 * @return array
	 */
	private function install_phases($manifest, $modules_only){
		$phases = $this->get_val($manifest, 'order', RevSliderPackageManifest::PHASES);
		if(!$modules_only) return $phases;

		return array_values(array_intersect($phases, self::MODULE_PHASES));
	}

	/**
	 * Write the receipt, drop the state and clean up the extracted zip. The receipt is what makes a
	 * reinstall detectable and an uninstall possible; without it the package's 58 new rows are
	 * indistinguishable from anything else the user made.
	 * @return void
	 */
	private function finish(&$state){
		$receipts = get_option(self::RECEIPTS, []);
		if(!is_array($receipts)) $receipts = [];

		$uid = $this->get_val($state['receipt'], 'uid');
		if($uid !== ''){
			//Keep every install, not just the last: two installs of one package made two sets of pages, and
			//an uninstall has to be able to remove either one.
			if(!isset($receipts[$uid]) || !is_array($receipts[$uid])) $receipts[$uid] = [];
			$receipts[$uid][] = $state['receipt'];
			update_option(self::RECEIPTS, $receipts, false);
		}

		$this->cleanup($state['dir']);
		delete_transient(self::STATE_PREFIX . $state['install_id']);
	}


	/*******************
	 * PHASES          *
	 *******************/

	/**
	 * Sideload the package's bundled media. Deduped by sha1 so reinstalling does not leave the media
	 * library with two of every image.
	 * @return void
	 */
	private function phase_media(&$state){
		foreach($this->get_val($state['manifest'], 'assets', []) as $path => $meta){
			$existing = $this->find_asset_by_sha1($this->get_val($meta, 'sha1'));
			if($existing > 0){
				$state['map']['media'][$path]	= $existing;
				//RELIED ON but not created by this install. Recording it is what stops a later uninstall of
				//whichever install DID create it from deleting an image this one is still showing.
				$state['receipt']['media_used'][] = $existing;
				continue;
			}

			$id = $this->sideload($state['dir'] . RevSliderPackageManifest::ASSET_DIR . $path, $meta);
			if(is_wp_error($id)){
				$state['warnings'][] = sprintf(__('Could not add the image "%s" to your media library.', 'revslider'), $path);
				continue;
			}

			$state['map']['media'][$path]		= $id;
			$state['receipt']['media'][]		= $id;	// created here → this install may remove it
			$state['receipt']['media_used'][]	= $id;	// …and uses it
		}
	}

	/**
	 * Install each module through the ordinary template path, so a packaged module goes through exactly
	 * the same download, licence check and import as one installed by hand.
	 * @return void
	 */
	private function phase_modules(&$state){
		$modules = $this->get_val($state['manifest'], 'modules', []);
		if(empty($modules)) return; //a package can be pages only — don't pull the import stack in for nothing

		$this->need_admin_classes();
		$templates = new RevSliderTemplate();

		foreach($modules as $mod){
			$uid = $this->get_val($mod, 'uid');
			$ref = $this->get_val($mod, 'ref');
			if($uid === '') continue;

			$filepath = $templates->_download_template($uid);
			if($filepath === false || (is_array($filepath) && isset($filepath['error']))){
				$state['warnings'][] = sprintf(__('The module "%s" could not be downloaded.', 'revslider'), $this->get_val($mod, 'title', $ref));
				continue;
			}

			$import = new RevSliderSliderImport();
			$result = $import->import_slider(false, $filepath, $uid);
			$templates->_delete_template($uid);

			$slider_id = intval($this->get_val($result, 'sliderID', 0));
			if($this->get_val($result, 'success') !== true || $slider_id <= 0){
				$state['warnings'][] = sprintf(__('The module "%s" could not be installed.', 'revslider'), $this->get_val($mod, 'title', $ref));
				continue;
			}

			$slider = new RevSliderSlider();
			$slider->init_by_id($slider_id);

			//'from' rides along so a provider's remap() can turn what its data still says into what this
			//site actually calls the module.
			$state['map']['modules'][$ref] = [
				'id'	=> $slider_id,
				'alias'	=> $slider->get_alias(),
				'from'	=> $this->get_val($mod, 'from', [])
			];
			$state['receipt']['modules'][] = $slider_id;

			foreach($this->pair_slides($this->get_val($mod, ['from', 'slides'], []), $slider) as $old_slide => $new_slide){
				$state['map']['slides'][$old_slide] = $new_slide;
			}

			//Accumulate every import's own old→new map into ONE, exactly as the library JS does with
			//SR7.importMap (a deep merge, so zip_to_template/aliases/slides gather rather than overwrite).
			//The fixids phase then runs over the whole set — see phase_fixids().
			$state['import_map'] = $this->merge_map($state['import_map'], $this->get_val($result, 'map', []));
		}

		$this->file_into_folder($state);
	}

	/**
	 * Remove the folder an install created, but ONLY once nothing else is in it.
	 *
	 * A folder is shared ground: somebody may well have dragged their own modules into it since. Those
	 * are not ours to lose, so a folder that still has something in it keeps standing with the rest.
	 *
	 * @return void
	 */
	private function drop_folders($receipt){
		$folders = $this->get_val($receipt, 'folders', []);
		if(empty($folders)) return;

		$this->need_admin_classes();
		if(!class_exists('RevSliderFolder')) return;

		$ours = array_map('intval', (array)$this->get_val($receipt, 'modules', []));

		foreach($folders as $folder_id){
			$folder = new RevSliderFolder();
			$folder->init_folder_by_id($folder_id);
			if(intval($folder->get_id()) <= 0) continue;

			$left = array_diff(array_map('intval', (array)$folder->get_children()), $ours);
			if(!empty($left)){
				//true = replace the list. set_children() only changes the object, and update_settings()
				//merges recursively, which cannot shrink a list - it would leave the removed ids in place.
				$folder->add_slider_to_folder(array_values($left), $folder_id, true);
				continue;
			}

			$folder->delete_slider();
		}
	}

	/**
	 * The folder and the modules as the library draws them.
	 *
	 * Installing ONE template hands its overview data straight back, and the library puts the item on
	 * screen from that - no reload anywhere. A package installs on the server across several requests,
	 * so the same data has to travel with the last one, or the overview keeps showing what it read when
	 * the page was opened.
	 *
	 * @return array ['folder' => data|null, 'modules' => [data, …]]
	 */
	private function library_items($receipt){
		$this->need_admin_classes();

		$out = ['folder' => null, 'modules' => []];

		foreach($this->get_val($receipt, 'modules', []) as $id){
			$slider = new RevSliderSlider();
			$slider->init_by_id($id, false);
			if(intval($slider->get_id()) > 0) $out['modules'][] = $slider->get_overview_data();
		}

		$folders = $this->get_val($receipt, 'folders', []);
		if(!empty($folders) && class_exists('RevSliderFolder')){
			$folder = new RevSliderFolder();
			$folder->init_folder_by_id(reset($folders));
			if(intval($folder->get_id()) > 0){
				$data = $folder->get_overview_data();
				//the overview keys everything off parent; a package folder sits at the top level
				$data['parent'] = -1;
				$out['folder'] = $data;
			}
		}

		return $out;
	}

	/**
	 * Put everything the package installed into one folder, named after the package.
	 *
	 * Installing a package from the library has always done this: the nine modules of a website do not
	 * belong loose in the overview between everything else. It happened in the library's javascript,
	 * which is why the server driven install did not inherit it.
	 *
	 * @return void
	 */
	private function file_into_folder(&$state){
		$children = $this->get_val($state['receipt'], 'modules', []);
		if(empty($children)) return;

		$this->need_admin_classes();
		if(!class_exists('RevSliderFolder')) return;

		$folder	= new RevSliderFolder();
		$title	= $this->get_val($state['manifest'], 'title', __('Template Package', 'revslider'));
		//create_folder() hands back the folder OBJECT, or false when the row could not be written.
		$made	= $folder->create_folder($title);
		$id		= is_object($made) ? intval($made->get_id()) : 0;

		if($id <= 0){
			$state['warnings'][] = __('The modules could not be put into a folder.', 'revslider');
			return;
		}

		//false: add to what is there rather than replace it, which for a folder just made is nothing
		$folder->add_slider_to_folder($children, $id, false);

		//in the receipt, so uninstalling the package takes its folder with it
		$state['receipt']['folders'][] = $id;
	}

	/**
	 * Re-point every id that only became wrong once ALL the modules were in.
	 *
	 * A module is imported knowing only its own old→new mapping, so a button in module A that opens module B as
	 * a modal still points at B's id on the site the package was built from. Core solves this with a second pass
	 * over the ACCUMULATED map after every template has landed (in the library: SR7.B.tlibItems.fixIds() →
	 * 'slider.save.modal_ids'). This is that pass for the server-side install, deliberately the same operation
	 * as RevSliderApi::save_slider_modal_ids() against the same map shape.
	 *
	 * NOT replicated: the 'slider.save.js_css_ids' half. With the map shape the library actually sends, that
	 * handler's inner loop never runs - it reads the map as a LIST of {slider:…} entries, an older shape.
	 * Per-slider inline JS/CSS ids are already rewritten inside import_slider().
	 * @return void
	 */
	private function phase_fixids(&$state){
		$map = $state['import_map'];
		if(empty($map)) return;

		$ztt		= $this->get_val($map, ['slider', 'zip_to_template'], []);
		$aliases	= $this->get_val($map, ['slider', 'aliases'], []);
		$slides_ids	= $this->get_val($map, 'slides', []);
		$sliders	= $this->get_val($map, 'slider', []);

		if(empty($ztt)) return;

		// STEP ONE, the same call save_slider_modal_ids() makes: a button in one module that opens
		// another as a modal still names the id it had on the site the package came from.
		foreach($ztt as $new){
			$rsi = new RevSliderSliderImport();
			$rsi->init_by_id($new);
			$rsi->update_modal_ids($ztt, $slides_ids, [], $aliases);
			$this->clear_module_cache($new);
		}

		// STEP TWO, which this phase was missing entirely: the ids written into a module's own CSS and
		// javascript. Installing a template runs both - modal ids, then inline scripts - so a package
		// that ran only the first left every custom script and style rule pointing at the old module.
		foreach($ztt as $new){
			$rsi = new RevSliderSliderImport();
			$rsi->init_by_id($new);

			foreach($sliders as $old => $made){
				if($old === 'zip_to_template' || $old === 'aliases' || !is_scalar($made)) continue;

				$rsi->update_css_and_javascript_ids($old, $made, $slides_ids);
			}

			$this->clear_module_cache($new);
		}
	}

	/**
	 * Drop a module's cached object and markup after its ids were rewritten. Both api.class.php calls do
	 * this; without it the module keeps serving the markup built from the ids it had a moment ago.
	 * @return void
	 */
	private function clear_module_cache($slider_id){
		$api = class_exists('RevSliderApi') ? new RevSliderApi() : null;
		if($api === null || !method_exists($api, 'clear_cache')) return;

		$api->clear_cache($slider_id);
	}

	/**
	 * AUTHORING slide id → the one this install just created, paired by position.
	 *
	 * A layer's element id is "<moduleHtmlId>-<slideId>-<layerIndex>", so an effect bound to a layer carries a
	 * slide id from the site the package was BUILT on. The import's own map is no help: it runs from the ZIP's
	 * internal ids, which the authoring site never saw either. Both modules come from the same template, so
	 * their slides line up in order, and position is the only thing that connects the two.
	 *
	 * Stops at the first position the installed module cannot fill: a short pairing leaves one anchor
	 * unresolved, a WRONG pairing looks like the effect works and quietly draws in the wrong place.
	 *
	 * @param array  $authored slide ids as recorded by the exporter, in order
	 * @param object $slider   the module this install created
	 * @return array [authoringSlideId => newSlideId]
	 */
	private function pair_slides($authored, $slider){
		$out = [];
		if(empty($authored) || !is_array($authored)) return $out;

		$made = [];
		foreach((array)$slider->get_slides() as $sl){
			$sl_id = intval($sl->get_id());
			if($sl_id > 0) $made[] = $sl_id;
		}

		foreach(array_values($authored) as $i => $old_slide){
			if(!isset($made[$i])) break;
			$old_slide = intval($old_slide);
			if($old_slide > 0) $out[$old_slide] = $made[$i];
		}

		return $out;
	}

	/**
	 * Deep-merge two import maps, matching _tpt.extend() in tptools.js: nested arrays gather instead of
	 * replacing, so zip_to_template ends up holding every module's mapping rather than the last one's.
	 * @return array
	 */
	private function merge_map($into, $from){
		if(!is_array($from)) return $into;
		if(!is_array($into)) $into = [];

		foreach($from as $k => $v){
			if(is_array($v) && isset($into[$k]) && is_array($into[$k])) $into[$k] = $this->merge_map($into[$k], $v);
			else $into[$k] = $v;
		}

		return $into;
	}

	/**
	 * Create each page, with its block tree serialised and every ref resolved.
	 * @return void
	 */
	private function phase_pages(&$state){
		$pages = $this->get_val($state['manifest'], 'pages', []);

		//PASS 1 — every page gets its id before any content is built. A home page that links to the about
		//page (%%SR7_PAGE:about%%) is the ordinary case, and serialising in list order would resolve that
		//token against a page that does not exist yet and quietly produce an empty href.
		foreach($pages as $page){
			$post_id = wp_insert_post([
				'post_title'	=> $this->get_val($page, 'title'),
				'post_name'		=> $this->get_val($page, 'slug'),
				'post_content'	=> '',
				'post_type'		=> $this->get_val($page, 'post_type', 'page'),
				'post_status'	=> $this->get_val($page, 'status', 'draft')
			], true);

			if(is_wp_error($post_id)){
				$state['warnings'][] = sprintf(__('The page "%s" could not be created.', 'revslider'), $this->get_val($page, 'title'));
				continue;
			}

			$state['map']['pages'][$this->get_val($page, 'ref')] = $post_id;
			$state['receipt']['posts'][] = $post_id;
		}

		//PASS 2 — content, now that every page ref resolves.
		foreach($pages as $page){
			$ref		= $this->get_val($page, 'ref');
			$post_id	= intval($this->get_val($state['map']['pages'], $ref, 0));
			if($post_id <= 0) continue;

			//Providers are collected while serialising (they need the block they hang on) and installed in
			//the providers phase, which is the only point where every id they could reference exists.
			$pending = [];
			$content = $this->serialize_nodes($this->get_val($page, 'blocks', []), $state, $pending);

			wp_update_post(['ID' => $post_id, 'post_content' => $content]);
			$state['pending'][$ref] = $pending;

			// A package that names no template gets ours. The page exists to carry the modules, and the
			// tickbox beside a template in the library has always produced exactly this - a package should
			// not arrive looking different from the same modules installed one at a time.
			$tpl = $this->get_val($page, 'template');
			if($tpl === '' || !$this->template_allowed($tpl)) $tpl = self::BLANK_TEMPLATE;

			update_post_meta($post_id, '_wp_page_template', $tpl);

			// The block editor's Slider Revolution panel reads this, not the template. Setting only the
			// template leaves the toggle saying "off" on a page that plainly uses it.
			if($tpl === self::BLANK_TEMPLATE) update_post_meta($post_id, 'rs_blank_template', 'on');

			foreach($this->get_val($page, 'meta', []) as $k => $v){
				if($k === '_wp_page_template' || $k === '_thumbnail_id') continue; //handled explicitly
				update_post_meta($post_id, $k, $v);
			}

			$thumb = $this->get_val($page, 'thumbnail');
			if($thumb !== '' && isset($state['map']['media'][$thumb])) set_post_thumbnail($post_id, $state['map']['media'][$thumb]);
		}
	}

	/**
	 * Install the effects collected while the pages were serialised: mint each an effectId, let its type
	 * rewrite its own references against the map, write the meta, then stamp the id back into the page's
	 * markup where serialize_nodes() left a placeholder.
	 *
	 * Runs last of the content phases because an effect needs its host post to exist AND needs the module
	 * map to be complete — a ChalkLine anchor can bind to a selector inside a module.
	 * @return void
	 */
	private function phase_providers(&$state){
		if(empty($state['pending'])) return;

		foreach($state['pending'] as $page_ref => $providers){
			$post_id = intval($this->get_val($state['map']['pages'], $page_ref, 0));
			if($post_id <= 0 || empty($providers)) continue;

			$post = get_post($post_id);
			if(!$post) continue;

			$content = $post->post_content;
			$changed = false;

			foreach($providers as $p){
				$slug	= $this->get_val($p, 'provider');
				$ref	= $this->get_val($p, 'ref');
				$handler= self::get_handler('provider_' . $slug);

				if($handler !== false && is_callable($handler['install'])){
					$result = call_user_func($handler['install'], $p, $state);
					$eid = $this->get_val($result, 'effect_id', '');
				}else{
					$eid = $this->install_page_effect($post_id, $slug, $p, $state);
				}

				//No addon and no registered type: leave the placeholder empty rather than half-wiring an
				//effect nothing can render. The dry-run already warned about this.
				$token = '%%SR7_EFFECT:' . $ref . '%%';
				if(strpos($content, $token) === false) continue;

				$content = str_replace($token, $eid, $content);
				$changed = true;

				if($eid !== '') $state['map']['effects'][$ref] = $eid;
			}

			if($changed) wp_update_post(['ID' => $post_id, 'post_content' => $content]);
		}
	}

	/** @return string the minted effectId, or '' when the type is not available here */
	private function install_page_effect($post_id, $slug, $node, &$state){
		if(!class_exists('RevSliderPageEffects')) return '';

		$types = RevSliderPageEffects::get_types();
		if(!isset($types[$slug])) return '';

		$result = RevSliderPageEffects::install_effect($post_id, $slug, $this->get_val($node, 'data', []), $state['map']);
		if(is_wp_error($result)){
			$state['warnings'][] = sprintf(__('An effect on this page could not be set up: %s', 'revslider'), $result->get_error_message());
			return '';
		}

		return $this->get_val($result, 'effect_id', '');
	}

	/**
	 * Create the package's menus and hang them in the theme's positions.
	 * @return void
	 */
	private function phase_menus(&$state){
		$locations = get_theme_mod('nav_menu_locations', []);
		if(!is_array($locations)) $locations = [];

		$registered = get_registered_nav_menus();
		$touched	= false;

		foreach($this->get_val($state['manifest'], 'menus', []) as $menu){
			$name = $this->get_val($menu, 'name');

			//A menu of this name may already exist — from a previous install, or the user's own. Make our
			//own rather than appending package items into a menu somebody else owns.
			$menu_name = $name;
			$i = 2;
			while(wp_get_nav_menu_object($menu_name)) $menu_name = $name . ' ' . $i++;

			$menu_id = wp_create_nav_menu($menu_name);
			if(is_wp_error($menu_id)){
				$state['warnings'][] = sprintf(__('The menu "%s" could not be created.', 'revslider'), $name);
				continue;
			}

			$state['receipt']['menus'][] = $menu_id;
			$this->add_menu_items($menu_id, $this->get_val($menu, 'items', []), 0, $state);

			$loc = $this->get_val($menu, 'location');
			if($loc !== '' && isset($registered[$loc])){
				$locations[$loc] = $menu_id;
				$touched = true;
			}
		}

		if($touched) set_theme_mod('nav_menu_locations', $locations);
	}

	/** @return void */
	private function add_menu_items($menu_id, $items, $parent_id, &$state){
		foreach($items as $item){
			$page_ref	= $this->get_val($item, 'page');
			$post_id	= intval($this->get_val($state['map']['pages'], $page_ref, 0));
			$url		= $this->get_val($item, 'url');

			if($page_ref !== '' && $post_id <= 0) continue; //its page failed to install; a dead link is worse than a gap

			$args = [
				'menu-item-title'	=> $this->get_val($item, 'title'),
				'menu-item-status'	=> 'publish',
				'menu-item-parent-id' => $parent_id
			];

			if($post_id > 0){
				$args['menu-item-type']		= 'post_type';
				$args['menu-item-object']	= get_post_type($post_id);
				$args['menu-item-object-id']= $post_id;
			}else{
				$args['menu-item-type']	= 'custom';
				$args['menu-item-url']	= $url;
			}

			$item_id = wp_update_nav_menu_item($menu_id, 0, $args);
			if(is_wp_error($item_id)) continue;

			$children = $this->get_val($item, 'children', []);
			if(!empty($children)) $this->add_menu_items($menu_id, $children, $item_id, $state);
		}
	}

	/** @return void */
	private function phase_options(&$state){
		$options = $this->get_val($state['manifest'], 'options', []);
		if(empty($options)) return;

		$front = $this->get_val($options, 'front_page');
		$posts = $this->get_val($options, 'posts_page');

		if($front !== ''){
			$id = intval($this->get_val($state['map']['pages'], $front, 0));
			if($id > 0){
				//Remember what we replaced, so an uninstall can hand the site its own front page back.
				$state['receipt']['prev_options'] = [
					'show_on_front'	=> get_option('show_on_front'),
					'page_on_front'	=> get_option('page_on_front')
				];
				update_option('page_on_front', $id);
				update_option('show_on_front', 'page');
			}
		}

		if($posts !== ''){
			$id = intval($this->get_val($state['map']['pages'], $posts, 0));
			if($id > 0) update_option('page_for_posts', $id);
		}
	}


	/*******************
	 * BLOCK TREE      *
	 *******************/

	/**
	 * Turn manifest nodes into Gutenberg markup, resolving every ref as it goes.
	 *
	 * Effects are NOT installed here — the post does not exist yet. Each one leaves a
	 * %%SR7_EFFECT:<ref>%% placeholder in the markup and is appended to $pending, which phase_providers()
	 * picks up once the page has an id.
	 *
	 * @param array $nodes
	 * @param array $state
	 * @param array $pending  collected provider nodes (by reference)
	 * @return string
	 */
	private function serialize_nodes($nodes, &$state, &$pending){
		$out = '';

		foreach($nodes as $node){
			$type = $this->get_val($node, 'type');

			switch($type){
				case 'raw':
					$out .= $this->substitute($this->get_val($node, 'html'), $state) . "\n";
					break;

				case 'module':
					$out .= $this->serialize_module($node, $state);
					break;

				case 'provider':
					$out .= $this->serialize_provider($node, $state, $pending);
					break;

				case 'block':
				case 'group':
					$out .= $this->serialize_block_node($node, $state, $pending);
					break;
			}
		}

		return $out;
	}

	/**
	 * A module placement, emitted exactly as create_slider_page() has always emitted one so a packaged
	 * page and a hand-made one are the same markup.
	 * @return string
	 */
	private function serialize_module($node, &$state){
		$ref	= $this->get_val($node, 'ref');
		$module	= $this->get_val($state['map']['modules'], $ref, false);

		//The module failed to install. Emitting the block anyway would leave a shortcode pointing at
		//nothing, which renders as raw text on the front end.
		if($module === false) return '';

		$alias	= $this->get_val($module, 'alias');
		$usage	= ($this->get_val($node, 'usage') === 'modal') ? ' usage="modal"' : '';

		// The card refuses to fetch anything without `alias` - it is the first thing its effect checks -
		// and it reads the cover from `image`. Neither can be left to the shortcode text: the block only
		// ever sees what stands in this comment. The rest is what the card shows until its own request
		// comes back, so the page is right on the first paint rather than a beat later.
		$sid	= intval($this->get_val($module, 'id'));
		$slider = new RevSliderSlider();
		$slider->init_by_id($sid, false);
		$ov = $slider->get_overview_data();

		$attrs = [
			'alias'		=> $alias,
			'moduleId'	=> (string)$sid,
			'slides'	=> count((array)$this->get_val($ov, 'children', [])),
		];

		$img = $this->preview_image($ov);
		if($img !== '') $attrs['image'] = $img;

		$type = (string)$this->get_val($ov, 'type', '');
		if($type !== '') $attrs['type'] = $type;

		foreach($this->get_val($node, 'attrs', []) as $k => $v) $attrs[$k] = $v;

		$title = (string)$this->get_val($ov, 'title', '');
		if($title !== '') $attrs['title'] = $title;

		//Down to the empty id and the empty style: this has to be character for character what save()
		//produces, or WordPress reads the block as an older one and drops everything above.
		$inner = '<div class="wp-block-themepunch-revslider revslider" id=""'
			. ' data-slidertitle="' . esc_attr($title) . '" style="">'
			. '[rev_slider alias="' . esc_attr($alias) . '"' . $usage . '][/rev_slider]'
			. '</div>';

		return $this->serialize_one([
			'blockName'		=> 'themepunch/revslider',
			'attrs'			=> $attrs,
			'innerBlocks'	=> [],
			'innerHTML'		=> $inner,
			'innerContent'	=> [$inner]
		]) . "\n";
	}

	/**
	 * A module's preview picture, whichever shape the overview data is in.
	 *
	 * It used to be an array and is a plain url now, so reading only bg.src - as create_slider_page()
	 * still does - returns nothing on current data. Both are read here, newest first.
	 *
	 * @return string
	 */
	private function preview_image($ov){
		$bg = isset($ov['bg']) ? $ov['bg'] : '';
		if(is_string($bg) && $bg !== '') return $bg;

		return (string)$this->get_val($ov, ['bg', 'src'], '');
	}

	/**
	 * A standalone effect block (a ChalkLine). Its effectId is a placeholder until the page exists.
	 * @return string
	 */
	private function serialize_provider($node, &$state, &$pending){
		$slug = $this->get_val($node, 'provider');
		$ref  = $this->get_val($node, 'ref');

		$types = class_exists('RevSliderPageEffects') ? RevSliderPageEffects::get_types() : [];
		$block = isset($types[$slug]) ? $this->get_val($types[$slug], 'block') : '';
		if($block === '') return ''; //the addon that owns it is not here; the dry-run said so

		$pending[] = $node;

		$attrs = $this->get_val($node, 'attrs', []);
		//Attribute-backed types keep their whole config here, so it has to be remapped too — the meta path
		//in install_effect() never sees it.
		if(!empty($attrs) && class_exists('RevSliderPageEffects')){
			$attrs = RevSliderPageEffects::remap_data($slug, $attrs, $state['map']);
		}
		$attrs['effectId'] = '%%SR7_EFFECT:' . $ref . '%%';

		return $this->serialize_one([
			'blockName'		=> $block,
			'attrs'			=> $attrs,
			'innerBlocks'	=> [],
			'innerHTML'		=> '',
			'innerContent'	=> []
		]) . "\n";
	}

	/**
	 * An ordinary block, plus any effects riding on it. A Scroll Animation on a core/group is stored as an
	 * sr7Anim attribute on that group — the attribute name comes from what the TYPE claims, never from
	 * the manifest, so a package cannot write an arbitrary attribute onto a block.
	 * @return string
	 */
	private function serialize_block_node($node, &$state, &$pending){
		$attrs = $this->get_val($node, 'attrs', []);

		foreach($this->get_val($node, 'providers', []) as $p){
			$slug = $this->get_val($p, 'provider');
			$attr = class_exists('RevSliderPageEffects') ? RevSliderPageEffects::claimed_attr($slug) : '';
			if($attr === '') continue;

			$pending[] = $p;

			$own = $this->get_val($p, 'attrs', []);
			if(class_exists('RevSliderPageEffects')) $own = RevSliderPageEffects::remap_data($slug, $own, $state['map']);

			$own['enabled']  = true;
			$own['effectId'] = '%%SR7_EFFECT:' . $this->get_val($p, 'ref') . '%%';
			$attrs[$attr]    = $own;
		}

		$inner_blocks	= [];
		$inner_markup	= '';
		$children		= $this->get_val($node, 'inner', []);

		if(!empty($children)) $inner_markup = $this->serialize_nodes($children, $state, $pending);

		$open	= $this->substitute($this->get_val($node, 'html_open'), $state);
		$close	= $this->substitute($this->get_val($node, 'html_close'), $state);
		$html	= $this->substitute($this->get_val($node, 'html'), $state);

		//A container splits its markup around its children; a leaf just carries its own.
		if($inner_markup !== '' || $open !== '' || $close !== ''){
			$content = $open . "\n" . $inner_markup . $close;
		}else{
			$content = $html;
		}

		return $this->serialize_one([
			'blockName'		=> $this->get_val($node, 'name'),
			'attrs'			=> $attrs,
			'innerBlocks'	=> $inner_blocks,
			'innerHTML'		=> $content,
			'innerContent'	=> [$content]
		]) . "\n";
	}

	/**
	 * Serialise one block. Uses WordPress' own serializer wherever it exists: block attributes have
	 * escaping rules of their own ("--" and "<" inside the JSON break the comment delimiter), and
	 * hand-rolling json_encode here gets them wrong.
	 * @return string
	 */
	private function serialize_one($block){
		if(function_exists('serialize_block')) return serialize_block($block);

		$attrs = empty($block['attrs']) ? '' : ' ' . wp_json_encode($block['attrs']);
		if($block['innerHTML'] === '') return '<!-- wp:' . $block['blockName'] . $attrs . ' /-->';

		return '<!-- wp:' . $block['blockName'] . $attrs . ' -->' . $block['innerHTML'] . '<!-- /wp:' . $block['blockName'] . ' -->';
	}

	/**
	 * Replace the tokens the exporter left inside markup the installer cannot walk into.
	 * @return string
	 */
	private function substitute($html, &$state){
		if($html === '' || strpos($html, '%%SR7_') === false) return $html;

		$map = $state['map'];

		$html = preg_replace_callback(self::TOKEN_MEDIA, function($m) use ($map){
			$id = isset($map['media'][$m[1]]) ? $map['media'][$m[1]] : 0;
			return $id ? wp_get_attachment_url($id) : '';
		}, $html);

		$html = preg_replace_callback(self::TOKEN_MODULE, function($m) use ($map){
			return isset($map['modules'][$m[1]]) ? $map['modules'][$m[1]]['alias'] : '';
		}, $html);

		$html = preg_replace_callback(self::TOKEN_PAGE, function($m) use ($map){
			$id = isset($map['pages'][$m[1]]) ? $map['pages'][$m[1]] : 0;
			return $id ? get_permalink($id) : '';
		}, $html);

		return $html;
	}


	/*******************
	 * HELPERS         *
	 *******************/

	/** @return int attachment id, 0 when this package file is not in the library yet */
	private function find_asset_by_sha1($sha1){
		if($sha1 === '') return 0;

		$found = get_posts([
			'post_type'		=> 'attachment',
			'post_status'	=> 'inherit',
			'numberposts'	=> 1,
			'fields'		=> 'ids',
			'meta_key'		=> self::ASSET_SHA1,
			'meta_value'	=> $sha1
		]);

		return empty($found) ? 0 : intval($found[0]);
	}

	/**
	 * Copy one packaged file into the media library. Goes through wp_check_filetype_and_ext against the
	 * site's allowed types, so a package cannot drop a .php into uploads by naming it .jpg.
	 * @return int|WP_Error
	 */
	private function sideload($file, $meta){
		if(!file_exists($file)) return new WP_Error('missing', __('File missing from package', 'revslider'));

		$name = basename($file);
		$type = wp_check_filetype_and_ext($file, $name);
		if(empty($type['type']) || empty($type['ext'])) return new WP_Error('filetype', __('File type not allowed', 'revslider'));

		$upload = wp_upload_bits($name, null, file_get_contents($file));
		if(!empty($upload['error'])) return new WP_Error('upload', $upload['error']);

		$id = wp_insert_attachment([
			'post_mime_type'=> $type['type'],
			'post_title'	=> sanitize_file_name(pathinfo($name, PATHINFO_FILENAME)),
			'post_content'	=> '',
			'post_status'	=> 'inherit'
		], $upload['file'], 0, true);

		if(is_wp_error($id)) return $id;

		require_once(ABSPATH . 'wp-admin/includes/image.php');
		wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));

		$alt = $this->get_val($meta, 'alt');
		if($alt !== '') update_post_meta($id, '_wp_attachment_image_alt', $alt);

		$sha1 = $this->get_val($meta, 'sha1');
		if($sha1 !== '') update_post_meta($id, self::ASSET_SHA1, $sha1);

		return $id;
	}

	/**
	 * A page template a package may ask for: one of the templates this plugin ships, or one the active
	 * theme actually offers. Anything else would be a path of the package's choosing.
	 * @return bool
	 */
	private function template_allowed($template){
		// OUR OWN first. The path starts with ../ and the traversal guard below would throw it out, which
		// made the exception under it dead code and the one template a package is most likely to want the
		// one it could never have.
		if($template === self::BLANK_TEMPLATE) return true;

		if(strpos($template, '..') !== false) return false;

		$theme = wp_get_theme();
		$page_templates = $theme->get_page_templates();

		return isset($page_templates[$template]);
	}

	/**
	 * The template and import classes are required behind is_admin() in the bootstrap, while this class is
	 * declared outside it (api.class.php reaches for it from either side). An install is always an admin
	 * request, so they are normally already there — this just refuses to depend on that.
	 * @return void
	 */
	private function need_admin_classes(){
		if(!class_exists('RevSliderTemplate'))		require_once(RS_PLUGIN_PATH . 'admin/includes/template.class.php');
		if(!class_exists('RevSliderSliderImport'))	require_once(RS_PLUGIN_PATH . 'admin/includes/import.class.php');
	}

	/** @return bool */
	private function plugin_present($slug){
		if(!function_exists('get_plugins')) require_once(ABSPATH . 'wp-admin/includes/plugin.php');

		foreach(array_keys(get_plugins()) as $file){
			if(dirname($file) === $slug) return true;
		}

		return false;
	}

	/** @return string */
	private function addon_label($slug){
		return ucwords(str_replace(['revslider-', '-addon', '-'], ['', '', ' '], $slug));
	}

	/** @return array every provider slug used anywhere in the manifest */
	private function collect_providers($manifest){
		$found = [];

		$walk = function($nodes) use (&$walk, &$found){
			foreach($nodes as $n){
				if($this->get_val($n, 'type') === 'provider') $found[$this->get_val($n, 'provider')] = true;
				foreach($this->get_val($n, 'providers', []) as $p) $found[$this->get_val($p, 'provider')] = true;
				if(!empty($n['inner'])) $walk($n['inner']);
			}
		};

		foreach($this->get_val($manifest, 'pages', []) as $page) $walk($this->get_val($page, 'blocks', []));

		return array_keys($found);
	}

	/*******************
	 * UNINSTALL       *
	 *******************/

	/**
	 * Every install this site has a receipt for, newest last, with the index uninstall() takes.
	 * @return array
	 */
	public function get_installs($uid = ''){
		$receipts = get_option(self::RECEIPTS, []);
		if(!is_array($receipts)) return [];

		$out = [];
		foreach($receipts as $key => $list){
			if($uid !== '' && $key !== $uid) continue;
			if(!is_array($list)) continue;

			foreach($list as $i => $receipt){
				if(!is_array($receipt)) continue;
				$out[] = array_merge($receipt, ['uid' => $key, 'index' => $i]);
			}
		}

		return $out;
	}

	/**
	 * What removing an install would take away. Same idea as the dry-run, pointing the other way: a
	 * package's pages are somebody's site by the time they remove it, and they should see the list first.
	 * @return array|WP_Error
	 */
	public function uninstall_report($uid, $index = null){
		$receipt = $this->find_receipt($uid, $index);
		if(is_wp_error($receipt)) return $receipt;

		$pages = [];
		foreach($this->get_val($receipt, 'posts', []) as $id){
			$post = get_post($id);
			if($post) $pages[] = ['id' => intval($id), 'title' => $post->post_title];
		}

		$modules = 0;
		foreach($this->get_val($receipt, 'modules', []) as $id){
			//$show_error = false: a module the user has since deleted by hand is not an error here, it is
			//simply one less thing to remove. With the default, init_by_id() THROWS on a missing slider and
			//the guard below never runs — reporting on a part-dismantled package would take the page down.
			$slider = new RevSliderSlider();
			$slider->init_by_id($id, false);
			if(intval($slider->get_id()) > 0) $modules++;
		}

		return [
			'uid'		=> $this->get_val($receipt, 'uid', $uid),
			'title'		=> $this->get_val($receipt, 'title'),
			'version'	=> $this->get_val($receipt, 'version'),
			'date'		=> $this->get_val($receipt, 'date'),
			'pages'		=> $pages,
			'modules'	=> $modules,
			'media'		=> count($this->shared_safe_media($receipt, $uid, $index)),
			'menus'		=> count($this->get_val($receipt, 'menus', [])),
			//Pages go to the trash, not the shredder: by the time a package is removed its pages may have
			//been edited for months, and "undo" has to mean something.
			'pages_trashed' => true
		];
	}

	/**
	 * Remove one install.
	 *
	 * Order matters at the top: the site options go back BEFORE the front page is trashed, or WordPress
	 * is left pointing at a post in the bin.
	 *
	 * @param string   $uid
	 * @param int|null $index which install (see get_installs()); the most recent by default
	 * @return array|WP_Error
	 */
	public function uninstall($uid, $index = null){
		if(!current_user_can('delete_pages') || !current_user_can('edit_theme_options')){
			return new WP_Error('forbidden', __('You do not have permission to remove a template package.', 'revslider'));
		}

		$receipt = $this->find_receipt($uid, $index);
		if(is_wp_error($receipt)) return $receipt;

		$removed = ['pages' => 0, 'modules' => 0, 'media' => 0, 'menus' => 0];

		$prev = $this->get_val($receipt, 'prev_options', []);
		if(!empty($prev)){
			if(isset($prev['show_on_front'])) update_option('show_on_front', $prev['show_on_front']);
			if(isset($prev['page_on_front'])) update_option('page_on_front', $prev['page_on_front']);
		}

		foreach($this->get_val($receipt, 'menus', []) as $menu_id){
			if(wp_get_nav_menu_object($menu_id)){ wp_delete_nav_menu($menu_id); $removed['menus']++; }
		}

		foreach($this->get_val($receipt, 'posts', []) as $post_id){
			if(get_post($post_id)){ wp_trash_post($post_id); $removed['pages']++; }
		}

		//Only files this install actually added, and only where no OTHER install still points at them —
		//installing a package twice dedupes the second one onto the first one's attachments.
		$safe = $this->shared_safe_media($receipt, $uid, $index);
		foreach($safe as $att_id){
			if(get_post($att_id)){ wp_delete_attachment($att_id, true); $removed['media']++; }
		}

		//Whatever is spared because something else still uses it has just lost its owner. Hand it to an
		//install that is actually using it, or the file is orphaned for good: the installs still showing
		//it deduped onto it and never listed it as theirs to remove.
		$this->hand_over_media(array_diff(array_map('intval', (array)$this->get_val($receipt, 'media', [])), $safe), $uid, $this->get_val($receipt, 'index', $index));

		foreach($this->get_val($receipt, 'modules', []) as $slider_id){
			//Same reason as in uninstall_report(): removing a package whose modules a user has already
			//deleted themselves must finish the job, not die partway through it.
			$slider = new RevSliderSlider();
			$slider->init_by_id($slider_id, false);
			if(intval($slider->get_id()) > 0){ $slider->delete_slider(); $removed['modules']++; }
		}

		$this->drop_folders($receipt);

		foreach(self::$handlers as $handler){
			if(is_callable($handler['uninstall'])) call_user_func($handler['uninstall'], $receipt);
		}

		$this->forget_receipt($uid, $this->get_val($receipt, 'index', $index));

		return $removed;
	}

	/**
	 * The attachments this install created that nothing else is still relying on.
	 *
	 * Installing a package twice does NOT copy its images: the second install finds the first one's by
	 * sha1 and points at them. So "did I create it?" is not enough to decide whether removing it is safe
	 * — the question is whether any OTHER install is still using it, which is why every install records
	 * what it uses as well as what it made.
	 * @return array
	 */
	private function shared_safe_media($receipt, $uid, $index){
		$mine = array_map('intval', (array)$this->get_val($receipt, 'media', []));
		if(empty($mine)) return [];

		$my_index = intval($this->get_val($receipt, 'index', $index));
		$in_use   = [];

		foreach($this->get_installs() as $other){
			if($this->get_val($other, 'uid') === $uid && intval($this->get_val($other, 'index')) === $my_index) continue;

			//'media_used' is the honest answer. Receipts written before it existed only listed what they
			//created, which is still better than assuming nothing else wants the file.
			$used = $this->get_val($other, 'media_used', $this->get_val($other, 'media', []));
			$in_use = array_merge($in_use, array_map('intval', (array)$used));
		}

		$safe = [];
		foreach(array_unique($mine) as $id){
			if(!in_array($id, $in_use, true)) $safe[] = $id;
		}

		return $safe;
	}

	/**
	 * Pass ownership of shared attachments to an install that still uses them, so the LAST install
	 * standing is the one that finally cleans them up.
	 *
	 * @param array  $ids       attachments this install created but may not delete
	 * @param string $uid       the package being removed
	 * @param int    $my_index  the install being removed
	 * @return void
	 */
	private function hand_over_media($ids, $uid, $my_index){
		$ids = array_values(array_filter(array_map('intval', (array)$ids)));
		if(empty($ids)) return;

		$receipts = get_option(self::RECEIPTS, []);
		if(!is_array($receipts)) return;

		$changed = false;

		foreach($ids as $id){
			foreach($receipts as $key => $list){
				if(!is_array($list)) continue;

				foreach($list as $i => $other){
					if($key === $uid && intval($i) === intval($my_index)) continue;
					if(!is_array($other)) continue;

					$used = array_map('intval', (array)$this->get_val($other, 'media_used', []));
					if(!in_array($id, $used, true)) continue;

					$owns = array_map('intval', (array)$this->get_val($other, 'media', []));
					if(!in_array($id, $owns, true)){
						$receipts[$key][$i]['media'][] = $id;
						$changed = true;
					}

					continue 3; //one new owner is enough
				}
			}
		}

		if($changed) update_option(self::RECEIPTS, $receipts, false);
	}

	/** @return array|WP_Error */
	private function find_receipt($uid, $index){
		$installs = $this->get_installs($uid);
		if(empty($installs)) return new WP_Error('no_receipt', __('This package is not recorded as installed.', 'revslider'));

		if($index === null) return $installs[count($installs) - 1];

		foreach($installs as $install){
			if(intval($this->get_val($install, 'index')) === intval($index)) return $install;
		}

		return new WP_Error('no_receipt', __('That installation could not be found.', 'revslider'));
	}

	/** @return void */
	private function forget_receipt($uid, $index){
		$receipts = get_option(self::RECEIPTS, []);
		if(!is_array($receipts) || !isset($receipts[$uid]) || !is_array($receipts[$uid])) return;

		unset($receipts[$uid][$index]);

		//Reindexing would renumber every OTHER install of this package, and the numbers are what the
		//uninstall UI just handed the user.
		if(empty(array_filter($receipts[$uid]))) unset($receipts[$uid]);

		update_option(self::RECEIPTS, $receipts, false);
	}

	/**
	 * The pages an install created, with the links the finished dialog offers. Built from the receipt at
	 * the moment it is asked for — permalinks change, and a receipt is kept for the life of the install.
	 * @return array
	 */
	private function page_links($receipt){
		$out = [];

		foreach($this->get_val($receipt, 'posts', []) as $id){
			$post = get_post($id);
			if(!$post) continue;

			$out[] = [
				'id'	=> intval($id),
				'title'	=> $post->post_title,
				'view'	=> str_replace('&amp;', '&', (string)get_permalink($id)),
				'edit'	=> str_replace('&amp;', '&', (string)get_edit_post_link($id))
			];
		}

		return $out;
	}

	/** @return array */
	public function get_receipt($uid){
		$receipts = get_option(self::RECEIPTS, []);
		if(!is_array($receipts) || !isset($receipts[$uid]) || !is_array($receipts[$uid])) return [];

		$list = $receipts[$uid];

		return empty($list) ? [] : end($list);
	}

	/**
	 * Read and parse just the manifest out of a package zip, without unpacking the media alongside it.
	 * The dry-run needs the manifest and nothing else, and a package's assets can run to tens of MB —
	 * unpacking them to answer "what would this do?" would make the preview cost as much as the install.
	 *
	 * @param string $zip_path
	 * @param array  $errors   filled with the manifest's own parse errors
	 * @return array|WP_Error
	 */
	public function read_manifest($zip_path, &$errors = []){
		$errors = [];

		if(!file_exists($zip_path)) return new WP_Error('missing', __('Package file not found', 'revslider'));
		if(!class_exists('ZipArchive')) return new WP_Error('no_zip', __('Your server cannot read zip files.', 'revslider'));

		$zip = new ZipArchive();
		if($zip->open($zip_path) !== true) return new WP_Error('bad_zip', __('This package could not be opened.', 'revslider'));

		$json = $zip->getFromName(RevSliderPackageManifest::FILE);
		$zip->close();

		if($json === false) return new WP_Error('no_manifest', __('This package is missing its manifest.', 'revslider'));

		$manifest = RevSliderPackageManifest::parse($json, $errors);
		if($manifest === false) return new WP_Error('package_invalid', implode(' ', $errors));

		return $manifest;
	}

	/**
	 * Unpack the package zip into its own temp folder.
	 * @return string|WP_Error the folder, with a trailing slash
	 */
	private function extract($zip_path){
		if(!file_exists($zip_path)) return new WP_Error('missing', __('Package file not found', 'revslider'));

		//unzip_file() and WP_Filesystem() live in wp-admin/includes/file.php, which is NOT loaded on every
		//path that can reach an install (the REST route in particular). Ask for it rather than assuming.
		if(!function_exists('WP_Filesystem')) require_once(ABSPATH . 'wp-admin/includes/file.php');

		WP_Filesystem();

		$dir = trailingslashit(get_temp_dir()) . 'rs-package-' . substr(md5($zip_path . microtime()), 0, 12) . '/';
		if(!wp_mkdir_p($dir)) return new WP_Error('mkdir', __('Could not create a temporary folder for the package', 'revslider'));

		$result = unzip_file($zip_path, $dir);
		if(is_wp_error($result)){
			$this->cleanup($dir);
			return $result;
		}

		if(!file_exists($dir . RevSliderPackageManifest::FILE)){
			$this->cleanup($dir);
			return new WP_Error('no_manifest', __('This package is missing its manifest.', 'revslider'));
		}

		return $dir;
	}

	/** @return void */
	private function cleanup($dir){
		if($dir === '' || strpos($dir, 'rs-package-') === false) return; //never recurse over a path we did not make

		global $wp_filesystem;
		if(!empty($wp_filesystem)) $wp_filesystem->delete($dir, true);
	}

	/** @return void */
	private function save_state($state){
		set_transient(self::STATE_PREFIX . $state['install_id'], $state, self::STATE_TTL);
	}

	/** @return array|WP_Error */
	private function load_state($install_id){
		$install_id = sanitize_key($install_id);
		$state = get_transient(self::STATE_PREFIX . $install_id);

		if(!is_array($state)) return new WP_Error('no_state', __('This installation timed out. Please start it again.', 'revslider'));

		return $state;
	}
}
