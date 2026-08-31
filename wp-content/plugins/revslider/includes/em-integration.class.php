<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Events Manager integration.
 *
 * Adds a date filter ("today", "this month", …) to post-based sliders whose posts are EM events, and
 * exposes the event's date/location fields as %event_*% layer placeholders. Every entry point checks
 * isEventsExists() first, so the class is inert without the Events Manager plugin.
 */
class RevSliderEventsManager extends RevSliderFunctions {
	public function __construct(){
		$this->init_em();
	}

	/**
	 * @return void
	 */
	public function init_em(){
		add_filter('revslider_get_posts_by_category', [$this, 'add_post_query'], 10, 2);
	}

	/**
	 * check if events class exists
	 * @return bool
	 */
	public static function isEventsExists(){
		return defined('EM_VERSION') && defined('EM_PRO_MIN_VERSION');
	}

	/**
	 * get sort by list
	 * the date filters offered in the editor, filter key => label
	 * @return array
	 */
	public static function get_filter_types(){
		return [
			'none'		=> __('All Events', 'revslider'),
			'today'		=> __('Today', 'revslider'),
			'tomorrow'	=> __('Tomorrow', 'revslider'),
			'future'	=> __('Future', 'revslider'),
			'past'		=> __('Past', 'revslider'),
			'month'		=> __('This Month', 'revslider'),
			'nextmonth'	=> __('Next Month', 'revslider')
		];
	}
	
	
	/**
	 * get meta query
	 * translates a filter key from get_filter_types() into WP_Query meta_query/orderby arguments against
	 * Events Manager's _start_ts / _end_ts meta
	 * @param string $filter_type one of the get_filter_types() keys
	 * @param string $sort_by     'event_start_date' | 'event_end_date' | anything else (ignored)
	 * @return array query arguments to merge into the post query
	 * @throws Exception on an unknown filter key
	 */
	public static function get_query($filter_type, $sort_by){
		$response			= [];
		$dayMs				= DAY_IN_SECONDS;
		//current_time('timestamp') is a site-local *shifted* stamp (UTC + offset), and the day/month boundaries
		//below have to be calculated in that same shifted space to line up with Events Manager's _start_ts /
		//_end_ts meta. the previous date()/strtotime() round-trip did that correctly only as long as PHP's
		//default timezone happened to be UTC (which WP sets, but any plugin may change). gmdate()/gmmktime()
		//pin the calculation to UTC explicitly, so it no longer depends on ambient state.
		$time				= current_time('timestamp');
		$month				= (int)gmdate('n', $time);
		$year				= (int)gmdate('Y', $time);
		$todayStart			= $time - ($time % $dayMs);
		$todayEnd			= $todayStart + $dayMs-1;
		$tomorrowStart		= $todayEnd+1;
		$tomorrowEnd		= $tomorrowStart + $dayMs-1;
		$start_month		= gmmktime(0, 0, 0, $month, 1, $year);
		//first second of the following month minus 1; gmmktime() normalises month 13 into January of the next
		//year on its own. this also fixes the old strtotime('+1 month') overflow, where "next month" jumped
		//from e.g. Jan 31st straight into March.
		$start_next_month	= gmmktime(0, 0, 0, $month + 1, 1, $year);
		$end_month			= $start_next_month - 1;
		$end_next_month		= gmmktime(0, 0, 0, $month + 2, 1, $year) - 1;
		$query				= [];
		
		switch($filter_type){
			case 'none':	//none
			break;
			case 'today':
				$query[] = ['key' => '_start_ts', 'value' => $todayEnd, 'compare' => '<='];
				$query[] = ['key' => '_end_ts', 'value' => $todayStart, 'compare' => '>='];
			break;
			case 'future':
				$query[] = ['key' => '_start_ts', 'value' => $time, 'compare' => '>'];
			break;
			case 'tomorrow':
				$query[] = ['key' => '_start_ts', 'value' => $tomorrowEnd, 'compare' => '<='];
				$query[] = ['key' => '_end_ts', 'value' => $todayStart, 'compare' => '>='];
			break;
			case 'past':
				$query[] = ['key' => '_end_ts', 'value' => $todayStart, 'compare' => '<'];
			break;
			case 'month':
				$query[] = ['key' => '_start_ts', 'value' => $end_month, 'compare' => '<='];
				$query[] = ['key' => '_end_ts', 'value' => $start_month, 'compare' => '>='];
			break;
			case 'nextmonth':
				$query[] = ['key' => '_start_ts', 'value' => $end_next_month, 'compare' => '<='];
				$query[] = ['key' => '_end_ts', 'value' => $start_next_month, 'compare' => '>='];
			break;
			default:
				$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
				$f->throw_error('Wrong event filter');
			break;
		} 
		
		if(!empty($query))
			$response['meta_query'] = $query;
		
		//convert sortby
		switch($sort_by){
			case 'event_start_date':
				$response['orderby']	= 'meta_value_num';
				$response['meta_key']	= '_start_ts';
			break;
			case 'event_end_date':
				$response['orderby']	= 'meta_value_num';
				$response['meta_key']	= '_end_ts';
			break;
		}			
		
		return $response;
	}
	
	
	/**
	 * get event post data in array.
	 * if the post is not event, return empty array
	 * @param int    $postID
	 * @param string $prefix prepended to every returned key, e.g. 'event_'
	 * @return array date and location fields, empty when the post is not an EM event
	 */
	public static function get_event_post_data($postID, $prefix = ''){
		//memoize per request: the same event post is resolved for every layer of a slide
		static $em_cache = [];
		$ckey = $postID . '|' . $prefix;
		if(!isset($em_cache[$ckey])) $em_cache[$ckey] = self::_get_event_post_data($postID, $prefix);
		return $em_cache[$ckey];
	}

