<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Font loading for the front end.
 *
 * Collects the fonts every slider on the page needs and emits the corresponding @font-face / Google Fonts
 * imports. Google fonts can optionally be downloaded into uploads/themepunch/gfonts and served locally
 * (GDPR), and since 7.1.0 users can upload their own font files - those live in uploads/revslider/fonts.
 */
class RevSliderFonts extends RevSliderFunctions {

	
	/**
	 * Load Used Google Fonts
	 * add google fonts of all sliders found on the page
	 * @since: 6.0
	 * @return void echoes the import markup
	 */
	public function load_google_fonts(){
		$fonts	= $this->print_clean_font_import_v7();
		
		if(empty($fonts)) return;

		echo "\n".$fonts."\n";

		global $SR_GLOBALS;
		if(empty($SR_GLOBALS['fonts']['loaded']) && empty($SR_GLOBALS['fonts']['custom'])) return;
	
		$domFonts = [];
		echo '<script>'."\n";
		$branches = ['loaded', 'custom'];
		foreach($branches as $branch){
			foreach($SR_GLOBALS['fonts'][$branch] ?? [] as $handle => $values){
				$handle = preg_replace('/[^-0-9a-zA-Z+]/', '', str_replace(' ', '+', $handle));
				if(isset($values['url'])){
					echo "_tpt.R.fonts.customFonts['". $handle ."'] = ". json_encode($values) .";"."\n";
				}else{
					$domFonts[$handle] = [
						'normal'	=> $this->get_val($values, ['variants', 'normal'], []),
						'italic'	=> $this->get_val($values, ['variants', 'italic'], [])
					];
				}
			}
		}

		if(!empty($domFonts)){
			echo "_tpt.R.fonts.domFonts = ". json_encode($domFonts) .";"."\n";
		}
		echo '</script>'."\n";
	}

