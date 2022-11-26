<?php
/*
Plugin Name: Accessible Cookie Notice
Description: Accessible Cookie Notice enables your site to accessibly inform users that it uses cookies and comply with GDPR, CCPA and other data privacy laws.
Version: 1.0.0
Author: Joe Dolson
Author URI: https://www.joedolson.com
Plugin URI: https://www.joedolson.com/accessible-cookie-notice/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: accessible-cookie-notice
Domain Path: /lang
Update URI: https://www.joedolson.com
*/
/*
	Copyright 2021  Joe Dolson (email : joe@joedolson.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible Cookie Notice class.
 *
 * @class Accessible_Cookie_Notice
 * @version 1.0.0
 */
class Accessible_Cookie_Notice {

	/**
	 * @var $defaults
	 */
	public $defaults = array(
		'general' => array(
			'position'               => 'bottom',
			'script_location'       => 'bottom',
			'css_style'              => 'dark',
			'see_more'               => false,
			'see_more_opt'           => array(),
			'message_text'           => '',
			'accept_text'            => '',
			'refuse_text'            => '',
			'refuse_opt'             => false,
			'acceptance_code_footer' => '',
			'acceptance_code_head'   => '',
			'revoke_cookies'         => false,
			'revoke_cookies_opt'     => 'banner',
			'revoke_message_text'    => '',
			'revoke_text'            => '',
			'redirection'            => false,
			'time'                   => 'month',
			'time_rejected'          => 'month',
			'hide_effect'            => 'fade',
			'colors' => array(
				'highlight' => '#2271b1',
				'banner'    => '#2c3338',
			),
			'translate'              => true,
		),
		'version' => '1.0.0',
	);

	private static $_instance;

	/**
	 * Disable object cloning.
	 */
	public function __clone() {}

	/**
	 * Disable unserializing of the class.
	 */
	public function __wakeup() {}

