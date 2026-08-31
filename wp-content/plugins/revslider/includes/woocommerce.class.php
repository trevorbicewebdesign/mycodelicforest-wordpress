<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * WooCommerce integration for product sliders.
 *
 * Two jobs: building the meta_query that selects products (stock, visibility, price range) and exposing a
 * product's data as %wc_*% layer placeholders. Everything is static and guarded by woo_exists(), so the
 * class is inert without WooCommerce.
 */
class RevSliderWooCommerce extends RevSliderFunctions {
	
	const META_SKU	 = '_sku'; //can be 'instock' or 'outofstock'
	const META_STOCK = '_stock'; //can be 'instock' or 'outofstock'
	
	/**
	 * return true / false if the woo commerce exists
	 * @before RevSliderWooCommerce::isWooCommerceExists();
	 * @return bool
	 */
	public static function woo_exists(){
		return (class_exists('Woocommerce')) ? true : false;
	}
	
	
	/**
	 * compare wc current version to given version
	 * the class branches on version_check('3.0') a lot: WC 3.0 replaced direct property access with getters
	 * @return bool true when the installed WooCommerce is at least $version
	 */
	public static function version_check($version = '1.0') {
		if(self::woo_exists()){
			global $woocommerce;
			if(version_compare($woocommerce->version, $version, '>=')){
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * get wc post types
	 * @return array post types a product slider may pull from
	 */
	public static function getCustomPostTypes(){
		$arr = [
			'product'			=> __('Product', 'revslider'),
			'product_variation'	=> __('Product Variation', 'revslider')
		];
		
		return $arr;
	}
	
	
	/**
	 * get price query
	 * @return array one meta_query clause matching prices between $from and $to (inclusive)
	 */
	private static function get_price_query($from, $to, $meta_tag){
		$from	= (empty($from)) ? 0 : $from;
		$to		= (empty($to)) ? 9999999999 : $to;
		$query	= [
			'key'		=> $meta_tag,
			'value'		=> [$from, $to],
			'type'		=> 'numeric',
			'compare'	=> 'BETWEEN'
		];
		
		return $query;
	}
	
	
	/**
	 * check if in pricerange
	 * @param mixed $check a single price or the array get_post_meta() returns for variable products
	 * @return bool true when at least one of the prices falls inside the range
	 */
	private static function check_price_range($from, $to, $check){
		$from	= (empty($from)) ? 0 : (float)$from;
		$to		= (empty($to)) ? 9999999999 : (float)$to;

		//meta values from get_post_meta() without a key are always arrays and can hold multiple prices (i.e. _price of variable products)
		if(!is_array($check)) $check = [$check];
		foreach($check as $price){
			if($price === '' || $price === null || $price === false) continue;
			$price = (float)$price;
			if($price > $from && $price < $to) return true;
		}

		return false;
	}
	
	
	/**
	 * get meta query for filtering woocommerce posts.
	 * @6.5.23: removed _regular_price and _sale_price here, will be later checked under filter_products_by_price() to add the children
	 * @return array meta_query clauses for stock status, visibility, featured and price range
	 */
	public static function get_meta_query($args){
		$f			= RevSliderGlobals::instance()->get('RevSliderFunctions');
		$query		= [];
		$meta_query	= [];
		$tax_query	= [];
		
		if($f->get_val($args, ['source', 'woo', 'inStockOnly']) == true){
			$meta_query[] = [
				'key' => '_stock_status',
				'value' => 'instock',
				'compare' => '='
			];
		}
		
		if($f->get_val($args, ['source', 'woo', 'featuredOnly']) == true){
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			];
		}

		$tax_query['relation'] = 'AND';
		$tax_query[] = [
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'exclude-from-catalog',
			'operator' => 'NOT IN',
		];
		
		if(!empty($meta_query))	$query['meta_query'] = $meta_query;
		if(!empty($tax_query))	$query['tax_query'] = $tax_query;
		
		return $query;
	}


	/**
	 * filter posts by sales prices, also check for child products
	 * runs after the query because a variable product's price lives on its variations, not on the parent
	 * @since: 6.5.23
	 * @return array the posts that survive the price filter
	 */
	public static function filter_products_by_price($posts, $args){
		if(empty($posts)) return $posts;

		$f					= RevSliderGlobals::instance()->get('RevSliderFunctions');
		$is_30				= RevSliderWooCommerce::version_check('3.0');
		$reg_price_from		= $f->get_val($args, ['source', 'woo', 'regPriceFrom']);
		$reg_price_to		= $f->get_val($args, ['source', 'woo', 'regPriceTo']);
		$sale_price_from	= $f->get_val($args, ['source', 'woo', 'salePriceFrom']);
		$sale_price_to		= $f->get_val($args, ['source', 'woo', 'salePriceTo']);
		$post_types			= $f->get_val($args, ['source', 'woo', 'types'], 'any');

		$meta_query = [];
		//get regular price array
		if(!empty($reg_price_from) || !empty($reg_price_to)){
			$meta_query[] = self::get_price_query($reg_price_from, $reg_price_to, '_regular_price');
		}
		
		//get sale price array
		if(!empty($sale_price_from) || !empty($sale_price_to)){
			$meta_query[] = self::get_price_query($sale_price_from, $sale_price_to, '_sale_price');
		}

		$_good_posts = [];
		foreach($posts as $key => $post){
			$product_id = $f->get_val($post, 'ID'); // ID of parent product
			$product    = ($is_30) ? wc_get_product($product_id) : get_product($product_id);

			if($product === false){
				$_good_posts[] = $post;
				unset($posts[$key]);
				continue;
			}
			
			//check if current post is okay with _regular_price and _sale_price
			if(!empty($reg_price_from) || !empty($reg_price_to) || !empty($sale_price_from) || !empty($sale_price_to)){
				$meta			= get_post_meta($product_id);
				$in_reg_range	= false;
				$in_sale_range	= false;
				if(!empty($reg_price_from) || !empty($reg_price_to)){
					$in_reg_range	= self::check_price_range($reg_price_from, $reg_price_to, $f->get_val($meta, '_regular_price', $f->get_val($meta, '_price')));
				}
				if(!empty($sale_price_from) || !empty($sale_price_to)){
					$in_sale_range	= self::check_price_range($sale_price_from, $sale_price_to, $f->get_val($meta, '_sale_price'));
				}

				if($in_reg_range || $in_sale_range){
					$_good_posts[] = $post;
					continue;
				}else{
					unset($posts[$key]);
				}
			}
			
			if(!empty($meta_query)){
				$my_posts	= new WP_Query(
					[
						'post_parent'	=> $product_id, // ID of a page, post, or custom type
						'post_type'		=> $post_types,
						'meta_query'	=> $meta_query,
						'no_found_rows'	=> true //only ->posts is read below, so skip the SQL_CALC_FOUND_ROWS count
					]
				);
				$_posts		= $my_posts->posts;
				if(!empty($_posts)){
					foreach($_posts as $child_post){
						$_good_posts[] = $child_post;
					}
				}
			}else{
				$_good_posts[] = $post;
			}
		}

		return $_good_posts;
	}
	
	
	/**
	 * get sortby function including standart wp sortby array
	 * @return array the extra sort options a product slider offers, key => label
	 */
	public static function getArrSortBy(){
		
		$sort_by = [
			'meta_num__regular_price'	=> __('Regular Price', 'revslider'),
			'meta_num__sale_price'		=> __('Sale Price', 'revslider'),
			'meta_num_total_sales'		=> __('Number Of Sales', 'revslider'),
			'meta_num__wc_average_rating' => __('Rating', 'revslider'),
			//'meta__featured'			=> __('Featured Products', 'revslider'),
			'meta__sku'					=> __('SKU', 'revslider'),
			'meta_num_stock'			=> __('Stock Quantity', 'revslider')
		];
		
		return $sort_by;
	}

	/**
	 * since WooCommerce 3.0 this function is deprecated as it could lead to performance issues
	 * this is a 1to1 copy of the named function without the deprecation message
	 * @return int|float parent stock plus the stock of every child that manages its own
	 **/
	public static function get_total_stock($product){
		if ( sizeof( $product->get_children() ) > 0 ) {
			$total_stock = max( 0, $product->get_stock_quantity() );

			foreach ( $product->get_children() as $child_id ) {
				if ( 'yes' === get_post_meta( $child_id, '_manage_stock', true ) ) {
					$stock = get_post_meta( $child_id, '_stock', true );
					$total_stock += max( 0, wc_stock_amount( $stock ) );
				}
			}
		} else {
			$total_stock = $product->get_stock_quantity();
		}
		
		return wc_stock_amount( $total_stock );
	}

	/**
	 * all %wc_*% placeholder values for one product
	 * @param int    $post_id product id
	 * @param string $text    unused; kept for older callers
	 * @return array|false placeholder name => value, false when the id is not a product
	 */
	public static function get_wc_data($post_id, $text = ''){
		//memoize per request: the same product is resolved for every layer of a slide
		static $wc_cache = [];
		if(!isset($wc_cache[$post_id])) $wc_cache[$post_id] = self::_get_wc_data($post_id);
		return $wc_cache[$post_id];
	}

	/**
	 * Build loop-style add-to-cart markup (incl. ajax_add_to_cart) and enqueue WC's script.
	 * Always built for stream data so {{wc_add_to_cart_button}} resolves client-side.
	 */
	private static function get_add_to_cart_button($product, $is_30 = true){
		$pr_id		= ($is_30) ? $product->get_id() : $product->id;
		$pr_type	= ($is_30) ? $product->get_type() : $product->product_type;
		$purchasable = $product->is_purchasable() && $product->is_in_stock();
		$supports_ajax = $purchasable && $product->supports('ajax_add_to_cart');

		// Use WC's registered handle so wc_add_to_cart_params (incl. wc_ajax_url) is localized correctly
		if($supports_ajax){
			wp_enqueue_script('wc-add-to-cart');
			wp_enqueue_script('wc-cart-fragments');
		}

		$classes = implode(' ', array_filter([
			'button',
			'product_type_' . $pr_type,
			$purchasable ? 'add_to_cart_button' : '',
			$supports_ajax ? 'ajax_add_to_cart' : '',
		]));

		$attrs = [
			'data-quantity'		=> '1',
			'data-product_id'	=> $pr_id,
			'data-product_sku'	=> $product->get_sku(),
			'aria-label'		=> $product->add_to_cart_description(),
			'rel'				=> 'nofollow',
		];
		if($supports_ajax) $attrs['role'] = 'button';

		$attr_html = '';
		foreach($attrs as $name => $value){
			$attr_html .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
		}

		return apply_filters(
			'woocommerce_loop_add_to_cart_link',
			sprintf(
				'<a href="%s" class="%s"%s>%s</a>',
				esc_url($product->add_to_cart_url()),
				esc_attr($classes),
				$attr_html,
				esc_html($product->add_to_cart_text())
			),
			$product,
			[
				'quantity'		=> 1,
				'class'			=> $classes,
				'attributes'	=> $attrs,
			]
		);
	}

	/**
	 * uncached worker behind get_wc_data()
	 * @return array|false
	 */
	private static function _get_wc_data($post_id){
		$is_30 = RevSliderWooCommerce::version_check('3.0');
		$product = ($is_30) ? wc_get_product($post_id) : get_product($post_id);

		if($product === false) return false;

		$wc_stock		= ($is_30) ? RevSliderWooCommerce::get_total_stock($product) : $product->get_total_stock();
		$wc_rating		= ($is_30) ? wc_get_rating_html($product->get_average_rating()) : $product->get_rating_html();
		$wc_categories	= ($is_30) ? wc_get_product_category_list($product->get_id(), ',') : $product->get_categories(',');
		$wc_tags		= ($is_30) ? wc_get_product_tag_list($product->get_id()) : $product->get_tags();
		// WC returns false for empty term lists and null when stock is unmanaged — never ship those into stream/meta (front would print "false" / leave {{…}} unresolved)
		if($wc_categories === false || $wc_categories === null) $wc_categories = '';
		if($wc_tags === false || $wc_tags === null) $wc_tags = '';
		$wc_stock_quantity = $product->get_stock_quantity();
		if($wc_stock_quantity === false || $wc_stock_quantity === null) $wc_stock_quantity = '';
		$wc_star_rating = '<div class="sr-starring">';
		preg_match_all('#<strong class="rating">.*?</span>#', $wc_rating, $match);
		if(!empty($match) && isset($match[0]) && isset($match[0][0])){
			$wc_star_rating .= str_replace($match[0][0], '', $wc_rating);
			$wc_star_rating = str_replace("Rated ","",$wc_star_rating);
		}
		$wc_star_rating .= '</div>';

		return [
			'wc_full_price'		=> $product->get_price_html(),
			'wc_price'			=> wc_price($product->get_price()),
			'wc_price_no_cur'	=> $product->get_price(),
			'wc_stock'			=> $wc_stock,
			'wc_rating'			=> $wc_rating,
			'wc_star_rating'	=> $wc_star_rating,
			'wc_categories'		=> $wc_categories,
			'wc_add_to_cart'	=> $product->add_to_cart_url(),
			'wc_add_to_cart_button'	=> self::get_add_to_cart_button($product, $is_30),
			'wc_sku'			=> $product->get_sku(),
			'wc_stock_quantity'	=> $wc_stock_quantity,
			'wc_rating_count'	=> $product->get_rating_count(),
			'wc_review_count'	=> $product->get_review_count(),
			'wc_tags'			=> $wc_tags,
		];
	}

	/**
	 * modify layer text, to replace all meta
	 * replaces %wc_price%, {{wc_stock}} and friends in one layer's text
	 * @return string
	 */
	public static function add_wc_layer($text, $post_id, $slide){
		if(RevSliderWooCommerce::woo_exists() === false) return $text;
		//skip building the (expensive) product data when the layer has no wc_ placeholder
		if(strpos($text, 'wc_') === false) return $text;

		$data = RevSliderWooCommerce::get_wc_data($post_id, $text);
		if($data === false) return $text;
		
		foreach($data ?? [] as $tag => $value){
			//false/null only — keep 0 stock quantities (empty() would wipe those)
			if($value === false || $value === null) $value = '';
			$text = str_replace(['%'.$tag.'%', '{{'.$tag.'}}'], $value, $text);
		}
		
		return $text;
	}

	/**
	 * sr_streamline_post_data_post filter: merge the product fields into each post so layers and stream
	 * data can use them. Also fills an empty excerpt from the content.
	 * @return array
	 */
	public static function add_wc_layer_v7($post_data, $data, $metas, $slider){
		if(RevSliderWooCommerce::woo_exists() === false) return $post_data;
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');

		foreach($post_data ?? [] as $key => $post){
			$content = $f->get_val($post, ['content', 'content']);
			$data = RevSliderWooCommerce::get_wc_data($f->get_val($post, 'id'));
			if($data === false) continue;

			//modify excerpt if empty to be filled with content
			if(!isset($post['excerpt']) || trim($post['excerpt']) === ''){
				$post['excerpt'] = str_replace(['<br/>', '<br />'], '', strip_tags($content, '<b><br><i><strong><small>'));
			}

			$post_data[$key] = array_merge($post, $data ?? []);
		}

		return $post_data;
	}
	
}	//end of the class

add_filter('sr_modify_layer_text', ['RevSliderWooCommerce', 'add_wc_layer'], 10, 3);
add_filter('sr_streamline_post_data_post', ['RevSliderWooCommerce', 'add_wc_layer_v7'], 10, 4);