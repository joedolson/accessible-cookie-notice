<?php
/**
 * Accessible Cookie Notice public class.
 *
 * @category Public
 * @package  Accessible Cookie Notice
 * @author   Joe Dolson
 * @license  GPLv2 or later
 * @link     https://www.joedolson.com/accessible-cookie-notice/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible_Cookie_Notice_Public class.
 *
 * @category  Public
 * @package   Accessible Cookie Notice
 * @author    Joe Dolson
 * @copyright 2021
 * @license   GPLv2 or later
 * @version   1.0
 */
class Accessible_Cookie_Notice_Public {
	private $widget_url = '';
	private $is_bot = false;

	public function __construct() {
		// actions
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize public features.
	 */
	public function init() {
		// check preview mode
		$this->preview_mode = isset( $_GET['cn_preview_mode'] );

		// whether to count robots
		$this->is_bot = Accessible_Cookie_Notice()->bot_detect->is_crawler();

		// bail if in preview mode or it's a bot request
		if ( ! $this->preview_mode && ! $this->is_bot ) {
			// actions
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_notice_scripts' ) );
			add_filter( 'script_loader_tag', array( $this, 'wp_enqueue_script_async' ), 10, 3 );
			add_action( 'wp_head', array( $this, 'wp_print_header_scripts' ) );
			add_action( 'wp_head', array( $this, 'style_variables' ) );
			add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ) );
			add_action( 'wp_footer', array( $this, 'add_cookie_notice' ), 1000 );

			// filters
			add_filter( 'body_class', array( $this, 'change_body_class' ) );
		}
	}

	/**
	 * Accessible Cookie Notice output.
	 *
	 * @return mixed
	 */
	public function add_cookie_notice() {
		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			Accessible_Cookie_Notice()->options['general']['message_text'] = apply_filters( 'wpml_translate_single_string', Accessible_Cookie_Notice()->options['general']['message_text'], 'Accessible Cookie Notice', 'Message in the notice' );
			Accessible_Cookie_Notice()->options['general']['accept_text'] = apply_filters( 'wpml_translate_single_string', Accessible_Cookie_Notice()->options['general']['accept_text'], 'Accessible Cookie Notice', 'Button text' );
			Accessible_Cookie_Notice()->options['general']['refuse_text'] = apply_filters( 'wpml_translate_single_string', Accessible_Cookie_Notice()->options['general']['refuse_text'], 'Accessible Cookie Notice', 'Refuse button text' );
			Accessible_Cookie_Notice()->options['general']['revoke_message_text'] = apply_filters( 'wpml_translate_single_string', Accessible_Cookie_Notice()->options['general']['revoke_message_text'], 'Accessible Cookie Notice', 'Revoke message text' );
			Accessible_Cookie_Notice()->options['general']['revoke_text'] = apply_filters( 'wpml_translate_single_string', Accessible_Cookie_Notice()->options['general']['revoke_text'], 'Accessible Cookie Notice', 'Revoke button text' );
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_t' ) ) {
			Accessible_Cookie_Notice()->options['general']['message_text']        = icl_t( 'Accessible Cookie Notice', 'Message in the notice', Accessible_Cookie_Notice()->options['general']['message_text'] );
			Accessible_Cookie_Notice()->options['general']['accept_text']         = icl_t( 'Accessible Cookie Notice', 'Button text', Accessible_Cookie_Notice()->options['general']['accept_text'] );
			Accessible_Cookie_Notice()->options['general']['refuse_text']          = icl_t( 'Accessible Cookie Notice', 'Refuse button text', Accessible_Cookie_Notice()->options['general']['refuse_text'] );
			Accessible_Cookie_Notice()->options['general']['revoke_message_text']  = icl_t( 'Accessible Cookie Notice', 'Revoke message text', Accessible_Cookie_Notice()->options['general']['revoke_message_text'] );
			Accessible_Cookie_Notice()->options['general']['revoke_text']          = icl_t( 'Accessible Cookie Notice', 'Revoke button text', Accessible_Cookie_Notice()->options['general']['revoke_text'] );
		}

		// get cookie container args
		$options = apply_filters( 'cn_cookie_notice_args', array(
			'position'            => Accessible_Cookie_Notice()->options['general']['position'],
			'css_style'           => Accessible_Cookie_Notice()->options['general']['css_style'],
			'message_text'        => Accessible_Cookie_Notice()->options['general']['message_text'],
			'accept_text'         => Accessible_Cookie_Notice()->options['general']['accept_text'],
			'refuse_text'         => Accessible_Cookie_Notice()->options['general']['refuse_text'],
			'revoke_message_text' => Accessible_Cookie_Notice()->options['general']['revoke_message_text'],
			'revoke_text'         => Accessible_Cookie_Notice()->options['general']['revoke_text'],
			'refuse_opt'          => Accessible_Cookie_Notice()->options['general']['refuse_opt'],
			'revoke_cookies'      => Accessible_Cookie_Notice()->options['general']['revoke_cookies'],
		) );

		$options['message_text'] = wp_kses_post( $options['message_text'] );

		// message output
		$output = '
		<section id="acn" class="cookie-notice-hidden cookie-revoke-hidden cn-position-' . esc_attr( $options['position'] ) . '" aria-label="' . __( 'Cookie Settings', 'accessible-cookie-notice' ) . '">'
			. '<div class="cookie-notice-container">'
			. '<p id="acn-text" class="acn-text">'. esc_html( $options['message_text'] ) . '</p>'
			. '<p id="acn-buttons" class="acn-buttons"><button type="button" id="cn-accept-cookie" data-cookie-set="accept" class="cn-set-cookie ' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . '">' . esc_html( $options['accept_text'] ) . '</button>'
			. ( $options['refuse_opt'] === true ? '<button type="button" id="cn-refuse-cookie" data-cookie-set="refuse" class="cn-set-cookie ' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . '">' . esc_html( $options['refuse_text'] ) . '</button>' : '' )
			. '</p>'
			. '</div>
			' . ( $options['refuse_opt'] === true && $options['revoke_cookies'] == true ?
			'<div class="cookie-revoke-container">'
			. ( ! empty( $options['revoke_message_text'] ) ? '<p id="acn-revoke" class="acn-text">'. esc_html( $options['revoke_message_text'] ) . '</p>' : '' )
			. '<p id="acn-revoke-buttons" class="acn-buttons"><button type="button" class="cn-revoke-cookie ' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . '">' . esc_html( $options['revoke_text'] ) . '</button></p>
			</div>' : '' ) . '
		</section>';

		echo apply_filters( 'acn_output', $output, $options );
	}

	/**
	 * Load scripts and styles for public functions.
	 */
	public function wp_enqueue_notice_scripts() {
		$acn       = Accessible_Cookie_Notice();
		$in_footer = isset( $acn->options['general']['script_location'] ) && 'footer' === $acn->options['general']['script_location'];
		wp_enqueue_script( 'accessible-cookie-notice-front', plugins_url( '../js/front.js', __FILE__ ), array(), $acn->defaults['version'], $in_footer );

		wp_localize_script(
			'accessible-cookie-notice-front',
			'acnSettings',
			array(
				'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'cn_save_cases' ),
				'hideEffect'         => $acn->options['general']['hide_effect'],
				'position'           => $acn->options['general']['position'],
				'cookieName'         => 'acn_accepted',
				'cookieTime'         => $acn->settings->times[ Accessible_Cookie_Notice()->options['general']['time']][1],
				'cookiePath'         => ( defined( 'COOKIEPATH' ) ? (string) COOKIEPATH : '' ),
				'cookieDomain'       => ( defined( 'COOKIE_DOMAIN' ) ? (string) COOKIE_DOMAIN : '' ),
				'redirection'        => (int) $acn->options['general']['redirection'],
				'cache'              => $acn->caching_enabled(),
				'refuse'             => (int) $acn->options['general']['refuse_opt'],
				'revokeCookies'      => (int) $acn->options['general']['revoke_cookies'],
				'revokeCookiesOpt'   => $acn->options['general']['revoke_cookies_opt'],
				'secure'             => (int) is_ssl()
			)
		);

		wp_enqueue_style( 'accessible-cookie-notice-front', plugins_url( '../css/front.css', __FILE__ ) );
	}

	/**
	 * Make a JavaScript Asynchronous
	 *
	 * @param string $tag The original enqueued script tag
	 * @param string $handle The registered unique name of the script
	 * @param string $src
	 * @return string $tag The modified script tag
	 */
	public function wp_enqueue_script_async( $tag, $handle, $src ) {
		if ( 'accessible-cookie-notice-front' === $handle ) {
			$tag = str_replace( '<script', '<script async', $tag );
		}
		return $tag;
	}

	/**
	 * Print non functional JavaScript in body.
	 *
	 * @return mixed
	 */
	public function wp_print_footer_scripts() {
		if ( Accessible_Cookie_Notice()->cookies_accepted() ) {
			$scripts = apply_filters( 'acn_after_acceptance_scripts_footer', html_entity_decode( trim( wp_kses( Accessible_Cookie_Notice()->options['general']['acceptance_code_footer'], Accessible_Cookie_Notice()->get_allowed_html() ) ) ) );

			if ( ! empty( $scripts ) ) {
				echo $scripts;
			}
		}
	}

	/**
	 * Print non functional JavaScript in header.
	 *
	 * @return mixed
	 */
	public function wp_print_header_scripts() {
		if ( Accessible_Cookie_Notice()->cookies_accepted() ) {
			$scripts = apply_filters( 'acn_after_acceptance_scripts_head', html_entity_decode( trim( wp_kses( Accessible_Cookie_Notice()->options['general']['acceptance_code_head'], Accessible_Cookie_Notice()->get_allowed_html() ) ) ) );

			if ( ! empty( $scripts ) ) {
				echo $scripts;
			}
		}
	}

	/**
	 * Generate the optimal contrasting color for a given hex.
	 *
	 * @param string $color Hex value.
	 *
	 * @return string
	 */
	public function inverse_color( $color ) {
		$color = str_replace( '#', '', $color );
		if ( strlen( $color ) !== 6 ) {
			return '#000000';
		}
		$rgb       = '';
		$total     = 0;
		$red       = 0.299 * ( 255 - hexdec( substr( $color, 0, 2 ) ) );
		$green     = 0.587 * ( 255 - hexdec( substr( $color, 2, 2 ) ) );
		$blue      = 0.114 * ( 255 - hexdec( substr( $color, 4, 2 ) ) );
		$luminance = 1 - ( ( $red + $green + $blue ) / 255 );
		if ( $luminance < 0.5 ) {
			return '#ffffff';
		} else {
			return '#000000';
		}
	}

	/**
	 * Shift light colors lighter and dark colors darker for an alternate highlight.
	 *
	 * @param string $color Color hex.
	 *
	 * @return string New color hex
	 */
	public function shift_color( $color ) {
		$color   = str_replace( '#', '', $color );
		$rgb     = '';
		$percent = ( $this->inverse_color( $color ) === '#ffffff' ) ? - 20 : 20;
		$per     = $percent / 100 * 255;
		// Percentage to work with. Change middle figure to control color temperature.
		if ( $per < 0 ) {
			// DARKER.
			$per = abs( $per ); // Turns Neg Number to Pos Number.
			for ( $x = 0; $x < 3; $x ++ ) {
				$c    = hexdec( substr( $color, ( 2 * $x ), 2 ) ) - $per;
				$c    = ( $c < 0 ) ? 0 : dechex( $c );
				$rgb .= ( strlen( $c ) < 2 ) ? '0' . $c : $c;
			}
		} else {
			// LIGHTER.
			for ( $x = 0; $x < 3; $x ++ ) {
				$c    = hexdec( substr( $color, ( 2 * $x ), 2 ) ) + $per;
				$c    = ( $c > 255 ) ? 'ff' : dechex( $c );
				$rgb .= ( strlen( $c ) < 2 ) ? '0' . $c : $c;
			}
		}

		return '#' . $rgb;
	}

	/**
	 * Generate style variables for use by banner.
	 *
	 * @return string
	 */
	public function style_variables() {
		$theme      = Accessible_Cookie_Notice()->options['general']['css_style'];
		$style_vars = '';
		if ( 'light' === $theme ) {
			$theme = 'light';
		}
		foreach ( Accessible_Cookie_Notice()->options['general']['colors'] as $key => $var ) {
			$inverse = '';
			if ( 'banner' === $key ) {
				$inverse = $this->inverse_color( $var );
			}
			if ( '#ffffff' === $inverse ) {
				if ( 'dark' === $theme ) {
					$style_vars .= '--acn-text : ' . $inverse . '; ';
				} else {
					$style_vars .= '--acn-text : ' . $var . '; ';
					$var         = $inverse;
				}
			} elseif ( '#000000' === $inverse ) {
				if ( 'light' === $theme ) {
					$style_vars .= '--acn-text : ' . $inverse . '; ';
				} else {
					$style_vars .= '--acn-text : ' . $var . '; ';
					$var         = $inverse;
				}
			}
			if ( 'highlight' === $key ) {
				$style_vars .= '--acn-highlight-alt : ' . $this->shift_color( $var ) . '; ';
				$style_vars .= '--acn-highlight-inverse : ' . $this->inverse_color( $var ) . '; ';
			}
			if ( $var ) {
				$style_vars .= '--acn-' . sanitize_key( $key ) . ': ' . $var . '; ';
			}
		}
		if ( '' !== $style_vars ) {
			$style_vars = '#acn {' . $style_vars . '}';
		}

		$all_styles = "
<style>
<!--
/* Style variables for Accessible Cookie Notice */
$style_vars
-->
</style>";
		echo $all_styles;
	}

	/**
	 * Add new body classes.
	 *
	 * @param array $classes Body classes
	 * @return array
	 */
	public function change_body_class( $classes ) {
		if ( is_admin() ) {
			return $classes;
		}

		if ( Accessible_Cookie_Notice()->cookies_set() ) {
			$classes[] = 'cookies-set';

			if ( Accessible_Cookie_Notice()->cookies_accepted() ) {
				$classes[] = 'cookies-accepted';
			} else {
				$classes[] = 'cookies-refused';
			}
		} else {
			$classes[] = 'cookies-not-set';
		}

		return $classes;
	}
}