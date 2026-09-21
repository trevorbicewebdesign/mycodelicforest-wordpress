<?php
/**
 * Template Package manifest — the schema authority.
 *
 * A package zip (see docs/template-packages-v2.md) carries a package.json describing everything the
 * package installs: media, modules, pages with their block trees, per-block addon data, menus and
 * options. This class is the ONLY thing that decides what a valid manifest looks like. The installer
 * consumes what parse() returns; whatever AUTHORS a package calls the same parse() before it writes
 * a zip, so an export can never produce something the installer would reject.
 *
 * THE ONE RULE: a manifest never names a slider id, post id, attachment id or effectId. Those are all
 * minted at install time on the customer's site. Everything is a symbolic ref, resolved through the
 * map RevSliderPackageInstaller builds as it walks the phases. parse() enforces that refs are
 * well-formed; the installer enforces that they resolve.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Parses and validates a package manifest into a normalised array.
 *
 * Everything is static: a manifest is data, and both the reading and the writing side want to check one
 * without owning an object. parse() returns false and fills $errors rather than throwing, because the
 * dry-run wants the WHOLE list of what is wrong with a package, not just the first thing.
 */
class RevSliderPackageManifest {

	/** manifest schema version this build understands */
	const SCHEMA = 1;

	/** the file inside the package zip */
	const FILE = 'package.json';

	/** where a package's bundled media lives inside the zip */
	const ASSET_DIR = 'assets/';

	/** block-tree node types. Anything else is refused rather than silently dropped. */
	const NODE_TYPES = ['module', 'block', 'group', 'provider', 'raw'];

	/** phases the installer runs, and the only values allowed in the manifest's "order" */
	const PHASES = ['media', 'modules', 'fixids', 'pages', 'providers', 'menus', 'options'];

	/**
	 * Post meta a page node may carry. A package writes post content and menus already; letting it write
	 * arbitrary meta would let it reach into any other plugin's storage. Own prefix only, plus the two
	 * WordPress keys a page legitimately needs.
	 */
	const META_ALLOW_PREFIX = '_sr7_pkg_';
	const META_ALLOW_KEYS   = ['_wp_page_template', '_thumbnail_id'];

	/** site options a package may set. Deliberately tiny — this is the sharpest edge in the format. */
	const OPTION_ALLOW = ['show_on_front', 'front_page', 'posts_page'];

	/** ref syntax: what the exporter emits and the installer looks up. Kept strict so refs can be used in tokens. */
	const REF_PATTERN = '/^[a-z0-9][a-z0-9_-]{0,63}$/i';

	/**
	 * Parse a manifest.
	 *
	 * @param string|array $json    raw package.json contents, or an already-decoded array
	 * @param array        $errors  filled with every problem found (by reference)
	 * @return array|false  the normalised manifest, or false when $errors is non-empty
	 */
	public static function parse($json, &$errors = []){
		$errors = [];

		$raw = is_array($json) ? $json : json_decode((string)$json, true);
		if(!is_array($raw)){
			$errors[] = __('Package manifest is not valid JSON.', 'revslider');
			return false;
		}

		$schema = isset($raw['schema']) ? intval($raw['schema']) : 0;
		if($schema < 1){
			$errors[] = __('Package manifest has no schema version.', 'revslider');
			return false;
		}
		//A newer package on an older plugin: refuse cleanly instead of installing half of it. The catalogue's
		//own "required" gate normally stops this first, but a manifest can be newer than the row that carries it.
		if($schema > self::SCHEMA){
			$errors[] = sprintf(__('This package needs a newer version of Slider Revolution (manifest v%1$d, this build reads v%2$d).', 'revslider'), $schema, self::SCHEMA);
			return false;
		}

		$m = [
			'schema'		=> $schema,
			'uid'			=> self::str($raw, 'uid'),
			'title'			=> self::str($raw, 'title'),
			'version'		=> self::str($raw, 'version', '1.0.0'),
			'requires'		=> self::parse_requires($raw),
			'assets'		=> [],
			'modules'		=> [],
			'pages'			=> [],
			'menus'			=> [],
			'references'	=> [],
			'options'		=> [],
			'order'			=> self::PHASES
		];

		if($m['uid'] === '')	$errors[] = __('Package manifest is missing its uid.', 'revslider');
		if($m['title'] === '')	$errors[] = __('Package manifest is missing its title.', 'revslider');

		$m['assets']		= self::parse_assets($raw, $errors);
		$m['modules']		= self::parse_modules($raw, $errors);
		$m['pages']			= self::parse_pages($raw, $m, $errors);
		$m['menus']			= self::parse_menus($raw, $errors);
		$m['references']	= self::parse_references($raw, $m['assets']);
		$m['options']		= self::parse_options($raw, $errors);

		if(isset($raw['order']) && is_array($raw['order'])){
			$order = [];
			foreach($raw['order'] as $phase){
				if(in_array($phase, self::PHASES, true) && !in_array($phase, $order, true)) $order[] = $phase;
			}
			//A partial "order" must not silently drop the phases it forgot to mention.
			foreach(self::PHASES as $phase) if(!in_array($phase, $order, true)) $order[] = $phase;
			$m['order'] = $order;
		}

		self::check_refs($m, $errors);

		return empty($errors) ? $m : false;
	}