	/**
	 * uncached worker behind get_event_post_data(); dates are formatted with the site's date/time format
	 * @return array
	 */
	private static function _get_event_post_data($postID, $prefix = ''){
		if(self::isEventsExists() == false) return [];
		
		$postType = get_post_type($postID);
		
		if($postType != EM_POST_TYPE_EVENT) return [];
	
		$f			 = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$event		 = new EM_Event($postID, 'post_id');
		$location	 = $event->get_location();
		$ev			 = $event->to_array();
		$loc		 = $location->to_array();
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		
		$response = [
			$prefix.'id'				 => $f->get_val($ev, 'event_id'),
			$prefix.'start_date'		 => date_format(date_create_from_format('Y-m-d', $f->get_val($ev, 'event_start_date')), $date_format),
			$prefix.'end_date'			 => date_format(date_create_from_format('Y-m-d', $f->get_val($ev, 'event_end_date')), $date_format),
			$prefix.'start_time'		 => date_format(date_create_from_format('H:i:s', $f->get_val($ev, 'event_start_time')), $time_format),
			$prefix.'end_time'			 => date_format(date_create_from_format('H:i:s', $f->get_val($ev, 'event_end_time')), $time_format),
			$prefix.'location_name'		 => $f->get_val($loc, 'location_name'),
			$prefix.'location_address'	 => $f->get_val($loc, 'location_address'),
			$prefix.'location_slug'		 => $f->get_val($loc, 'location_slug'),
			$prefix.'location_town'		 => $f->get_val($loc, 'location_town'),
			$prefix.'location_state'	 => $f->get_val($loc, 'location_state'),
			$prefix.'location_postcode'	 => $f->get_val($loc, 'location_postcode'),
			$prefix.'location_region'	 => $f->get_val($loc, 'location_region'),
			$prefix.'location_country'	 => $f->get_val($loc, 'location_country'),
			$prefix.'location_latitude'	 => $f->get_val($loc, 'location_latitude'),
			$prefix.'location_longitude' => $f->get_val($loc, 'location_longitude')
		];
		
		return $response;
	}
	
	
	/**
	 * get events sort by array
	 * the extra sort options the editor offers for event sliders
	 * @return array
	 */
	public static function getArrSortBy(){
		return [
			'event_start_date'	=> __('Event Start Date', 'revslider'),
			'event_end_date'	=> __('Event End Date', 'revslider')
		];
	}
	
	/**
	 * triggered if we receive posts by categories (RevSliderSlider::get_posts_by_categories())
	 * injects the event date filter into the query arguments
	 * @return array
	 **/
	public function add_post_query($data, $slider){
		$filter_type = $slider->get_param('events_filter', 'none');
		if(self::isEventsExists()){
			$data['addition'] = RevSliderEventsManager::get_query($filter_type, $this->get_val($data, 'sort_by'));
		}
		
		return $data;
	}
	
	/**
	 * sr_streamline_post_data_post filter: merge the event fields into each post so layers can use
	 * %event_start_date%, %event_location_town% and the rest. Also fills an empty excerpt from the content.
	 * @return array
	 */
	public static function add_em_layer_v7($post_data, $data, $metas, $slider){
		if(self::isEventsExists() == false) return $post_data;
		
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');

		foreach($post_data ?? [] as $key => $post){
			$data = RevSliderEventsManager::get_event_post_data($f->get_val($post, 'id'), 'event_');
			if($data === false) continue;
			//modify excerpt if empty to be filled with content
			if(!isset($post['excerpt']) || trim($post['excerpt']) === ''){
				$post['excerpt'] = str_replace(['<br/>', '<br />'], '', strip_tags($f->get_val($post, ['content', 'content']), '<b><br><i><strong><small>'));
			}

			$post_data[$key] = array_merge($post, $data);
		}

		return $post_data;
	}
	
}

add_filter('sr_streamline_post_data_post', ['RevSliderEventsManager', 'add_em_layer_v7'], 10, 4);