	/**
	 * Main plugin instance.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();

			add_action( 'plugins_loaded', array( self::$_instance, 'load_textdomain' ) );

			self::$_instance->includes();

			self::$_instance->bot_detect = new Accessible_Cookie_Notice_Bot_Detect();
			self::$_instance->frontend   = new Accessible_Cookie_Notice_Public();
			self::$_instance->settings   = new Accessible_Cookie_Notice_Settings();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// get options.
		$options = get_option( 'acn_settings', $this->defaults['general'] );

		// merge old options with new ones.
		$this->options = array(
			'general' => $this->multi_array_merge( $this->defaults['general'], $options )
		);

		// actions
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'wp_ajax_cn_dismiss_notice', array( $this, 'ajax_dismiss_admin_notice' ) );

		// filters
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function includes() {
		include_once( plugin_dir_path( __FILE__ ) . 'includes/detect-bots.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/public.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/settings.php' );
	}

	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'accessible-cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Activate the plugin.
	 */
	public function activation() {
		add_option( 'acn_settings', $this->defaults['general'], '', 'no' );
	}

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'cookies_accepted', array( $this, 'cookies_accepted_shortcode' ) );
		add_shortcode( 'cookies_revoke', array( $this, 'cookies_revoke_shortcode' ) );
		add_shortcode( 'cookies_policy_link', array( $this, 'cookies_policy_link_shortcode' ) );
	}

	/**
	 * Register cookies accepted shortcode.
	 *
	 * @param array $args Array of shortcode attributes.
	 * @param mixed $content Contained content.
	 *
	 * @return mixed
	 */
	public function cookies_accepted_shortcode( $args, $content ) {
		if ( $this->cookies_accepted() ) {
			$scripts = html_entity_decode( trim( wp_kses( $content, $this->get_allowed_html() ) ) );

			if ( ! empty( $scripts ) ) {
				if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content ) ) {
					$scripts = do_shortcode( $scripts );
				}
				return $scripts;
			}
		}

		return '';
	}

	/**
	 * Register cookies accepted shortcode.
	 *
	 * @param array $args Array of shortcode attributes.
	 * @param mixed $content Contained content.
	 *
	 * @return mixed
	 */
	public function cookies_revoke_shortcode( $args, $content ) {
		// get options.
		$options = $this->options['general'];

		// defaults.
		$defaults = array(
			'title' => $options['revoke_text'],
		);

		// combine shortcode arguments.
		$args = shortcode_atts( $defaults, $args );

		// escape class(es).
		$args['class'] = esc_attr( $args['class'] );

		if ( Accessible_Cookie_Notice()->get_status() === 'active' ) {
			$shortcode = '<button type="button" class="cn-revoke-cookie cn-button cn-revoke-inline' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '" data-hu-action="notice-revoke">' . esc_html( $args['title'] ) . '</button>';
		} else {
			$shortcode = '<button type="button" class="cn-revoke-cookie cn-button cn-revoke-inline' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '">' . esc_html( $args['title'] ) . '</button>';
		}

		return $shortcode;
	}

	/**
	 * Register cookies policy link shortcode.
	 *
	 * @param array  $args Array of shortcode attributes.
	 * @param string $content Contained content.
	 *
	 * @return string
	 */
	public function cookies_policy_link_shortcode( $args, $content ) {
		// get options.
		$options = $this->options['general'];

		// defaults.
		$defaults = array(
			'title' => esc_html( $options['see_more_opt']['text'] !== '' ? $options['see_more_opt']['text'] : __( 'Privacy Policy', 'accessible-cookie-notice' ) ),
			'link'  => ( ! is_numeric( $options['see_more_opt'] ) ) ? $options['see_more_opt']['link'] : get_permalink( (int) $options['see_more_opt']['link'] ),
		);

		// combine shortcode arguments.
		$args = shortcode_atts( $defaults, $args );

		$shortcode = '<a href="' . $args['link'] . '" id="cn-more-info" class="cn-privacy-policy-link cn-link' . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '">' . esc_html( $args['title'] ) . '</a>';

		return $shortcode;
	}

	/**
	 * Check if cookies are accepted.
	 *
	 * @return bool
	 */
	public static function cookies_accepted() {
		$result = isset( $_COOKIE['acn_accepted'] ) && 'true' === $_COOKIE['acn_accepted'];

		return apply_filters( 'acn_is_cookie_accepted', $result );
	}

	/**
	 * Check if cookies are set.
	 *
	 * @return boolean Whether cookies are set
	 */
	public function cookies_set() {
		$result = isset( $_COOKIE['acn_accepted'] );

;		return apply_filters( 'acn_is_cookie_set', $result );
	}

	/**
	 * Add links to settings page.
	 *
	 * @param array $links
	 * @param string $file
	 * @return array
	 */
	public function plugin_action_links( $links, $file ) {
		if ( ! current_user_can( apply_filters( 'acn_management_permissions', 'manage_options' ) ) )
			return $links;

		if ( $file == plugin_basename( __FILE__ ) )
			array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=cookie-notice' ) ), __( 'Settings', 'cookie-notice' ) ) );

		return $links;
	}

	/**
	 * Get allowed script blocking HTML.
	 *
	 * @return array
	 */
	public function get_allowed_html() {
		return apply_filters(
			'acn_acceptance_code_allowed_html',
			array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'script' => array(
						'type' => array(),
						'src' => array(),
						'charset' => array(),
						'async' => array()
					),
					'noscript' => array(),
					'style' => array(
						'type' => array()
					),
					'iframe' => array(
						'src' => array(),
						'height' => array(),
						'width' => array(),
						'frameborder' => array(),
						'allowfullscreen' => array()
					)
				)
			)
		);
	}

	/**
	 * Helper: Check whether any common caching plugins are enabled.
	 *
	 * @return bool
	 */
	public function caching_enabled() {
		// W3 Total Cache.
		if ( function_exists( 'w3tc_pgcache_flush_post' ) ) {
			return true;
		}

		// WP Super Cache.
		if ( function_exists( 'wp_cache_post_change' ) ) {
			return true;
		}

		// WP Rocket.
		if ( function_exists( 'rocket_clean_post' ) ) {
			return true;
		}

		// WP Fastest Cache.
		if ( isset( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'singleDeleteCache' ) ) {
			return true;
		}

		// Comet Cache.
		if ( class_exists( 'comet_cache' ) ) {
			return true;
		}

		// Cache Enabler.
		if ( class_exists( 'Cache_Enabler' ) ) {
			return true;
		}

		// WP Optimize.
		if ( class_exists( 'WPO_Page_Cache' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Merge multidimensional associative arrays.
	 * Works only with strings, integers and arrays as keys. Values can be any type but they have to have same type to be kept in the final array.
	 * Every array should have the same type of elements. Only keys from $defaults array will be kept in the final array unless $siblings are not empty.
	 * $siblings examples: array( '=>', 'only_first_level', 'first_level=>second_level', 'first_key=>next_key=>sibling' ) and so on.
	 * Single '=>' means that all siblings of the highest level will be kept in the final array.
	 *
	 * @param array         $default Array with default values.
	 * @param array         $array Array to merge.
	 * @param boolean|array $siblings Allow "string" siblings to copy from $array if not in $defaults, false otherwise.
	 *
	 * @return array Merged arrays
	 */
	public function multi_array_merge( $defaults, $array, $siblings = false ) {
		// make a copy for better performance and to prevent $default override in foreach.
		$copy = $defaults;

		// prepare siblings for recursive deeper level.
		$new_siblings = array();

		// allow siblings.
		if ( ! empty( $siblings ) && is_array( $siblings ) ) {
			foreach ( $siblings as $sibling ) {
				// highest level siblings.
				if ( $sibling === '=>' ) {
					// copy all non-existent string siblings.
					foreach( $array as $key => $value ) {
						if ( is_string( $key ) && ! array_key_exists( $key, $defaults ) ) {
							$defaults[ $key ] = null;
						}
					}
				// sublevel siblings.
				} else {
					// explode siblings.
					$ex = explode( '=>', $sibling );

					// copy all non-existent siblings.
					foreach ( array_keys( $array[$ex[0]] ) as $key ) {
						if ( ! array_key_exists( $key, $defaults[$ex[0]] ) ) {
							$defaults[$ex[0]][ $key ] = null;
						}
					}

					// more than one sibling child.
					if ( count( $ex ) > 1 ) {
						$new_siblings[$ex[0]] = array( substr_replace( $sibling, '', 0, strlen( $ex[0] . '=>' ) ) );
						// no more sibling children.
					} else {
						$new_siblings[$ex[0]] = false;
					}
				}
			}
		}

		// loop through first array.
		foreach ( $defaults as $key => $value ) {
			// integer key.
			if ( is_int( $key ) ) {
				$copy = array_unique( array_merge( $defaults, $array ), SORT_REGULAR );

				break;
			// string key.
			} elseif ( is_string( $key ) && isset( $array[ $key ] ) ) {
				// string, boolean, integer or null values.
				if ( ( is_string( $value ) && is_string( $array[ $key ] ) ) || ( is_bool( $value ) && is_bool( $array[ $key ] ) ) || ( is_int( $value ) && is_int( $array[ $key ] ) ) || is_null( $value ) ) {
					$copy[ $key ] = $array[ $key ];
					// arrays.
				} elseif ( is_array( $value ) && isset( $array[ $key ] ) && is_array( $array[ $key ] ) ) {
					if ( empty( $value ) ) {
						$copy[ $key ] = $array[ $key ];
					} else {
						$copy[ $key ] = $this->multi_array_merge( $defaults[ $key ], $array[ $key ], ( isset( $new_siblings[ $key ] ) ? $new_siblings[ $key ] : false ) );
					}
				}
			}
		}

		return $copy;
	}

	/**
	 * Indicate if current page is the Cookie Policy page
	 *
	 * @return bool
	 */
	public function is_cookie_policy_page() {
		$see_more = $this->options['general']['see_more_opt'];

		$cp_id   = is_numeric( $see_more['link_type'] ) ? $see_more : false;
		if ( ! $cp_id ) {
			return false;
		}

		$cp_slug      = get_post_field( 'post_name', $cp_id );
		$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );

		return ( $current_page->post_name === $cp_slug );
	}

}

/**
 * Initialize Accessible Cookie Notice.
 */
function Accessible_Cookie_Notice() {
	static $instance;

	// first call to instance() initializes the plugin.
	if ( $instance === null || ! ( $instance instanceof Accessible_Cookie_Notice ) ) {
		$instance = Accessible_Cookie_Notice::instance();
	}

	return $instance;
}

$acn = Accessible_Cookie_Notice();