	/**
	 * Every ref a manifest USES has to be one it DEFINES. Catching this here means the installer can
	 * resolve refs without a null check on every lookup, and the dry-run can report a broken package
	 * before it writes anything.
	 * @return void
	 */
	private static function check_refs($m, &$errors){
		$modules = [];
		foreach($m['modules'] as $mod) $modules[$mod['ref']] = true;

		$pages = [];
		foreach($m['pages'] as $p) $pages[$p['ref']] = true;

		foreach($m['pages'] as $p){
			self::check_node_refs($p['blocks'], $modules, $m['assets'], $p['ref'], $errors);

			if($p['thumbnail'] !== '' && !isset($m['assets'][$p['thumbnail']])){
				$errors[] = sprintf(__('Page "%1$s" uses a thumbnail that the package does not ship: %2$s', 'revslider'), $p['ref'], $p['thumbnail']);
			}
		}

		foreach($m['menus'] as $menu){
			self::check_menu_refs($menu['items'], $pages, $menu['name'], $errors);
		}

		foreach(['front_page', 'posts_page'] as $k){
			if(isset($m['options'][$k]) && !isset($pages[$m['options'][$k]])){
				$errors[] = sprintf(__('Package option "%1$s" points at an unknown page: %2$s', 'revslider'), $k, $m['options'][$k]);
			}
		}
	}

	/** @return void */
	private static function check_node_refs($nodes, $modules, $assets, $page_ref, &$errors){
		foreach($nodes as $n){
			if($n['type'] === 'module' && !isset($modules[$n['ref']])){
				$errors[] = sprintf(__('Page "%1$s" places a module the package does not list: %2$s', 'revslider'), $page_ref, $n['ref']);
			}
			if(isset($n['media'])){
				foreach($n['media'] as $path){
					if(!isset($assets[$path])) $errors[] = sprintf(__('Page "%1$s" uses media the package does not ship: %2$s', 'revslider'), $page_ref, $path);
				}
			}
			if(!empty($n['inner'])) self::check_node_refs($n['inner'], $modules, $assets, $page_ref, $errors);
		}
	}

	/** @return void */
	private static function check_menu_refs($items, $pages, $menu_name, &$errors){
		foreach($items as $item){
			if($item['page'] !== '' && !isset($pages[$item['page']])){
				$errors[] = sprintf(__('Menu "%1$s" links to an unknown page: %2$s', 'revslider'), $menu_name, $item['page']);
			}
			if(!empty($item['children'])) self::check_menu_refs($item['children'], $pages, $menu_name, $errors);
		}
	}

	/** @return array */
	private static function parse_requires($raw){
		$r = isset($raw['requires']) && is_array($raw['requires']) ? $raw['requires'] : [];

		$addons = [];
		foreach((isset($r['addons']) && is_array($r['addons'])) ? $r['addons'] : [] as $slug){
			$slug = self::slug($slug);
			if($slug !== '') $addons[] = $slug;
		}

		$plugins = [];
		foreach((isset($r['plugins']) && is_array($r['plugins'])) ? $r['plugins'] : [] as $p){
			if(!is_array($p)) continue;
			$slug = self::slug(isset($p['slug']) ? $p['slug'] : '');
			if($slug === '') continue;
			$plugins[] = [
				'slug'		=> $slug,
				'name'		=> self::str($p, 'name', $slug),
				//Third-party plugins are declared and detected, never installed for the user.
				'required'	=> !empty($p['required'])
			];
		}

		return [
			'revslider'	=> self::str($r, 'revslider'),
			'addons'	=> $addons,
			'plugins'	=> $plugins
		];
	}

