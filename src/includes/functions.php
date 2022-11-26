<?php
/**
 * Accessible Cookie Notice utility functions.
 *
 * @category Utilities
 * @package  Accessible Cookie Notice
 * @author   Joe Dolson
 * @license  GPLv2 or later
 * @link     https://www.joedolson.com/accessible-cookie-notice/
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'acn_cookies_list' ) ) {
	/**
	 * List all cookies on site.
	 *
	 * @return string
	 */
	function acn_cookies_list() {
		$separator = ': ';
		$cookies   = $_COOKIE;
		ksort( $cookies );
		$content = '<ol>';

		foreach ( $cookies as $key => $val ) {
			if ( false === strpos( $key, 'wordpress_' ) && false === strpos( $key, 'wp-' ) ) {
				$content .= '<li><code> ' . $key . '</code>';
				$content .= $separator . $val; 
				$content .= '</li>'; 
			}
		} 
		$content .= '</ol>';

		return $content;
	}
}

if ( ! function_exists( 'acn_cookies_accepted' ) ) {
	/**
	 * Check if cookies are accepted.
	 *
	 * @return boolean
	 */
	function acn_cookies_accepted() {
		return (bool) Accessible_Cookie_Notice::cookies_accepted();
	}
}

if ( ! function_exists( 'acn_cookies_set' ) ) {
	/**
	 * Check if cookies are set.
	 *
	 * @return boolean
	 */
	function acn_cookies_set() {
		return (bool) Accessible_Cookie_Notice::cookies_set();
	}
}