<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

if(!class_exists('RSColorpicker')){
	/**
	 * Colour handling for the editor's colour picker.
	 *
	 * A colour value is either a plain CSS colour, or - for gradients - a JSON array of stops that starts
	 * with "[{". process() is the central entry point: it normalizes whatever came from the database into
	 * [css value, type], so callers do not have to know which of the historic formats they were handed.
	 */
	class RSColorpicker {
		
		/**
		 * @since 5.3.1.6
		 */
		public function __construct(){
		}
		
		/**
		 * get a value
		 * the ready-to-use CSS value for any stored colour or gradient
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function get($val){
	        if(!$val || empty($val)) return 'transparent';
	        $process = RSColorpicker::process($val, true);
	        return $process[0];
	    }
		
		/**
		 * parse a color
		 * @param string|false $prop            optional CSS property, e.g. 'background' - wraps the value in "prop: value;"
		 * @param bool         $returnColorType also return the detected type in index 1
		 * @since 5.3.1.6
		 * @return array
		 */
		public static function parse($val, $prop, $returnColorType){
			$val = RSColorpicker::process($val, true);
			$ar = [];
			$ar[0] = (!$prop) ? $val[0] : $prop . ': ' . $val[0] . ';';
			
			if($returnColorType) $ar[1] = $val[1];
			
			return $ar;
		}


		/**
		 * convert a color
		 * applies a legacy separate opacity value to a colour, producing rgba() where needed
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function convert($color, $opacity = '100'){
			if($opacity == 'transparent') return 'rgba(0,0,0,0)';
			if($color == '') return '';
			if(strpos($color, '[{') !== false || strpos($color, 'gradient') !== false) return RSColorpicker::get($color);
			if(!is_bool($opacity) && ''.$opacity === '0') return 'transparent';

			if($opacity == -1 || !$opacity || empty($opacity) || !is_numeric($opacity) || $color == 'transparent' || $opacity === 1 || $opacity === 100){
				if(strpos($color, 'rgba') === false && strpos($color, '#') !== false){
					return RSColorpicker::processRgba(RSColorpicker::sanitizeHex($color), $opacity);
				}else{
					$color = RSColorpicker::process($color, true);
					return $color[0];
				}
			}

			$opacity = floatval($opacity);
			if($opacity < 1) $opacity = $opacity * 100;
			$opacity = round($opacity);
			$opacity = ($opacity > 100) ? 100 : $opacity;
			$opacity = ($opacity < -1) ? 0 : $opacity;

			if($opacity === 0) return 'transparent';

			if(strpos($color, '#') !== false){
				return RSColorpicker::processRgba(RSColorpicker::sanitizeHex($color), $opacity);
			}else{
				$color = RSColorpicker::rgbValues($color, 3);
				return RSColorpicker::rgbaString($color[0], $color[1], $color[2], $opacity);
			}
		}


		/**
		 * process color
		 * the central normalizer: takes any stored colour (plain, gradient JSON, gradient CSS) and returns
		 * [css value, type] - plus the decoded gradient in index 2 for gradients
		 * @param bool $processColor also sanitize the gradient stops
		 * @since 5.3.1.6
		 * @return array
		 */
		public static function process($clr, $processColor = false){
			if(!is_string($clr)){
				if($processColor) $clr = RSColorpicker::sanatizeGradient($clr);
				return [RSColorpicker::processGradient($clr), 'gradient', $clr];
			}elseif(trim($clr) == 'transparent'){
				return ['transparent', 'transparent'];
			}elseif(strpos($clr, '[{') !== false){
				try{
					$clr = json_decode(str_replace("amp;", '',str_replace("&", '"', $clr)), true);

					if($processColor) $clr = RSColorpicker::sanatizeGradient($clr);

					return [RSColorpicker::processGradient($clr), 'gradient', $clr];
				}catch(Exception $e){
					return [
						'linear-gradient(0deg, rgb(255, 255, 255) 0%, rgb(0, 0, 0) 100%)', 
						'gradient',
						[
							'type' => 'linear',
							'angle' => '0',
							'colors' => [
								[
									'r' => '255',
									'g' => '255',
									'b' => '255',
									'a' => '1',
									'position' => '0',
									'align' => 'bottom'
								],
								[
									'r' => '0',
									'g' => '0',
									'b' => '0',
									'a' => '1',
									'position' => '100',
									'align' => 'bottom'
								]
							]
						]
					];
				}
			}elseif(strpos($clr, '-gradient') !== false){
				// gradient was not stored as a JSON string for some reason and needs to be converted
				$reversed = RSColorpicker::reverseGradient($clr);
				return [RSColorpicker::processGradient($reversed), 'gradient_css', $reversed];
				
			}elseif(strpos($clr,'#') !== false){
				return [RSColorpicker::sanitizeHex($clr), 'hex'];
			}elseif(strpos($clr,'rgba') !== false){
				$clr = preg_replace('/\s+/', '', $clr);
				
				// fixes 'rgba(0,0,0,)' issue
				preg_match('/,\)/', $clr, $matches);
				if(!empty($matches)) {
					$clr = explode(',)', $clr);
					$clr = $clr[0] . ',1)';
				}
				
				return [$clr, 'rgba'];
			}else{
				$clr = preg_replace('/\s+/', '', $clr);
				return [$clr, 'rgb'];
			}
		}

		/**
		 * sanitize a gradient
		 * casts every stop to int/float and drops stops identical to their predecessor
		 * @since 5.3.1.6
		 * @return array
		 */
		public static function sanatizeGradient($obj){
			$colors = $obj['colors'];
			$len = count($colors);
			$ar = [];

			for($i = 0; $i < $len; $i++){
				$cur = $colors[$i];
				unset($cur['align']);
				
				if(is_bool($cur['a'])) $cur['a'] = $cur['a'] ? 1 : 0;
				$cur['a'] = RSColorpicker::sanitizeAlpha($cur['a']);
				
				$cur['r'] = intval($cur['r']);
				$cur['g'] = intval($cur['g']);
				$cur['b'] = intval($cur['b']);
				$cur['position'] = intval($cur['position']);
				
				if(isset($prev)){
					if(json_encode($cur) !== json_encode($prev)){
						$ar[] = $cur;
					}
				}else{
					$ar[] = $cur;
				}
				$prev = $cur;
			}
			
			$obj['colors'] = $ar;
			
			return $obj;
		}
		
		/**
		 * cleans up the alpha value for comparison operations
		 * clamped to 0..1 and stripped of trailing zeros, so 0.50 and .5 compare equal
		 * @since 6.0
		 * @return float
		 */
		 public static function sanitizeAlpha($alpha){
			$alpha = floatval($alpha);
			$alpha = min($alpha, 1);
			$alpha = max($alpha, 0);
			$alpha = number_format($alpha, 2, '.', '');
			$alpha = preg_replace('/\.?0*$/', '', $alpha);
			
			return floatval($alpha);
		}
		
		/**
		 * accounting for cases where gradient doesn't exist as a JSON Object from previous templates for some reason
		 * parses a CSS gradient string back into the stop array format
		 * @since 6.0
		 * @return array|string the stop array, or the input unchanged when it cannot be parsed
		 */
		public static function reverseGradient($str){
			// hsl colors not supported yet
			if(strpos($str, 'hsl') !== false) return $str;
			
			$str = str_replace('/\-moz\-|\-webkit\-/', '', $str);
			$str = str_replace('to left', '90deg', $str);
			$str = str_replace('to bottom', '180deg', $str);
			$str = str_replace('to top', '0deg', $str);
			$str = str_replace('to right', '270deg', $str);
			$str = str_replace(';', '', $str);
			
			$gradient = explode('-gradient(', $str);
			if(count($gradient) < 2) return $str;
			
			$grad = trim($gradient[1]);
			$degree = '0';
			
			if(strpos($grad, 'ellipse at center') === false){
				if(strpos($grad, 'deg') !== false){
					$grad = explode('deg', $grad);
					$degree = trim($grad[0]);
					$grad = trim($grad[1]);
				}
			}else{
				$grad = str_replace('ellipse at center', '', $grad);
			}
			
			if($grad[0] === ',') $grad = ltrim($grad, ',');
			if($grad[strlen($grad) - 1] === ',') $grad = rtrim($grad, ',');
			
			$colors = explode('%', $grad);
			$list = [];
			
			array_pop($colors);
			$prev = false;
			
			foreach($colors as $clr) {
				
				$clr = trim($clr);
				$perc = '';
				
				if($clr[0] === ',') $clr = ltrim($clr, ',');
				if(strpos($clr, ' ') === false) return $str;
				
				$perc = explode(' ', $clr);
				$perc = $perc[count($perc) - 1];
				
				$leg = strlen($clr);
				$index = 0;
				
				while($leg--){
					$index = $leg;
					if($clr[$leg] === ' ') break;
				}
				
				$clr = substr($clr, 0, $index);
				preg_match('/\)/', $clr, $matches);
				
				if(!empty($matches)) {
					$clr = explode(')', $clr);
					$clr = trim($clr[0]) . ')';
				}else{
					$clr = explode(' ', $clr);
					$clr = trim($clr[0]);
				}
				
				$tpe = RSColorpicker::process($clr, false);
				if($tpe[1] === 'hex'){
					$clr = RSColorpicker::sanitizeHex($clr);
					$clr = RSColorpicker::processRgba($clr);
				}
				
				if($prev && $prev === $clr) continue;
				$prev = $clr;
				
				$clr = RSColorpicker::rgbValues($clr, 4);
				$list[] = ['r' => $clr[0], 'g' => $clr[1], 'b' => $clr[2], 'a' => $clr[3], 'position' => $perc, 'align' => 'top'];
			
			}
			
			return ['type' => trim($gradient[0]), 'angle' => $degree, 'colors' => $list];
		
		}
		
		/**
		 * create the gradient
		 * inserts interpolated stops so the gradient follows the configured easing curve
		 * @since 6.0
		 * @return void $gradient is modified in place
		 */
		 public static function easeGradient(&$gradient){
			include_once(RS_PLUGIN_PATH . 'includes/coloreasing.class.php');
			if(class_exists('RSColorEasing')){
				$strength = (intval($gradient['strength']) * 0.01) * 15;
				$easing = $gradient['easing'];
				$points = $gradient['colors'];
				
				$len = count($points) - 1;
				$ar = [];

				for($i = 0; $i < $len; $i++){
					$ar[] = $points[$i];
					RSColorEasing::insertPoints($points[$i], $points[$i + 1], $ar, $easing, $strength);
				}

				$ar[] = $points[$len];
				$gradient['colors'] = $ar;
			}
		 }

		/**
		 * create the gradient
		 * renders the stop array as a CSS linear/radial-gradient()
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function processGradient($obj){
			if(!is_array($obj)) return 'transparent';
			if(array_key_exists('easing', $obj) && $obj['easing'] !== 'none') {
				RSColorpicker::easeGradient($obj);
			}
			
			$tpe = $obj['type'];
			$begin = $tpe . '-gradient(';
			
			if($tpe === 'linear'){
				$angle = intval($obj['angle']);
				$middle = $angle !== 180 ? $angle . 'deg, ' : '';
			}else{
				$middle = 'ellipse at center, ';
			}

			$colors = $obj['colors'];
			$end = '';
			$i = 0;
			
			foreach($colors as $clr){
				if($i > 0) $end .= ', ';
				$end .= 'rgba(' . $clr['r'] . ',' . $clr['g'] . ',' . $clr['b'] . ',' . $clr['a'] . ') ' . $clr['position'] . '%';
				$i++;
			}
			
			return $begin . $middle . $end . ')';
			
		}


		/**
		 * get rgb values
		 * @param string $values an "rgb(...)" / "rgba(...)" string
		 * @param int    $num    how many components to return (3 or 4)
		 * @since 5.3.1.6
		 * @return array|string the components, or the input unchanged when it is not a rgb/rgba string
		 */
		public static function rgbValues($values, $num){
			if(empty($values)) return $values;
			if(strpos($values, '(') === false) return $values;
			if(strpos($values, ')') === false) return $values;
			$values = substr($values, strpos($values, '(') + 1, strpos($values, ')') - strpos($values, '(') - 1);
			$values = explode(',', $values);
			
			if(count($values) == 3 && $num == 4) $values[3] = '1';
			for($i = 0; $i < $num; $i++){
				if(isset($values[$i])) $values[$i] = trim($values[$i]);
			}
			
			if(count($values) < $num){
				$v = count($values)-1;
				for($i = $v; $i < $num; $i++){
					$values[$i] = $values[0];
				}
			}
			
			return $values;
		}

		/**
		 * get an rgba string
		 * an alpha above 1 is treated as a percentage
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function rgbaString($r, $g, $b, $a){
			if($a > 1){
				$a = ''.number_format($a * 0.01, 2, '.', '');
	      		$a = str_replace('.00', '', $a);
	      	}
			return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $a . ')';
		}

		/**
		 * change rgb to hex
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function rgbToHex($clr){
			$values = RSColorpicker::rgbValues($clr, 3);
			return RSColorpicker::getRgbToHex($values[0], $values[1], $values[2]);
		}

		/**
		 * change rgba to hex
		 * the alpha channel is dropped
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function rgbaToHex($clr){
			$values = RSColorpicker::rgbValues($clr, 4);
			return RSColorpicker::getRgbToHex($values[0], $values[1], $values[2]);
		}

		/**
		 * get opacity
		 * @since 5.3.1.6
		 */
		public static function getOpacity($val){
			$rgb = RSColorpicker::rgbValues($val, 4);
			return intval($rgb[3] * 100, 10) + '%';
		}

		/**
		 * change rgb to hex
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function getRgbToHex($r, $g, $b){
			$rgb = [$r, $g, $b];
			$hex = "#";
	   		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
	   		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
	   		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
	   		return $hex;
		}

		/**
		 * join it together to be rgba
		 * splits the legacy "color||opacity" storage format and converts it
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function joinToRgba($val){
			$val = explode('||', $val);
			return RSColorpicker::convert($val[0], $val[1]);
		}
		
		/**
		 * rgb to rgba
		 * @since 6.0
		 * @return string
		 */
		public static function rgbToRgba($val){
			$val = RSColorpicker::rgbValues($val, 4);
			return RSColorpicker::rgbaString($val[0], $val[1], $val[2], $val[3]);
		}
		
		/**
		 * shorten #aabbcc to #abc where possible
		 * @since 6.0
		 * @return string
		 */
		public static function trimHex($color){
			$color = trim($color);
			if(strlen($color) !== 7) return $color;
			
			$clr = str_replace('#', '', $color);
			$char = $clr[0];
			
			for($i = 1; $i < 6; $i++) {
				if($clr[$i] !== $char) return $color;
				$char = $clr[$i];
			}
			
			return '#' . substr($clr, 0, 3);
		}
		
		/**
		* the legacy opacity to rgba conversions and also checks for gradients
		* leaves gradients and non-strings untouched
		* @since: 6.0
		* @return mixed
		*/
		public function correctValue($color, $opacity = false) {
			if(!is_string($color)) return $color; // unknown value
			
			// gradients can exist as a JSON string or a CSS string
			// when they exist as a CSS string it is a result of a bug from 5.0 
			if(strpos($color, '[{') === false && strpos($color, 'gradient') === false) {
				if($opacity === false) return $color; // normal color
				return RSColorpicker::convert($color, $opacity); // legacy conversion
			}
			
			return $color; // gradient
		}
		
		/**
		 * useful when you need to compare two values and also for smallest print size
		 * for example, this function will convert both"
		 * "rgba(255,255, 255,1)" and "#FFFFFF" to "#FFF"
		 * @since: 6.0
		 * @return string
		 */  
		public static function normalizeColor($color) {
			if(empty(trim($color))) return $color;
			
			$color = RSColorpicker::process($color, true);
			$clr = $color[0];
			$tpe = $color[1];
			$processed = true;
			
			if($tpe === 'hex'){
				$clr = RSColorpicker::sanitizeHex($clr);
				$clr = RSColorpicker::processRgba($clr, true);
				$processed = true;
			}elseif($tpe === 'rgb'){
				$clr = RSColorpicker::rgbToRgba($clr);
			}elseif($tpe === 'rgba'){
				$clr = preg_replace('/\s+/', '', $clr);
			}else{
				$processed = false;
			}
			
			if($processed) $clr = RSColorpicker::sanitizeRgba($clr);
			
			return $clr;
		}
		
		/**
		 * normalize colors for comparison
		 * accepts a single colour or a list and normalizes each
		 * @since: 6.0
		 * @return mixed
		 */  
		public static function normalizeColors($color){
			if(is_object($color)) $color = (array)$color;
			if(is_array($color)) {
				$total = count($color);
				for($i = 0; $i < $total; $i++) $color[$i] = RSColorpicker::normalizeColor($color[$i]);
			}else{
				$color = RSColorpicker::normalizeColor($color);
			}
			
			return $color;
		}
		
		/**
		 * convert rgba with 100% opacity to hex
		 * a fully opaque rgba() is stored as hex instead
		 * @since 6.0
		 * @return string
		 */
		public static function sanitizeRgba($color, $opacity = false){
			if($opacity){
				$color = RSColorpicker::rgbaToHex($color);
				$color = RSColorpicker::trimHex($color);
			}else{
				$opacity = RSColorpicker::rgbValues($color, 4);
				if($opacity[3] === '1') {
					$color = RSColorpicker::rgbaToHex($color);
					$color = RSColorpicker::trimHex($color);
				}
			}
			
			return $color;
		}

		/**
		 * process rgba
		 * hex to "rgb(r,g,b)", or "rgba(r,g,b,a)" when an opacity is given
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function processRgba($hex, $opacity = false){
			$hex = trim(str_replace('#', '' , $hex));
			$rgb = $opacity!==false ? 'rgba' : 'rgb';
			$r = @hexdec(substr($hex,0,2));
	      	$g = @hexdec(substr($hex,2,2));
	      	$b = @hexdec(substr($hex,4,2));
	      	
	      	$color = $rgb . "(" . $r . "," . $g . "," . $b ;

	      	if($opacity!==false){
	      		if($opacity > 1) $opacity = ''.number_format($opacity * 0.01 ,  2, '.', '');
				
	      		$opacity = str_replace('.00', '', $opacity);
	      		$color .= ',' . $opacity;
	      	}

	      	$color .= ')';

	      	return $color;
		}

		/**
		 * sanitize hex
		 * expands the 3 digit shorthand and puts the # back
		 * @since 5.3.1.6
		 * @return string
		 */
		public static function sanitizeHex($hex){
			$hex = trim(str_replace('#', '' , $hex));
			if(strlen($hex) == 3){
			    $hex[5] = $hex[2]; // f60##0
			    $hex[4] = $hex[2]; // f60#00
			    $hex[3] = $hex[1]; // f60600
			    $hex[2] = $hex[1]; // f66600
			    $hex[1] = $hex[0]; // ff6600
			}
			
			return '#'.$hex;
		}
		
		/**
		 * Save presets
		 * the colour swatches the user saved in the editor
		 * @since 5.3.2
		 * @return array the stored presets
		 */
		public static function save_color_presets($presets){
			$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
			$f->update_option(['presets', 'colorpicker'], $presets);
			
			return self::get_color_presets();
		}
		
		
		/**
		 * Load presets
		 * @since 5.3.2
		 * @return array
		 */
		public static function get_color_presets(){
			$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
			
			return $f->get_options(['presets', 'colorpicker'], []);
		}
		
	}
}