	/**
	 * assets: { "media/hero.jpg": {sha1, size, alt} }. The sha1 is what lets a reinstall find the image
	 * it already sideloaded instead of adding a second copy.
	 * @return array
	 */
	private static function parse_assets($raw, &$errors){
		$out = [];
		if(!isset($raw['assets']) || !is_array($raw['assets'])) return $out;

		foreach($raw['assets'] as $path => $meta){
			$clean = self::asset_path($path);
			if($clean === ''){
				$errors[] = sprintf(__('Package ships an asset with an unsafe path: %s', 'revslider'), (string)$path);
				continue;
			}
			if(!is_array($meta)) $meta = [];
			$out[$clean] = [
				'sha1'	=> preg_match('/^[a-f0-9]{40}$/i', isset($meta['sha1']) ? $meta['sha1'] : '') ? strtolower($meta['sha1']) : '',
				'size'	=> isset($meta['size']) ? max(0, intval($meta['size'])) : 0,
				'alt'	=> self::str($meta, 'alt')
			];
		}

		return $out;
	}

	/** @return array */
	private static function parse_modules($raw, &$errors){
		$out = [];
		$seen = [];
		if(!isset($raw['modules']) || !is_array($raw['modules'])) return $out;

		foreach($raw['modules'] as $mod){
			if(!is_array($mod)) continue;
			$ref = self::ref($mod, 'ref');
			if($ref === ''){
				$errors[] = __('Package lists a module without a usable ref.', 'revslider');
				continue;
			}
			if(isset($seen[$ref])){
				$errors[] = sprintf(__('Package lists the module ref "%s" more than once.', 'revslider'), $ref);
				continue;
			}
			$seen[$ref] = true;

			//"uid" is the catalogue template this module comes from. A module authored FOR the package has
			//none yet — tp-admin binds it to the child template row it creates from the exported zip, and until
			//then the manifest is legal but not installable. The dry-run is what surfaces that.
			//
			//"from" is what the module was CALLED on the site it was exported from. Nothing installs it — it
			//exists so a provider's remap() can rewrite references it finds in its own data: a ChalkLine
			//anchor bound inside a module holds a CSS selector naming that module's alias or its
			//"SR7_<sliderId>_<serial>" element id, and both of those change on the customer's site. Without
			//the old values there is nothing to search for.
			$from = (isset($mod['from']) && is_array($mod['from'])) ? $mod['from'] : [];

			$out[] = [
				'ref'	=> $ref,
				'uid'	=> self::str($mod, 'uid'),
				'title'	=> self::str($mod, 'title'),
				'from'	=> [
					'alias'		=> self::str($from, 'alias'),
					'id'		=> max(0, intval(self::str($from, 'id', '0'))),
					'html_id'	=> self::str($from, 'html_id'),
					//The module's slide ids IN ORDER, as they were on the authoring site. A layer's element
					//id is "<moduleHtmlId>-<slideId>-<layerIndex>", so any selector picked inside a module
					//carries one — and slide ids are handed out fresh on every import. Pairing this list
					//positionally with the installed module's own slides is the only way to translate them:
					//the import's own map runs from the ZIP's internal ids, which the authoring site never saw.
					'slides'	=> self::int_list(isset($from['slides']) ? $from['slides'] : [])
				]
			];
		}

		return $out;
	}

	/** @return array */
	private static function parse_pages($raw, $m, &$errors){
		$out = [];
		$seen = [];
		if(!isset($raw['pages']) || !is_array($raw['pages'])) return $out;

		foreach($raw['pages'] as $page){
			if(!is_array($page)) continue;
			$ref = self::ref($page, 'ref');
			if($ref === ''){
				$errors[] = __('Package lists a page without a usable ref.', 'revslider');
				continue;
			}
			if(isset($seen[$ref])){
				$errors[] = sprintf(__('Package lists the page ref "%s" more than once.', 'revslider'), $ref);
				continue;
			}
			$seen[$ref] = true;

			$status = self::str($page, 'status', 'draft');
			if(!in_array($status, ['draft', 'publish', 'private'], true)) $status = 'draft';

			$out[] = [
				'ref'		=> $ref,
				'title'		=> self::str($page, 'title', $ref),
				'slug'		=> sanitize_title(self::str($page, 'slug')),
				'post_type'	=> self::post_type($page),
				'status'	=> $status,
				'template'	=> self::str($page, 'template'),
				'thumbnail'	=> self::asset_path(self::str($page, 'thumbnail')),
				'meta'		=> self::parse_meta($page, $ref, $errors),
				'blocks'	=> self::parse_nodes(isset($page['blocks']) ? $page['blocks'] : [], $ref, $errors)
			];
		}

		return $out;
	}