	/**
	 * print html font import
	 * builds the <link>/@import markup for every queued font, honouring the "download locally" setting
	 * @return string
	 */
	public function print_clean_font_import_v7(){
		global $SR_GLOBALS;

		$global_settings	= $this->get_global_settings();
		$font_download		= $this->get_val($global_settings, ['fonts', 'download'], 'off');
		$ret				= '';
		$family_query		= '';
		$href_imports		= '';
		$font_first			= true;
		$fonts				= [];

		if(!empty($SR_GLOBALS['fonts']['queue'])){
			foreach($SR_GLOBALS['fonts']['queue'] as $font_name => $font_settings){
				if(!isset($font_settings['url'])) continue; //if url is not set, continue
				
				if(isset($font_settings['load']) && $font_settings['load'] === true){ //only load if we are true
					$ret .=  RS_T3.'<link href="' . esc_html($font_settings['url']) . '" rel="stylesheet" property="stylesheet" media="all" type="text/css" />'."\n";
				}
				if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name])) $SR_GLOBALS['fonts']['loaded'][$font_name] = [];
				$SR_GLOBALS['fonts']['loaded'][$font_name] = ['url' => $this->get_val($font_settings, 'url')];
			}
		}

		if($font_download === 'disable') return $ret;

		if(!empty($SR_GLOBALS['fonts']['queue'])){
			foreach($SR_GLOBALS['fonts']['queue'] as $font_name => $font_settings){
				if(!is_bool($font_settings)) continue;
				$loaded = false;
				switch($font_name){
					case 'Materialicons':
						$ret .= RS_T3.'<link href="' . RS_PLUGIN_URL_CLEAN . 'public/css/fonts/material/material-icons.css" rel="stylesheet" property="stylesheet" media="all" type="text/css" />'."\n";
						$loaded = ['url' => RS_PLUGIN_URL_CLEAN . 'public/css/fonts/material/material-icons.css', 'icon' => true, 'family' => 'Materialicons'];
					break;
					case 'FontAwesome':
						$ret .= RS_T3.'<link href="' . RS_PLUGIN_URL_CLEAN . 'public/css/fonts/font-awesome/css/font-awesome.css" rel="stylesheet" property="stylesheet" media="all" type="text/css" />'."\n";
						$loaded = ['url' => RS_PLUGIN_URL_CLEAN . 'public/css/fonts/font-awesome/css/font-awesome.css', 'icon' => true, 'family' => 'FontAwesome'];
					break;
					case 'PeIcon':
						$ret .= RS_T3.'<link href="' . RS_PLUGIN_URL_CLEAN . 'public/css/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" rel="stylesheet" property="stylesheet" media="all" type="text/css" />'."\n";
						$loaded = ['url' => RS_PLUGIN_URL_CLEAN . 'public/css/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css', 'icon' => true, 'family' => 'Pe-icon-7-stroke'];
					break;
					case 'RevIcon':
						$ret .= RS_T3.'<link href="' . RS_PLUGIN_URL_CLEAN . 'public/css/fonts/revicons/css/revicons.css" rel="stylesheet" property="stylesheet" media="all" type="text/css" />'."\n";
						$loaded = ['url' => RS_PLUGIN_URL_CLEAN . 'public/css/fonts/revicons/css/revicons.css', 'icon' => true, 'family' => 'revicons'];
					break;
				}
				if($loaded === false) continue;

				if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name])) $SR_GLOBALS['fonts']['loaded'][$font_name] = $loaded;
			}
		}

		if(!empty($SR_GLOBALS['fonts']['queue'])){
			$this->remove_wordpress_global_fonts();

			$font_types = ['normal', 'italic'];
			
			foreach($SR_GLOBALS['fonts']['queue'] as $font_name => $font_settings){
				if(empty($font_name)) continue;
				if(isset($font_settings['url']) && !empty($font_settings['url'])) continue; //ignore custom
			
				$_variants	= $this->get_val($font_settings, 'variants', ['normal' => [], 'italic' => []]);
				$_subsets	= $this->get_val($font_settings, 'subsets', []);
				if(!empty($_variants['normal']) || !empty($_variants['italic']) || !empty($_subsets)){
					if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name])) $SR_GLOBALS['fonts']['loaded'][$font_name] = [];
					if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name]['variants'])) $SR_GLOBALS['fonts']['loaded'][$font_name]['variants'] = [];
					if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name]['variants']['normal'])) $SR_GLOBALS['fonts']['loaded'][$font_name]['variants']['normal'] = [];
					if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name]['variants']['italic'])) $SR_GLOBALS['fonts']['loaded'][$font_name]['variants']['italic'] = [];
					if(!isset($SR_GLOBALS['fonts']['loaded'][$font_name]['subsets'])) $SR_GLOBALS['fonts']['loaded'][$font_name]['subsets'] = [];
					
					if(strpos($font_name, 'href=') === false){
						$google_slug = str_replace(["'", '"', '+'], ['', '', ' '], $font_name);
						if(!isset($googlefonts)) $googlefonts = self::get_google_fonts();
						if(!isset($googlefonts[$google_slug])) continue; //check if font found in our own google fonts list

						$fonts[$font_name] = ['font' => $font_name, 'normal' => $this->get_val($_variants, 'normal', []), 'italic' => $this->get_val($_variants, 'italic', [])]; //$font_query; //we do not want to add the subsets
						$font_query = '';

						if($font_first == false) $font_query .= '&family=';
						$font_query .= preg_replace('/[^-0-9a-zA-Z+]/', '', str_replace(' ', '+', $font_name)).':';

						if(!empty($_variants['normal']) || !empty($_variants['italic'])){
							$is_first_weight = true;
							$italic = false;
							if(!empty($font_settings['variants']['italic'])){
								$font_query .= 'ital,';
								$italic = true;
							}
							$font_query .= 'wght@';

							$weights = [];
							foreach($font_types as $font_type){
								if(!isset($font_settings['variants'][$font_type])) continue;
								$weights[$font_type] = [];
								foreach($font_settings['variants'][$font_type] as $variant){
									if(in_array($variant, $SR_GLOBALS['fonts']['loaded'][$font_name]['variants'][$font_type], true)) continue;

									$variant_key = ($font_type === 'italic') ? $variant.'italic' : $variant;
									if(!in_array($variant_key , $googlefonts[$google_slug]['variants'])){
										if($font_type === 'italic'){
											//check if it exists in normal weights and add if not already added
											if(in_array($variant, $googlefonts[$google_slug]['variants'])){
												if(!isset($weights['normal'])) $weights['normal'] = [];
												if(!in_array($variant, $weights['normal'])) $weights['normal'][] = $variant;
											}
										}
										continue;
									}

									$SR_GLOBALS['fonts']['loaded'][$font_name]['variants'][$font_type][] = $variant;
									//if($variant === 'italic') continue;
									
									$weights[$font_type][] = $variant;
								}
							}
							if(empty($weights)) continue;
							
							$i = 0;
							foreach($weights ?? [] as $weight_type => $weight_values){
								if(empty($weight_values)) continue;

								asort($weight_values); //sort as we need to start from low to high

								foreach($weight_values as $weight){
									if(!$is_first_weight) $font_query .= ';';

									$font_query .= ($italic === true) ? $i.','.$weight : $weight;
									$is_first_weight = false;
								}
								$i++;
							}
							
							//we did not add any variants, so dont add the font
							if($is_first_weight === true) continue;

							$family_query .= $font_query;
						}
					}else{
						if($font_download === 'preload'){
							//from URL to normal italic formats fetching
							$font_full = $this->get_font_weights_by_url($font_name);
							if(!empty($font_full['font'])) $fonts[$font_full['font']] = $font_full;
						}else{
							$href_imports .= html_entity_decode(stripslashes($font_name));
						}
					}
					$font_first = false;
				}
			}
		}

		if($font_download === 'preload'){
			$ret .= $this->preload_fonts($fonts);
		}else{
			$url = $this->modify_fonts_url('https://fonts.googleapis.com/css2?family=');
			$ret .= ($family_query !== '') ?  RS_T3.'<link href="'.$url.$family_query.'&display=swap" rel="stylesheet" property="stylesheet" media="all" type="text/css" >'."\n" : '';
			$ret .= ($href_imports !== '') ?  RS_T3.html_entity_decode(stripslashes($href_imports)) : '';
		}

		return apply_filters('revslider_printCleanFontImport', $ret);
	}

	/**
	 * create a list of normal and italic weights to be useable by preload_fonts()
	 * 
	 * @param string $font_url - the font url to be parsed
	 * @return array - array with font name, normal weights, and italic weights
	 */
	public function get_font_weights_by_url($font_url){
		$font_full = ['font' => '', 'normal' => [], 'italic' => []];

		if(strpos($font_url, ':') === false) return $font_full;
		$f_raw = explode(':', $font_url);

		if(empty($f_raw) || !is_array($f_raw) || !isset($f_raw[1])) return $font_full;

		if(strpos($f_raw[1], ',') !== false && strpos($f_raw[1], ';') === false || intval($f_raw[1]) > 0){
			$f_raw[1]	= str_replace(['%2C', 'wght', '@0,', ';0,', '@', '&family='], [',', '', '', ',', '', ''], $f_raw[1]);
			$font_full['font'] = $f_raw[0];
			$weights = explode(',', $f_raw[1]);
			foreach($weights ?? [] as $wk => $weight){
				$weight = strtolower($weight);
				if(strpos($weight, 'ital') !== false){
					$weight = str_replace(['italic', 'ital'], '', $weight);
					if(intval($weight) === 0) $weight = 400;
					$font_full['italic'][$weight] = $weight;
				}else{
					$font_full['normal'][$weight] = $weight;
				}
			}
		}else{ //no /css2 process here as we seem to be /css
			$f_raw[1]	= str_replace(['%2C', 'wght', '@0,', ';0,', '@', ';', '&family='], [',', '', '', ',', '', ',', ''], $f_raw[1]);
			$weights	= explode(',', $f_raw[1]);
			foreach($weights ?? [] as $wk => $weight){
				if($weight === 'ital' || $weight === 'italic'){
					$wk = intval($wk);
					if($wk < 100) continue;

					$font_full['italic'][$wk] = $wk;
				}else{
					$weight = intval($weight);
					if($weight < 100) continue;

					$font_full['normal'][$weight] = $weight;
				}
			}
		}

		if(empty($font_full['normal']) && empty($font_full['italic'])) $font_full['normal'] = ['400'];
		if(!empty($font_full['normal'])) $font_full['normal'] = array_unique($font_full['normal']);
		if(!empty($font_full['italic'])) $font_full['italic'] = array_unique($font_full['italic']);

		return $font_full;
	}
	
	/**
	 * preloading fonts and return style for it
	 * @param bool $style true wraps the result in a <style> block instead of returning bare rules
	 * @return string
	 **/
	public function preload_fonts($fonts, $style = true, $all = false){
		$ret = '';
		
		if(empty($fonts)) return $ret;
	
		if(!function_exists('download_url')) require_once ABSPATH . 'wp-admin/includes/file.php';
		$allowed_mime_types = ['ttf'  => 'font/ttf', 'woff' => 'font/woff', 'woff2' => 'font/woff2', 'otf'  => 'font/otf'];
		$upload_dir	= wp_upload_dir();
		$base_dir	= $upload_dir['basedir'];
		$base_url	= $this->remove_http($upload_dir['baseurl']);
		$tp_google_ts = $this->get_options(['timestamps', 'google-fonts'], 0);
		$types		= [
			//--- original
			'ttf'	=> ['user-agent' => ''],
			'woff'	=> ['accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.10240'],
			'woff2'	=> ['accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 'user-agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0'],
			//--- original end
			/*--- alternative
			//'ttf'	=> ['user-agent' => 'Mozilla/5.0 (Unknown; Linux x86_64) AppleWebKit/538.1 (KHTML, like Gecko) Safari/538.1 Daum/4.1'],
			//'woff'	=> ['user-agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0'],
			//'woff2'	=> ['user-agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0'],
			//'eot'	=> ['user-agent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)'],
			//'svg'	=> ['user-agent' => 'Mozilla/4.0 (iPad; CPU OS 4_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/4.1 Mobile/9A405 Safari/7534.48.3'],
			//--- alternative 2 end */
		];
		$fonts_css	= $this->get_options(['fonts', 'fonts'], [], false, self::OPTIONS_FONTS);
		if(!is_array($fonts_css)) $fonts_css = [];
		$load = 'ttf';

		if($all === false){
			$_browser	= $this->get_browser();
			$version	= $this->get_val($_browser, 'version', '0');
			$browser	= $this->get_val($_browser, 'name', '');
			//Chrome 6+ , Firefox 3.6+ IE9+, Safari 5.1+  -> WOFF
			//Chrome 26+, Operae23+, Firefox 39+ -> Woff2
			switch(strtolower($browser)){
				case 'mozilla firefox':
					if(version_compare($version, '3.6', '>=')) $load = 'woff';
					if(version_compare($version, '39', '>=')) $load = 'woff2';
				break;
				case 'edge':
					$load = 'woff2';
				break;
				case 'google chrome':
					if(version_compare($version, '6', '>=')) $load = 'woff';
					if(version_compare($version, '26', '>=')) $load = 'woff2';
				break;
				case 'apple safari':
					if(version_compare($version, '5.1', '>=')) $load = 'woff';
				break;
				case 'opera':
					if(version_compare($version, '23', '>=')) $load = 'woff';
				break;
				case 'internet explorer':
					if(version_compare($version, '9', '>=')) $load = 'woff';
				break;
			}
		}
		if(!isset($googlefonts)) $googlefonts = self::get_google_fonts();

		foreach($fonts ?? [] as $key => $_font){
			//check if we downloaded the font already
			$font		= (isset($_font['font'])) ? $_font['font'] : $key;
			$font_name	= preg_replace('/[^-a-z0-9 ]+/i', '', $font);
			$font_name	= strtolower(str_replace(' ', '-', esc_attr($font_name)));
			$font		= preg_replace('/[^-a-zA-Z0-9+ ]+/i', '', $font);
			$gfont		= str_replace('+', ' ', $font);
			$font_loaded = [];
			if(!isset($googlefonts[$gfont])) continue; //check if font found in our own google fonts list

			$collection = ['normal' => array_unique($this->get_val($_font, 'normal', [])), 'italic' => array_unique($this->get_val($_font, 'italic', []))];

			if(empty($collection['normal']) && !empty($collection['italic'])) $collection['normal'][] = 400;

			$is_first_weight = true;
			$italic	 = false;
			$font	.= ':';
			if(!empty($collection['italic'])){
				$font .= 'ital,';
				$italic = true;
			}
			$font .= 'wght@';

			$i = 0;
			$cycles = ['normal', 'italic'];
			
			foreach($cycles ?? [] as $cycle){
				$weight_values = $collection[$cycle];
				if(empty($weight_values)) continue;

				asort($weight_values); //sort as we need to start from low to high

				foreach($weight_values as $weight){
					if(!$is_first_weight) $font .= ';';

					$font .= ($italic === true) ? $i.','.$weight : $weight;
					$is_first_weight = false;
				}
				$i++;
			}

			foreach($types as $ftype => $options){
				if($load !== $ftype && $all === false) continue;
				$f_download = false;
				foreach($collection as $font_style => $weight){
					if(empty($weight)) continue;
					
					foreach($weight as $w){
						$_css = $this->get_val($fonts_css, [$font_name, $ftype, $w, $font_style], false);

						if(!empty($_css) && is_array($_css)){
							foreach($_css as $uc => $fw){
								if(empty($fw) || !is_array($fw)) continue;
								
								foreach($fw as $_fw => $font_css){
									$start = strpos($font_css, '###BASE###');
									if($start === false) continue;
									$end = strpos($font_css, ')', $start + 10);
									$file_raw = substr($font_css, $start + 10, $end - ($start + 10));

									if(!is_file($base_dir.'/themepunch/gfonts/'. $file_raw) || filemtime($base_dir.'/themepunch/gfonts/'. $file_raw) < $tp_google_ts){
										$f_download = true;
										break;
									}
								}
							}
						}else{
							$f_download = true;
						}

						if($f_download) break;
					}
				}

				if($f_download){
					//one wp_mkdir_p() instead of three bare mkdir() calls: it creates the whole path at once and
					//applies FS_CHMOD_DIR. If the directory cannot be created there is nothing to download into,
					//so skip this font instead of running into a copy() that cannot succeed.
					if(!wp_mkdir_p($base_dir.'/themepunch/gfonts/'.$font_name)) continue;

					$content = wp_safe_remote_get('https://fonts.googleapis.com/css2?family='.$font, $options);
					$body	 = $this->get_val($content, 'body', '');
					$body	 = explode('}', $body);

					if(empty($body)) continue;
				
					foreach($body ?? [] as $b){
						if(preg_match("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $b, $found_fonts)){
							$found_font	= rtrim($found_fonts[0], ')');
							$path     = parse_url($found_font, PHP_URL_PATH);
							$filename = $path ? basename($path) : 'font';
							if(!preg_match('/\.[a-z0-9]{2,5}$/i', $filename)) $filename = md5($found_font) . '.' . $load;

							$file_type	= wp_check_filetype($filename, $allowed_mime_types);
							if($this->get_val($file_type, 'ext', false) === false || $this->get_val($file_type, 'type', false) === false) continue;
							$found_fw	= (preg_match("/(?<=font-weight:)(.*)(?=;)/", $b, $found_fw)) ? trim($found_fw[0]) : '400';
							$found_fs	= (preg_match("/(?<=font-style:)(.*)(?=;)/", $b, $found_fs)) ? trim($found_fs[0]) : 'normal';
							$found_ur	= (preg_match("/(?<=\/\*)(.*)(?=\*\/)/", $b, $found_ur)) ? trim($found_ur[0]) : '';

							$found_ur	= (empty($found_ur)) ? 'all' : $found_ur;
							$found_fs	= ($found_fs !== 'normal') ? 'italic' : $found_fs;
							$found_fw	= (empty($found_fw)) ? '400' : $found_fw;
							$file		= $base_dir.'/themepunch/gfonts/'. $font_name . '/' . $filename;
							$_file		= '###BASE###'. $font_name . '/' . $filename;
							if(!in_array($filename, $font_loaded)){
								$tmp = download_url($found_font, 4);
								if(!is_wp_error($tmp)){
									//the target directory is created once above, so no per-file mkdir() here
									@copy($tmp, $file);
									wp_delete_file($tmp);
								}

								//marked as attempted even when the download failed - $font_loaded only guards
								//against fetching the same file twice, and retrying inside this loop would mean
								//another 4 second timeout per subset on a broken connection
								$font_loaded[] = $filename;
							}

							if(strpos($b, 'font-display') === false) $b .= '  font-display: swap;'."\n";
							
							if(!isset($fonts_css[$font_name]))									$fonts_css[$font_name] = [];
							if(!isset($fonts_css[$font_name][$ftype]))							$fonts_css[$font_name][$ftype] = [];
							if(!isset($fonts_css[$font_name][$ftype][$found_fw]))				$fonts_css[$font_name][$ftype][$found_fw] = [];
							if(!isset($fonts_css[$font_name][$ftype][$found_fw][$found_fs]))	$fonts_css[$font_name][$ftype][$found_fw][$found_fs] = [];
							$fonts_css[$font_name][$ftype][$found_fw][$found_fs][$found_ur]	= str_replace($found_font, $_file, $b . '}');
						}
					}
				}

				foreach($collection as $font_style => $weights){
					if(empty($weights) || !is_array($weights)) continue;
				
					if($style === true)	$ret .= '<style class="sr7-inline-css">';
					$format = ($ftype !== 'ttf') ? $ftype : 'truetype';
					foreach($weights ?? [] as $weight){
						$_css = $this->get_val($fonts_css, [$font_name, $ftype, $weight, $font_style], false);

						if(!empty($_css) && is_array($_css)){
							foreach($_css as $fw => $font_css){
								if(empty($font_css)) continue;
								
								$ret .= str_replace('###BASE###', $base_url.'/themepunch/gfonts/', $font_css);
							}
						}else{
							if(!isset($fonts_css[$font_name]))									$fonts_css[$font_name] = [];
							if(!isset($fonts_css[$font_name][$ftype]))							$fonts_css[$font_name][$ftype] = [];
							if(!isset($fonts_css[$font_name][$ftype][$weight]))					$fonts_css[$font_name][$ftype][$weight] = [];
							if(!isset($fonts_css[$font_name][$ftype][$weight][$font_style]))	$fonts_css[$font_name][$ftype][$weight][$font_style] = [];
							$fonts_css[$font_name][$ftype][$weight][$font_style]['all']	= '/* '.$weight.' '.$font_style.' does not exist  */';
						}
					}
					if($style === true)	$ret .= '</style>';
				}
			}
		}

		$this->update_option(['fonts', 'fonts'], $fonts_css, self::OPTIONS_FONTS);

		return $ret;
	}

	
	/**
	 * get a collection of all used fonts, either in a grid or from the whole plugin
	 * walks every slider's layers; paged because it can be slow on large installations
	 * @return array ['fonts' => collected fonts, 'more' => bool another page is pending]
	 **/
	public function collect_used_fonts($save = true, $fetch_all = true, $page = 1){
		$used_fonts	= $this->get_options(['fonts', 'collected'], [], false, self::OPTIONS_FONTS);
		foreach($used_fonts ?? [] as $handle => $font){
			if(!isset($used_fonts[$handle]['subset'])) $used_fonts[$handle] = ['normal' => [], 'italic' => [], 'subset' => []];
		}
		$more		= false;
		$sr			= new RevSliderSlider();
		$sl			= new RevSliderSlide();

		//get all slider, init them and get subsets and get_used_fonts
		$page = intval($page);
		if($page <= 0) $page = 1;

		$sliders = $sr->get_sliders(false, $page);
		foreach($sliders ?? [] as $slider){
			$gf	= $slider->get_param('fonts', []);
			foreach($gf ?? [] as $handle => $data){
				if(!isset($used_fonts[$handle]) || !isset($used_fonts[$handle]['subset'])) $used_fonts[$handle] = ['normal' => [], 'italic' => [], 'subset' => []]; //we are on the old format if subset does not exist

				foreach($used_fonts[$handle] as $k => $v){
					if(is_string($v)) continue;

					$d = $this->get_val($data, $k, []);
					foreach($d ?? [] as $f => $d){
						if(in_array($f, $used_fonts[$handle][$k])) continue;

						$used_fonts[$handle][$k][] = $f;
					}
				}

			}
		}
		if(count($sliders) >= 50) $more = true;

		foreach($used_fonts ?? [] as $font => $data){
			if(isset($data['font'])) continue;
			$used_fonts[$font]['font'] = $font;
		}

		if($fetch_all === true){
			/*if(class_exists('ThemePunch_Fonts') && method_exists('ThemePunch_Fonts', 'collect_used_fonts')){
				$esg_fonts = new ThemePunch_Fonts();
				$return = $esg_fonts->collect_used_fonts(false, false, $page);
				$fonts = $this->get_val($return, 'fonts', []);
				$_more = $this->get_val($return, 'more', false);
				if($_more === true) $more = true;
				//merge esg and revslider

				foreach($fonts ?? [] as $handle => $urls){
					if(empty($urls) || !is_array($urls)) continue;
					if(!isset($used_fonts[$handle]) ) $used_fonts[$handle] = [];
					if(!in_array($handle, $used_fonts[$handle])) {
						foreach($urls ?? [] as $url){
							if(!in_array($url, $used_fonts[$handle])) $used_fonts[$handle][] = $url;
						}
					}
				}
			}*/
		}

		$used_fonts = apply_filters('punchfonts_collect_fonts_v7', $used_fonts);
		if($save === true) $this->update_option(['fonts', 'collected'], $used_fonts, self::OPTIONS_FONTS);

		return ['fonts' => $used_fonts, 'more' => $more];
	}
	
	
	/**
	 * removes fonts from queue, that are already loaded by WordPress
	 * avoids loading a font twice when the theme already registers it via theme.json
	 * @return void
	 */
	public function remove_wordpress_global_fonts(){
		global $SR_GLOBALS;

		if(!class_exists('WP_Font_Face_Resolver')) return;
		if(!method_exists('WP_Font_Face_Resolver', 'get_fonts_from_theme_json' )) return;
		if(!method_exists('WP_Font_Face_Resolver', 'get_fonts_from_style_variations' )) return;
		
		$wp_font_list = [];
		$wp_fonts = WP_Font_Face_Resolver::get_fonts_from_theme_json();
		if(empty($wp_fonts)) $wp_fonts = WP_Font_Face_Resolver::get_fonts_from_style_variations();
		foreach($wp_fonts ?? [] as $wp_font){
			foreach($wp_font ?? [] as $_font){
				$wp_family = $this->get_val($_font, 'font-family');
				$wp_style = $this->get_val($_font, 'font-style');
				$wp_weight = $this->get_val($_font, 'font-weight');
				if(empty($wp_family)) continue;
				if(empty($wp_style)) continue;
				if(empty($wp_weight)) continue;
				if(!isset($wp_font_list[$wp_family])) $wp_font_list[$wp_family] = [];
				if(!isset($wp_font_list[$wp_family]['variants'])) $wp_font_list[$wp_family]['variants'] = [];
				if(strpos($wp_weight, ' ') !== false){
					$wp_weight = explode(' ', $wp_weight);
					$wp_font_list[$wp_family]['variants'][$wp_style] = [
						'from'	=> $this->get_val($wp_weight, 0),
						'to'	=> $this->get_val($wp_weight, 1)
					];
				}else{
					$wp_font_list[$wp_family]['variants'][$wp_style] = $wp_weight;
				}
			}
		}

		if(!empty($wp_font_list)){
			foreach($SR_GLOBALS['fonts']['queue'] as $font_name => $font_settings){		
				if(empty($font_name)) continue;
				if(isset($font_settings['url']) && !empty($font_settings['url'])) continue; //ignore custom
				if(!isset($wp_font_list[$font_name])) continue;
				$_variants	= $this->get_val($font_settings, 'variants', ['normal' => [], 'italic' => []]);
				foreach($_variants ?? [] as $f_w => $f_v){
					$from	= (isset($wp_font_list[$font_name]['variants'][$f_w]) && is_array($wp_font_list[$font_name]['variants'][$f_w])) ? intval($wp_font_list[$font_name]['variants'][$f_w]['from']) : false;
					$to		= (isset($wp_font_list[$font_name]['variants'][$f_w]) && is_array($wp_font_list[$font_name]['variants'][$f_w])) ? intval($wp_font_list[$font_name]['variants'][$f_w]['to']) : false;
					$exact	= (isset($wp_font_list[$font_name]['variants'][$f_w]) && !is_array($wp_font_list[$font_name]['variants'][$f_w])) ? intval($wp_font_list[$font_name]['variants'][$f_w]) : false;

					foreach($f_v ?? [] as $f_v_id => $f_v_check){
						if($exact !== false){
							if(intval($f_v_check) === $exact) unset($SR_GLOBALS['fonts']['queue'][$font_name]['variants'][$f_w][$f_v_id]);
						}else{
							if(intval($f_v_check) >= $from && intval($f_v_check) <= $to) unset($SR_GLOBALS['fonts']['queue'][$font_name]['variants'][$f_w][$f_v_id]);
						}
					}
				}

				if(
					(!isset($SR_GLOBALS['fonts']['queue'][$font_name]['variants']['normal']) || empty($SR_GLOBALS['fonts']['queue'][$font_name]['variants']['normal'])) && 
					(!isset($SR_GLOBALS['fonts']['queue'][$font_name]['variants']['italic']) || empty($SR_GLOBALS['fonts']['queue'][$font_name]['variants']['italic']))
				){
					unset($SR_GLOBALS['fonts']['queue'][$font_name]);
				}
			}
		}
	}

	/* ---------------------------------------------------------------------
	 * Custom Font Upload (Global Settings > Fonts > Custom Fonts)
	 * Lets users upload font files directly instead of hosting the CSS
	 * themselves. Files live in uploads/revslider/fonts/<slug>/ and a
	 * generated <slug>.css (@font-face) plugs into the existing custom
	 * font pipeline (the row just gets a local "url").
	 * @since: 7.1.0
	 * ------------------------------------------------------------------- */

	/**
	 * allowed uploadable font formats: ext => ['format' => css src format(), 'sig' => [magic byte signatures]]
	 * @return array
	 */
	public function get_custom_font_formats(){
		return [
			'woff2'	=> ['format' => 'woff2',	'sig' => ['wOF2']],
			'woff'	=> ['format' => 'woff',		'sig' => ['wOFF']],
			'ttf'	=> ['format' => 'truetype',	'sig' => ["\x00\x01\x00\x00", 'true', 'ttcf']],
			'otf'	=> ['format' => 'opentype',	'sig' => ['OTTO', "\x00\x01\x00\x00"]]
		];
	}

	/**
	 * base directory + url for uploaded custom fonts (created on demand, with index.php guard)
	 * @return array ['dir' => absolute path, 'url' => public url]
	 */
	public function get_custom_fonts_dir(){
		$upload_dir	= wp_upload_dir();
		$base_dir	= trailingslashit($upload_dir['basedir']) . 'revslider/fonts';
		$base_url	= trailingslashit($upload_dir['baseurl']) . 'revslider/fonts';
		if(!is_dir($base_dir)){
			wp_mkdir_p($base_dir);
			@file_put_contents($base_dir . '/index.php', '<?php // Silence is golden.');
		}
		return ['dir' => $base_dir, 'url' => $base_url];
	}

	/**
	 * slugify a font family name for safe folder / file names
	 * @return string
	 */
	public function get_custom_font_slug($family){
		$slug = sanitize_title($family);
		if(empty($slug)) $slug = 'font-' . substr(md5($family), 0, 8);
		return $slug;
	}

	/**
	 * validate the binary signature of a font file against its extension
	 * (defeats a renamed .php -> .woff2 at the content level)
	 * @return bool
	 */
	public function is_valid_font_file($path, $ext){
		$formats = $this->get_custom_font_formats();
		if(!isset($formats[$ext])) return false;
		$fh = @fopen($path, 'rb');
		if($fh === false) return false;
		$head = fread($fh, 4);
		fclose($fh);
		if($head === false || strlen($head) < 4) return false;
		foreach($formats[$ext]['sig'] as $sig){
			if(strncmp($head, $sig, strlen($sig)) === 0) return true;
		}
		return false;
	}

	/**
	 * guess weight + style from a font file name (e.g. "Roboto-BoldItalic.woff2")
	 * @return array ['weight' => int, 'style' => 'normal'|'italic']
	 */
	public function guess_font_weight_style($filename){
		$name	= strtolower($filename);
		$style	= (preg_match('/italic|oblique/', $name)) ? 'italic' : 'normal';
		$map	= [
			'hairline' => 100, 'thin' => 100,
			'extralight' => 200, 'ultralight' => 200,
			'semibold' => 600, 'demibold' => 600,
			'extrabold' => 800, 'ultrabold' => 800,
			'light' => 300, 'medium' => 500, 'black' => 900, 'heavy' => 900,
			'bold' => 700, 'regular' => 400, 'normal' => 400, 'book' => 400
		];
		$weight = 400;
		foreach($map as $needle => $w){
			if(strpos($name, $needle) !== false){
				$weight = $w;
				break;
			}
		}
		return ['weight' => $weight, 'style' => $style];
	}

	/**
	 * store one uploaded font file ($_FILES['import_file']) for the given family
	 * @return array ['error' => false|string, 'file', 'url', 'ext', 'weight', 'style']
	 */
	public function store_uploaded_font_file($family){
		if(!function_exists('wp_max_upload_size')) require_once(ABSPATH . 'wp-admin/includes/file.php');

		$file = $this->get_val($_FILES, 'import_file');
		if(empty($file)) return ['error' => __('No file sent', 'revslider')];

		switch($this->get_val($file, 'error')){
			case UPLOAD_ERR_NO_FILE:	return ['error' => __('No file sent', 'revslider')];
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:	return ['error' => __('Exceeded filesize limit', 'revslider')];
			case UPLOAD_ERR_OK:			break;
			default:					return ['error' => __('File not found', 'revslider')];
		}

		$tmp = $this->get_val($file, 'tmp_name');
		if(empty($tmp) || !is_uploaded_file($tmp)) return ['error' => __('File not found', 'revslider')];
		if($this->get_val($file, 'size') > wp_max_upload_size()) return ['error' => __('Exceeded filesize limit', 'revslider')];

		$orig_name	= basename($this->get_val($file, 'name'));
		$ext		= strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
		$formats	= $this->get_custom_font_formats();
		if(!isset($formats[$ext])) return ['error' => __('Only WOFF2, WOFF, TTF and OTF font files are allowed', 'revslider')];

		//validate the real file content, not just the extension
		if(!$this->is_valid_font_file($tmp, $ext)) return ['error' => __('The uploaded file is not a valid font file', 'revslider')];

		$slug	= $this->get_custom_font_slug($family);
		$base	= $this->get_custom_fonts_dir();
		$dir	= $base['dir'] . '/' . $slug;
		if(!is_dir($dir)) wp_mkdir_p($dir);

		$filename	= sanitize_file_name($orig_name);
		$check		= wp_check_filetype($filename, ['woff2' => 'font/woff2', 'woff' => 'font/woff', 'ttf' => 'font/ttf', 'otf' => 'font/otf']);
		if($this->get_val($check, 'ext', false) === false) $filename = $slug . '.' . $ext;
		$filename	= wp_unique_filename($dir, $filename);

		if(!move_uploaded_file($tmp, $dir . '/' . $filename)) return ['error' => __('File could not be saved', 'revslider')];
		@chmod($dir . '/' . $filename, defined('FS_CHMOD_FILE') ? FS_CHMOD_FILE : 0644);

		$guess = $this->guess_font_weight_style($filename);

		return [
			'error'		=> false,
			'file'		=> $slug . '/' . $filename,
			'url'		=> $base['url'] . '/' . $slug . '/' . $filename,
			'ext'		=> $ext,
			'weight'	=> $guess['weight'],
			'style'		=> $guess['style']
		];
	}

	/**
	 * (re)build the @font-face css file for one uploaded custom font family
	 * @param string $family
	 * @param array  $files [ ['file' => '<slug>/<file>', 'weight' => 400, 'style' => 'normal'], ... ]
	 * @return string the css file url, or '' on failure
	 */
	public function build_custom_font_css($family, $files){
		if(empty($files) || !is_array($files)) return '';

		$slug	= $this->get_custom_font_slug($family);
		$base	= $this->get_custom_fonts_dir();
		$dir	= $base['dir'] . '/' . $slug;
		if(!is_dir($dir)) wp_mkdir_p($dir);

		$formats	= $this->get_custom_font_formats();
		$family		= trim(str_replace(['"', "'", '<', '>', '\\'], '', $family)); //defensive against css/markup breakout
		$css		= '';
		foreach($files as $f){
			$rel = $this->get_val($f, 'file', '');
			if(empty($rel)) continue;
			$rel	= ltrim(str_replace(['..', '\\'], '', $rel), '/'); //no path traversal
			$fpath	= $base['dir'] . '/' . $rel;
			if(!is_file($fpath)) continue; //only reference files that really exist

			$ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
			$fmt = $this->get_val($formats, [$ext, 'format'], false);
			if($fmt === false) continue;

			$weight	= intval($this->get_val($f, 'weight', 400));
			if($weight < 1) $weight = 400;
			$style	= ($this->get_val($f, 'style', 'normal') === 'italic') ? 'italic' : 'normal';
			$url	= $base['url'] . '/' . $rel;

			$css .= "@font-face{font-family:'" . $family . "';font-style:" . $style . ";font-weight:" . $weight . ";font-display:swap;src:url('" . esc_url_raw($url) . "') format('" . $fmt . "');}\n";
		}

		if(empty($css)) return '';
		if(@file_put_contents($dir . '/' . $slug . '.css', $css) === false) return '';

		return $base['url'] . '/' . $slug . '/' . $slug . '.css';
	}

	/**
	 * remove all stored files of an uploaded custom font family
	 * @return void
	 */
	public function delete_custom_font($family){
		$slug = $this->get_custom_font_slug($family);
		if(empty($slug)) return;
		$base	= $this->get_custom_fonts_dir();
		$dir	= $base['dir'] . '/' . $slug;
		if(!is_dir($dir)) return;
		foreach((array)glob($dir . '/*') as $f){
			if(is_file($f)) @unlink($f);
		}
		@rmdir($dir);
	}

	/**
	 * sync the custom font list coming from a Global Settings save:
	 * - (re)build the @font-face css + fill url/weights for every uploaded font
	 * - delete stored files of uploaded fonts that were removed since the last save
	 * @param array $list     the new fonts.list array
	 * @param array $old_list the previously stored fonts.list array
	 * @return array the adjusted list
	 */
	public function sync_uploaded_fonts($list, $old_list = []){
		if(!is_array($list)) $list = [];

		$kept = [];
		foreach($list as $i => $row){
			if($this->_truefalse($this->get_val($row, 'uploaded', false)) !== true) continue;
			$family	= trim($this->get_val($row, 'family', ''));
			$files	= $this->get_val($row, 'files', []);
			if(empty($family) || empty($files)) continue; //incomplete row, leave url/weights as sent

			$css = $this->build_custom_font_css($family, $files);
			if(!empty($css)){
				$list[$i]['url'] = $css;
				$weights = [];
				foreach($files as $f){
					$w = intval($this->get_val($f, 'weight', 400));
					if($w > 0 && !in_array($w, $weights, true)) $weights[] = $w;
				}
				sort($weights);
				if(!empty($weights)) $list[$i]['weights'] = implode(',', $weights);
			}
			$kept[$this->get_custom_font_slug($family)] = true;
		}

		//prune files of uploaded fonts that no longer exist in the new list
		foreach((array)$old_list as $row){
			if($this->_truefalse($this->get_val($row, 'uploaded', false)) !== true) continue;
			$family = trim($this->get_val($row, 'family', ''));
			if(empty($family)) continue;
			if(isset($kept[$this->get_custom_font_slug($family)])) continue;
			$this->delete_custom_font($family);
		}

		return $list;
	}
}
