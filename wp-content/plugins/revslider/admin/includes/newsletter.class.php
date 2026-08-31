<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

if(!class_exists('ThemePunch_Newsletter')){
	 
	/**
	 * Opt-in/opt-out against the ThemePunch newsletter service. Wrapped in a class_exists() guard because
	 * other ThemePunch plugins ship the same class.
	 */
	class ThemePunch_Newsletter {

		protected static $remote_url	= 'http://newsletter.themepunch.com/';
		protected static $subscribe		= 'subscribe.php';
		protected static $unsubscribe	= 'unsubscribe.php';

		/**
		 * Subscribe to the ThemePunch Newsletter
		 * @return array|false the decoded server response, false on a transport error or a bad response
		 **/
		public static function subscribe($email){
			global $wp_version;
			
			$request = wp_safe_remote_post(self::$remote_url.self::$subscribe, [
				'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
				'timeout' => 15,
				'body' => [
					'email' => urlencode($email)
				]
			]);
			
			if(is_wp_error($request)) return false;

			if($response = json_decode($request['body'], true)){
				return (!is_array($response)) ? false : $response;
			}

			return false;
		}
		
		
		/**
		 * Unsubscribe to the ThemePunch Newsletter
		 * @return array|false the decoded server response, false on a transport error or a bad response
		 **/
		public static function unsubscribe($email){
			global $wp_version;
			
			$request = wp_safe_remote_post(self::$remote_url.self::$unsubscribe, [
				'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
				'timeout' => 15,
				'body' => [
					'email' => urlencode($email)
				]
			]);
			
			if(is_wp_error($request)) return false;
		
			if($response = json_decode($request['body'], true)){
				return (!is_array($response)) ? false : $response;
			}

			return false;
		}
		
	}
}