	/**
	 * A package may only create the public post types it declares, and only ones that exist here. An
	 * unknown type would fail silently inside wp_insert_post and leave the page map with a hole.
	 * @return string
	 */
	private static function post_type($page){
		$pt = self::str($page, 'post_type', 'page');
		return post_type_exists($pt) ? $pt : 'page';
	}

	/** @return array */
	private static function parse_meta($page, $page_ref, &$errors){
		$out = [];
		if(!isset($page['meta']) || !is_array($page['meta'])) return $out;

		foreach($page['meta'] as $key => $value){
			$key = (string)$key;
			$ok = in_array($key, self::META_ALLOW_KEYS, true) || strpos($key, self::META_ALLOW_PREFIX) === 0;
			if(!$ok){
				$errors[] = sprintf(__('Page "%1$s" tries to write a post meta key packages may not set: %2$s', 'revslider'), $page_ref, $key);
				continue;
			}
			//Scalars only. A nested array here is how a package would smuggle a serialised payload into meta.
			if(is_array($value) || is_object($value)){
				$errors[] = sprintf(__('Page "%1$s" sets meta "%2$s" to a value that is not a simple string or number.', 'revslider'), $page_ref, $key);
				continue;
			}
			$out[$key] = (string)$value;
		}

		return $out;
	}

	/**
	 * The block tree. Recursive, and depth-capped: a hand-edited manifest with a cycle would otherwise
	 * recurse until PHP dies, and a page nested 32 deep is a bug either way.
	 * @return array
	 */
	private static function parse_nodes($nodes, $page_ref, &$errors, $depth = 0){
		$out = [];
		if(!is_array($nodes)) return $out;

		if($depth > 32){
			$errors[] = sprintf(__('Page "%s" nests blocks too deeply.', 'revslider'), $page_ref);
			return $out;
		}

		foreach($nodes as $n){
			if(!is_array($n)) continue;

			$type = self::str($n, 'type');
			if(!in_array($type, self::NODE_TYPES, true)){
				$errors[] = sprintf(__('Page "%1$s" contains an unknown block node type: %2$s', 'revslider'), $page_ref, $type === '' ? '(none)' : $type);
				continue;
			}

			$node = ['type' => $type];

			switch($type){
				case 'module':
					$node['ref']	= self::ref($n, 'ref');
					$node['usage']	= (self::str($n, 'usage') === 'modal') ? 'modal' : '';
					$node['attrs']	= self::scalar_map(isset($n['attrs']) ? $n['attrs'] : []);
					if($node['ref'] === ''){
						$errors[] = sprintf(__('Page "%s" places a module without a ref.', 'revslider'), $page_ref);
						continue 2;
					}
					break;

				case 'block':
				case 'group':
					$node['name']	= self::block_name($n);
					$node['attrs']	= self::scalar_map(isset($n['attrs']) ? $n['attrs'] : [], true);
					//A leaf block's own markup. A CONTAINER splits it instead: html_open is everything before
					//its children, html_close everything after — which is how Gutenberg's innerContent stores
					//a wrapper, and the only way to put children back INSIDE the wrapper div on install.
					$node['html']		= isset($n['html']) ? (string)$n['html'] : '';
					$node['html_open']	= isset($n['html_open']) ? (string)$n['html_open'] : '';
					$node['html_close']	= isset($n['html_close']) ? (string)$n['html_close'] : '';
					if($node['name'] === ''){
						$errors[] = sprintf(__('Page "%s" contains a block without a valid block name.', 'revslider'), $page_ref);
						continue 2;
					}
					break;

				case 'provider':
					$node['provider']	= self::slug(self::str($n, 'provider'));
					$node['ref']		= self::ref($n, 'ref');
					//data = the meta payload (meta-backed types); attrs = block attributes (attribute-backed
					//types). A type may legitimately use either or both — the installer asks the registered
					//type which it is, rather than trusting the manifest to say.
					$node['data']		= (isset($n['data']) && is_array($n['data'])) ? $n['data'] : [];
					$node['attrs']		= self::scalar_map(isset($n['attrs']) ? $n['attrs'] : [], true);
					if($node['provider'] === '' || $node['ref'] === ''){
						$errors[] = sprintf(__('Page "%s" contains an addon effect without a provider slug or ref.', 'revslider'), $page_ref);
						continue 2;
					}
					break;

				case 'raw':
					$node['html'] = isset($n['html']) ? (string)$n['html'] : '';
					if(trim($node['html']) === '') continue 2; //an empty raw node is noise, not an error
					break;
			}

			//Providers attached to THIS block rather than standing on their own: a Scroll Animation on a
			//core/group, a Pan & Zoom on a core/image. The installer mints each one an effectId and writes it
			//into the host block's claimed attribute.
			$node['providers'] = self::parse_attached($n, $page_ref, $errors);

			//Media this node references from inside markup it carries, so check_refs can verify the package
			//actually ships it. The installer substitutes %%SR7_MEDIA:path%% in html at install time.
			$node['media'] = self::node_media($n);

			if(isset($n['inner'])) $node['inner'] = self::parse_nodes($n['inner'], $page_ref, $errors, $depth + 1);

			$out[] = $node;
		}

		return $out;
	}

	/** @return array */
	private static function parse_attached($n, $page_ref, &$errors){
		$out = [];
		if(!isset($n['providers']) || !is_array($n['providers'])) return $out;

		foreach($n['providers'] as $p){
			if(!is_array($p)) continue;
			$slug	= self::slug(self::str($p, 'provider'));
			$ref	= self::ref($p, 'ref');
			if($slug === '' || $ref === ''){
				$errors[] = sprintf(__('Page "%s" attaches an addon effect without a provider slug or ref.', 'revslider'), $page_ref);
				continue;
			}
			$out[] = [
				'provider'	=> $slug,
				'ref'		=> $ref,
				'data'		=> (isset($p['data']) && is_array($p['data'])) ? $p['data'] : [],
				'attrs'		=> (isset($p['attrs']) && is_array($p['attrs'])) ? self::scalar_map($p['attrs'], true) : []
			];
		}

		return $out;
	}

	/** @return array asset paths referenced by this node */
	private static function node_media($n){
		$out = [];
		if(!isset($n['media']) || !is_array($n['media'])) return $out;
		foreach($n['media'] as $path){
			$clean = self::asset_path($path);
			if($clean !== '') $out[] = $clean;
		}
		return $out;
	}

	/** @return array */
	private static function parse_menus($raw, &$errors){
		$out = [];
		if(!isset($raw['menus']) || !is_array($raw['menus'])) return $out;

		foreach($raw['menus'] as $menu){
			if(!is_array($menu)) continue;
			$name = self::str($menu, 'name');
			if($name === ''){
				$errors[] = __('Package lists a menu without a name.', 'revslider');
				continue;
			}
			$out[] = [
				'name'		=> $name,
				'location'	=> self::slug(self::str($menu, 'location')),
				'items'		=> self::parse_menu_items(isset($menu['items']) ? $menu['items'] : [], $errors)
			];
		}

		return $out;
	}

	/** @return array */
	private static function parse_menu_items($items, &$errors, $depth = 0){
		$out = [];
		if(!is_array($items) || $depth > 8) return $out;

		foreach($items as $item){
			if(!is_array($item)) continue;

			$page = self::ref($item, 'page');
			$url  = self::str($item, 'url');
			//An item is either a page ref we resolve at install, or a literal URL. Neither means a dead entry.
			if($page === '' && $url === ''){
				$errors[] = __('A menu item links to neither a package page nor a URL.', 'revslider');
				continue;
			}

			$out[] = [
				'title'		=> self::str($item, 'title'),
				'page'		=> $page,
				'url'		=> ($page === '' && filter_var($url, FILTER_VALIDATE_URL)) ? $url : '',
				'children'	=> self::parse_menu_items(isset($item['children']) ? $item['children'] : [], $errors, $depth + 1)
			];
		}

		return $out;
	}

	/**
	 * Credits for bundled media and content. Display-only — nothing here drives the install, which is
	 * why a credit is never a reason to refuse a package.
	 *
	 * A credit may name the asset it belongs to, or nothing at all (then it covers the whole package).
	 * An asset the package does NOT ship loses the link but keeps the credit: attribution someone typed
	 * in by hand is worth more than the pointer, and dropping the whole entry over a stale path would
	 * quietly remove a licence notice the package may be legally required to show.
	 *
	 * @return array
	 */
	private static function parse_references($raw, $assets = []){
		$out = [];
		if(!isset($raw['references']) || !is_array($raw['references'])) return $out;

		foreach($raw['references'] as $r){
			if(!is_array($r)) continue;
			$title = self::str($r, 'title');
			if($title === '') continue;

			$url	= self::str($r, 'url');
			$asset	= self::asset_path(self::str($r, 'asset'));

			$out[] = [
				'title'		=> $title,
				'author'	=> self::str($r, 'author'),
				'url'		=> filter_var($url, FILTER_VALIDATE_URL) ? $url : '',
				'license'	=> self::str($r, 'license'),
				'asset'		=> isset($assets[$asset]) ? $asset : ''
			];
		}

		return $out;
	}

	/** @return array */
	private static function parse_options($raw, &$errors){
		$out = [];
		if(!isset($raw['options']) || !is_array($raw['options'])) return $out;

		foreach($raw['options'] as $key => $value){
			if(!in_array($key, self::OPTION_ALLOW, true)){
				$errors[] = sprintf(__('Package tries to set a site option it may not: %s', 'revslider'), (string)$key);
				continue;
			}
			if(is_array($value) || is_object($value)) continue;
			$out[$key] = (string)$value;
		}

		if(isset($out['show_on_front']) && !in_array($out['show_on_front'], ['page', 'posts'], true)) unset($out['show_on_front']);

		return $out;
	}

	/**
	 * A path inside the package's assets/ folder. Refuses absolute paths, traversal and backslashes — this
	 * string ends up joined to a filesystem root when the installer sideloads media.
	 * @return string '' when the path is unusable
	 */
	public static function asset_path($path){
		if(!is_string($path) || $path === '') return '';

		$path = str_replace('\\', '/', $path);
		if($path[0] === '/' || strpos($path, '..') !== false || preg_match('#^[a-zA-Z]:#', $path)) return '';
		if(strpos($path, "\0") !== false) return '';
		if(!preg_match('#^[a-zA-Z0-9._/-]+$#', $path)) return '';

		return $path;
	}

	/**
	 * A block name. Namespaced (core/paragraph, themepunch/revslider) — the unnamespaced form is only ever
	 * a classic-editor freeform block, which the exporter emits as a raw node instead.
	 * @return string
	 */
	private static function block_name($n){
		$name = self::str($n, 'name');
		return preg_match('#^[a-z][a-z0-9-]*/[a-z][a-z0-9-]*$#', $name) ? $name : '';
	}

	/** @return string */
	private static function ref($a, $k){
		$v = self::str($a, $k);
		return preg_match(self::REF_PATTERN, $v) ? $v : '';
	}

	/** @return array a plain list of positive integers */
	private static function int_list($v){
		$out = [];
		if(!is_array($v)) return $out;
		foreach($v as $n){
			$n = intval($n);
			if($n > 0) $out[] = $n;
		}
		return $out;
	}

	/** @return string */
	private static function slug($v){
		if(!is_string($v)) return '';
		return preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/i', $v) ? $v : '';
	}

	/** @return string */
	private static function str($a, $k, $default = ''){
		if(!is_array($a) || !isset($a[$k])) return $default;
		if(is_array($a[$k]) || is_object($a[$k])) return $default;
		if(is_bool($a[$k])) return $a[$k] ? '1' : '';
		return (string)$a[$k];
	}

	/**
	 * Block attributes. Gutenberg attributes are legitimately nested (an sr7Anim object, a gallery's ids),
	 * so $nested allows one level of arrays while still refusing objects and resources.
	 * @return array
	 */
	private static function scalar_map($a, $nested = false, $depth = 0){
		$out = [];
		if(!is_array($a)) return $out;

		foreach($a as $k => $v){
			if(is_object($v) || is_resource($v)) continue;
			if(is_array($v)){
				if(!$nested || $depth >= 8) continue;
				$out[$k] = self::scalar_map($v, true, $depth + 1);
				continue;
			}
			$out[$k] = $v;
		}

		return $out;
	}
}
