<?php
/**
 * Accessible Cookie Notice settings class.
 *
 * @category Admin
 * @package  Accessible Cookie Notice
 * @author   Joe Dolson
 * @license  GPLv2 or later
 * @link     https://www.joedolson.com/accessible-cookie-notice/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible_Cookie_Notice_Settings class.
 *
 * @category  Admin
 * @package   Accessible Cookie Notice
 * @author    Joe Dolson
 * @copyright 2021
 * @license   GPLv2 or later
 * @version   1.0
 */
class Accessible_Cookie_Notice_Settings {
	public $positions         = array();
	public $styles            = array();
	public $choices           = array();
	public $links             = array();
	public $colors            = array();
	public $options           = array();
	public $effects           = array();
	public $times             = array();
	public $notices           = array();
	public $script_locations = array();
	public $cookie_messages   = array();

	public function __construct() {
		// actions.
		add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
		add_action( 'after_setup_theme', array( $this, 'load_defaults' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'settings_errors' ) );
	}

	/**
	 * Load plugin defaults.
	 */
	public function load_defaults() {
		$this->positions = array(
			'top'    => __( 'Top', 'accessible-cookie-notice' ),
			'bottom' => __( 'Bottom', 'accessible-cookie-notice' ),
			'manual' => __( 'Manual (shortcode or widget)', 'accessible-cookie-notice' ),
		);

		$this->styles = array(
			'none'  => __( 'None', 'accessible-cookie-notice' ),
			'light' => __( 'Light', 'accessible-cookie-notice' ),
			'dark'  => __( 'Dark', 'accessible-cookie-notice' )
		);

		$this->revoke_opts = array(
			'banner'    => __( 'Button in Banner', 'accessible-cookie-notice' ),
			'shortcode' => __( 'Manual (shortcode or widget)', 'accessible-cookie-notice' )
		);

		$this->links = array(
			'page'   => __( 'Page link', 'accessible-cookie-notice' ),
			'custom' => __( 'Custom link', 'accessible-cookie-notice' )
		);

		$this->colors = array(
			'highlight' => __( 'Highlight color', 'accessible-cookie-notice' ),
			'banner'    => __( 'Banner color', 'accessible-cookie-notice' ),
		);

		$this->times = apply_filters(
			'cn_cookie_expiry',
			array(
				'hour'     => array( __( 'An hour', 'accessible-cookie-notice' ), 3600 ),
				'day'      => array( __( '1 day', 'accessible-cookie-notice' ), 86400 ),
				'week'     => array( __( '1 week', 'accessible-cookie-notice' ), 604800 ),
				'month'    => array( __( '1 month', 'accessible-cookie-notice' ), 2592000 ),
				'3months'  => array( __( '3 months', 'accessible-cookie-notice' ), 7862400 ),
				'6months'  => array( __( '6 months', 'accessible-cookie-notice' ), 15811200 ),
				'year'     => array( __( '1 year', 'accessible-cookie-notice' ), 31536000 ),
				'infinity' => array( __( 'infinity', 'accessible-cookie-notice' ), 2147483647 )
			)
		);

		$this->effects = array(
			'none'  => __( 'None', 'accessible-cookie-notice' ),
			'fade'  => __( 'Fade', 'accessible-cookie-notice' ),
		);

		$this->script_locations = array(
			'header' => __( 'Header', 'accessible-cookie-notice' ),
			'footer' => __( 'Footer', 'accessible-cookie-notice' ),
		);

		$this->cookie_messages = array(
			0 => __( 'Cookies are small files that are stored on your browser. We use cookies and similar technologies to ensure our website works properly.', 'accessible-cookie-notice' ),
			1 => __( 'Cookies are small files that are stored on your browser. We use cookies and similar technologies to ensure our website works properly, and to personalize your browsing experience.', 'accessible-cookie-notice' ),
			2 => __( 'Cookies are small files that are stored on your browser. We use cookies and similar technologies to ensure our website works properly, personalize your browsing experience, and analyze how you use our website. For these reasons, we may share your site usage data with our analytics partners.', 'accessible-cookie-notice' ),
			3 => __( 'Cookies are small files that are stored on your browser. We use cookies and similar technologies to ensure our website works properly, personalize your browsing experience, analyze how you use our website, and deliver relevant ads to you. For these reasons, we may share your site usage data with our social media, advertising and analytics partners.', 'accessible-cookie-notice' ) );

		$text_strings = array(
			'acceptBtnText'    => __( 'Accept', 'accessible-cookie-notice' ),
			'rejectBtnText'    => __( 'Reject', 'accessible-cookie-notice' ),
			'revokeBtnText'    => __( 'Revoke Cookies', 'accessible-cookie-notice' ),
			'privacyBtnText'   => __( 'Privacy policy', 'accessible-cookie-notice' ),
			'dontSellBtnText'  => __( 'Do Not Sell', 'accessible-cookie-notice' ),
			'customizeBtnText' => __( 'Preferences', 'accessible-cookie-notice' ),
			'headingText'      => __( "We're Promoting Privacy", 'accessible-cookie-notice' ),
			'bodyText'         => $this->cookie_messages[0],
			'privacyBodyText'  => __( 'You can learn more about how we use cookies by visiting our privacy policy page.', 'accessible-cookie-notice' ),
		);

		$acn = Accessible_Cookie_Notice();
		// set default text strings.
		$acn->defaults['general']['message_text']              = __( 'We use cookies to optimize your experience on our website.', 'accessible-cookie-notice' );
		$acn->defaults['general']['accept_text']               = __( 'Accept Cookies', 'accessible-cookie-notice' );
		$acn->defaults['general']['refuse_text']               = __( 'Refuse Cookies', 'accessible-cookie-notice' );
		$acn->defaults['general']['revoke_message_text']       = __( 'You can revoke your consent any time using the Revoke consent button.', 'accessible-cookie-notice' );
		$acn->defaults['general']['revoke_text']               = __( 'Revoke Consent', 'accessible-cookie-notice' );
		$acn->defaults['general']['see_more_opt']['text'] = __( 'Privacy policy', 'accessible-cookie-notice' );

		// set translation strings on plugin activation.
		if ( true === $acn->options['general']['translate'] ) {
			$acn->options['general']['translate'] = false;

			$acn->options['general']['message_text']         = $acn->defaults['general']['message_text'];
			$acn->options['general']['accept_text']          = $acn->defaults['general']['accept_text'];
			$acn->options['general']['refuse_text']          = $acn->defaults['general']['refuse_text'];
			$acn->options['general']['revoke_message_text']  = $acn->defaults['general']['revoke_message_text'];
			$acn->options['general']['revoke_text']          = $acn->defaults['general']['revoke_text'];
			$acn->options['general']['see_more_opt']['text'] = $acn->defaults['general']['see_more_opt']['text'];

			update_option( 'acn_settings', $acn->options['general'] );
		}

		// WPML >= 3.2.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			$this->register_wpml_strings();
		// WPML and Polylang compatibility.
		} elseif ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'Accessible Cookie Notice', 'Message in the notice', $acn->options['general']['message_text'] );
			icl_register_string( 'Accessible Cookie Notice', 'Button text', $acn->options['general']['accept_text'] );
			icl_register_string( 'Accessible Cookie Notice', 'Refuse button text', $acn->options['general']['refuse_text'] );
			icl_register_string( 'Accessible Cookie Notice', 'Revoke message text', $acn->options['general']['revoke_message_text'] );
			icl_register_string( 'Accessible Cookie Notice', 'Revoke button text', $acn->options['general']['revoke_text'] );
			icl_register_string( 'Accessible Cookie Notice', 'Privacy policy text', $acn->options['general']['see_more_opt']['text'] );
			icl_register_string( 'Accessible Cookie Notice', 'Custom link', $acn->options['general']['see_more_opt']['link'] );
		}
	}

	/**
	 * Add submenu.
	 */
	public function admin_menu_options() {
		add_menu_page( __( 'Accessible Cookie Notice', 'accessible-cookie-notice' ), __( 'Cookies', 'accessible-cookie-notice' ), apply_filters( 'acn_management_permissions', 'manage_options' ), 'accessible-cookie-notice', array( $this, 'options_page' ), 'dashicons-privacy' );
	}

	/**
	 * Generate Settings Sidebar
	 *
	 * @param mixed array/boolean $add boolean or array to insert additional panels.
	 */
	public function settings_sidebar( $add = false ) {
		$add = apply_filters( 'acn_custom_admin_panels', $add );
		?>
		<div class="postbox-container jcd-narrow">
		<div class="metabox-holder">
		<?php
		if ( is_array( $add ) ) {
			foreach ( $add as $key => $value ) {
				?>
				<div class="ui-sortable meta-box-sortables">
					<div class="postbox">
						<h2><?php echo $key; ?></h2>

						<div class='<?php echo sanitize_title( $key ); ?> inside'>
							<?php echo $value; ?>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>
		<div class="ui-sortable meta-box-sortables">
			<div class="postbox support">
				<h2><strong><?php _e( 'Follow Me', 'accessible-cookie-notice' ); ?></strong></h2>

				<div class="inside resources">
					<p class="follow-me">
						<a href="https://twitter.com/intent/follow?screen_name=joedolson" class="twitter-follow-button" data-size="small" data-related="joedolson">Follow
							@joedolson</a>
						<script>!function (d, s, id) {
								var js, fjs = d.getElementsByTagName(s)[0];
								if (!d.getElementById(id)) {
									js = d.createElement(s);
									js.id = id;
									js.src = "https://platform.twitter.com/widgets.js";
									fjs.parentNode.insertBefore(js, fjs);
								}
							}(document, "script", "twitter-wjs");</script>
					</p>
					<ul>
						<li>
							<a href="<?php echo admin_url( 'admin.php?page=my-calendar-help' ); ?>#my-calendar-support"><?php _e( 'Get Support', 'accessible-cookie-notice' ); ?></a>
						</li>
						<li>
							<div class="dashicons dashicons-yes" aria-hidden='true'></div>
							<a href="http://profiles.wordpress.org/joedolson/"><?php _e( 'Check out my other plug-ins', 'accessible-cookie-notice' ); ?></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="ui-sortable meta-box-sortables">
			<div class="postbox legal">
				<h2><?php _e( 'Cookie Legislation', 'accessible-cookie-notice' ); ?></h2>
				<div class="inside legal">
					<ul>
						<li><strong><a href="https://eur-lex.europa.eu/legal-content/EN/ALL/?uri=CELEX%3A32002L0058"><?php _e( 'EU ePrivacy Directive', 'accessible-cookie-notice' ); ?></a></strong><br /><?php _e( 'Requires prior consent of the user before setting any cookies not necessary to the basic function of the site, but is not legally binding on its own.', 'accessible-cookie-notice' ); ?></li>
						<li><strong><a href="https://eur-lex.europa.eu/legal-content/EN/TXT/HTML/?uri=CELEX:32016R0679&from=EN"><?php _e( 'General Data Protection Regulation (GDPR)', 'accessible-cookie-notice' ); ?></a></strong><ul>
							<li><?php _e( 'Requires prior consent before setting any cookies not necessary to the basic function', 'accessible-cookie-notice' ); ?></li>
							<li><?php _e( 'Requires consent to be selective, so that a user can choose which cookies to accept', 'accessible-cookie-notice' ); ?></li>
							<li><?php _e( 'Consent must be revokable.', 'accessible-cookie-notice' ); ?></li>
							<li><?php _e( 'Consent must be renewed at least once per year.', 'accessible-cookie-notice' ); ?></li>
							<li><?php _e( 'Consent must be securely stored as legal documentation.', 'accessible-cookie-notice' ); ?></li>
							<li><?php _e( 'Consent can not be forced, assumed, or used as a requirement to access a service.', 'accessible-cookie-notice' ); ?></li>
						</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="ui-sortable meta-box-sortables">
			<div class="postbox support">
				<h2><strong><?php _e( 'Current Cookies Set', 'accessible-cookie-notice' ); ?></strong></h2>

				<div class="inside cookies">
					<?php
						echo acn_cookies_list( 'string' );
					?>
				</div>
			</div>
		</div>
		</div>
		</div>
		<?php
	}

	/**
	 * Options page output.
	 *
	 * @return mixed
	 */
	public function options_page() {
		echo '
		<div class="wrap accessible-cookie-notice">
			<h1>' . __( 'Accessible Cookie Notice', 'accessible-cookie-notice' ) . '</h1>
			<div class="postbox-container jcd-wide">
				<div class="metabox-holder">
					<div class="ui-sortable meta-box-sortables">
						<div class="postbox">

							<h2>' . __( 'Cookie Notice Settings', 'accessible-cookie-notice' ) . '</h2>
							<div class="inside">
								<form action="options.php" method="post">';

		settings_fields( 'acn_settings' );
		global $wp_settings_fields;
		$acn_settings = $wp_settings_fields['acn_settings'];
		foreach( $acn_settings as $section => $fields ) {
			$heading = ( 'acn_config' === $section ) ? __( 'Banner Configuration', 'accessible-cookie-notice' ) : __( 'Banner Design', 'accessible-cookie-notice' );
			echo '<h3>' . $heading . '</h3>';
			foreach ( (array) $acn_settings[ $section ] as $field ) {
				$class = '';

				if ( ! empty( $field['args']['class'] ) ) {
					$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
				}

				echo "<section{$class}>";
				call_user_func( $field['callback'], $field['args'] );
				echo '</section>';
			}
		}

		echo '
									<p class="submit">';
		submit_button( '', 'primary', 'save_acn_settings', false );
		echo '
									</p>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>';
		echo $this->settings_sidebar();
		echo '
		</div>';
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting( 'acn_settings', 'acn_settings', array( $this, 'validate_options' ) );

		add_settings_section( 'acn_config', '', '', 'acn_settings' );
		add_settings_field( 'acn_message_text', '', array( $this, 'acn_message_text' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_accept_text', '', array( $this, 'acn_accept_text' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_see_more', '', array( $this, 'acn_see_more' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_refuse_opt', '', array( $this, 'acn_refuse_opt' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_revoke_opt', '', array( $this, 'acn_revoke_opt' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_redirection', '', array( $this, 'acn_redirection' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_time', '', array( $this, 'acn_time' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_script_location', '', array( $this, 'acn_script_location' ), 'acn_settings', 'acn_config' );
		add_settings_field( 'acn_accept_code', '', array( $this, 'acn_accept_code' ), 'acn_settings', 'acn_config' );


		// design
		add_settings_section( 'cookie_notice_design', '', '', 'acn_settings' );
		add_settings_field( 'acn_position', '', array( $this, 'acn_position' ), 'acn_settings', 'cookie_notice_design' );
		add_settings_field( 'acn_hide_effect', '', array( $this, 'acn_hide_effect' ), 'acn_settings', 'cookie_notice_design' );
		add_settings_field( 'acn_css_style', '', array( $this, 'acn_css_style' ), 'acn_settings', 'cookie_notice_design' );
		add_settings_field( 'acn_colors', '', array( $this, 'acn_colors' ), 'acn_settings', 'cookie_notice_design' );
	}

	/**
	 * Message Text option.
	 */
	public function acn_message_text() {
		echo '
		<p id="cn_message_text">
			<label for="message_text">' . __( 'Cookie Notice banner message', 'accessible-cookie-notice' ) . '</label>
			<textarea id="message_text" name="acn_settings[message_text]" class="large-text" cols="50" rows="2">' . esc_textarea( Accessible_Cookie_Notice()->options['general']['message_text'] ) . '</textarea>
		</p>';
	}

	/**
	 * Accept cookie label option.
	 */
	public function acn_accept_text() {
		echo '
		<p id="cn_accept_text">
			<label for="accept_text">' . __( 'Accept Cookies Button Text', 'accessible-cookie-notice' ) . '</label>
			<input id="accept_text" type="text" class="regular-text" name="acn_settings[accept_text]" value="' . esc_attr( Accessible_Cookie_Notice()->options['general']['accept_text'] ) . '" />
		</p>';
	}

	/**
	 * Enable/Disable third party non functional cookies option.
	 */
	public function acn_refuse_opt() {
		echo '<p class="checkboxes"><input id="acn_refuse_opt" type="checkbox" name="acn_settings[refuse_opt]" value="1" ' . checked( true, Accessible_Cookie_Notice()->options['general']['refuse_opt'], false ) . ' /> <label for="acn_refuse_opt">' . __( 'Allow users to refuse third party non-functional cookies.', 'accessible-cookie-notice' ) . '</label></p>
			<div id="cn_refuse_opt_container"' . ( Accessible_Cookie_Notice()->options['general']['refuse_opt'] === false ? ' style="display: none;"' : '' ) . '>
				<p id="cn_refuse_text">
					<label for="refuse_text">' . __( 'Refuse Consent Button Text.', 'accessible-cookie-notice' ) . '</p>
					<input id="refuse_text" type="text" class="regular-text" name="acn_settings[refuse_text]" value="' . esc_attr( Accessible_Cookie_Notice()->options['general']['refuse_text'] ) . '" />
				</p>
			</div>';
	}

	/**
	 * Non functional cookies code.
	 */
	public function acn_accept_code() {
		$allowed_html = Accessible_Cookie_Notice()->get_allowed_html();
		$active       = ! empty( Accessible_Cookie_Notice()->options['general']['acceptance_code_footer'] ) && empty( Accessible_Cookie_Notice()->options['general']['acceptance_code_head'] ) ? 'body' : 'head';

		echo '
			<div id="acn_accept_code">
				<div id="acn_accept_code_fields">
					<div class="nav-tab-wrapper" role="tablist" data-default="accept_head">
						<button role="tab" aria-controls="accept_head" type="button" id="accept_head-tab" class="nav-tab' . ( 'head' === $active ? ' nav-tab-active' : '' ) . '">' . __( 'Head', 'accessible-cookie-notice' ) . '</button>
						<button role="tab" aria-controls="accept_body" type="button" id="accept_body-tab" class="nav-tab' . ( 'body' === $active ? ' nav-tab-active' : '' ) . '">' . __( 'Body', 'accessible-cookie-notice' ) . '</button>
					</div>
					<p id="accept_head" role="tabpanel" class="accept-code-tab' . ( 'head' === $active ? ' active' : '' ) . '">
						<label for="acceptance_code_head">' . __( 'JavaScript inserted in your site header, before the closing head tag.', 'accessible-cookie-notice' ) . '</label>
						<textarea id="acceptance_code_head" name="acn_settings[acceptance_code_head]" class="large-text" cols="50" rows="8" aria-describedby="script_code">' . html_entity_decode( trim( wp_kses( Accessible_Cookie_Notice()->options['general']['acceptance_code_head'], $allowed_html ) ) ) . '</textarea>
					</p>
					<p id="accept_body" role="tabpanel" class="accept-code-tab' . ( $active === 'body' ? ' active' : '' ) . '">
						<label for="acceptance_code_footer">' . __( 'JavaScript inserted in the footer, before the closing body tag.', 'accessible-cookie-notice' ) . '</label>
						<textarea id="acceptance_code_footer" name="acn_settings[acceptance_code_footer]" class="large-text" cols="50" rows="8" aria-describedby="script_code">' . html_entity_decode( trim( wp_kses( Accessible_Cookie_Notice()->options['general']['acceptance_code_footer'], $allowed_html ) ) ) . '</textarea>
					</p>
				</div>
				<p id="script_code">' . __( 'Code (e.g. Google Analytics) that runs only if cookies are accepted.', 'accessible-cookie-notice' ) . '</p>
			</div>';
	}

	/**
	 * Revoke cookies option.
	 */
	public function acn_revoke_opt() {
		echo '<p class="checkboxes"><input id="cn_revoke_cookies" type="checkbox" name="acn_settings[revoke_cookies]" value="1" ' . checked( true, Accessible_Cookie_Notice()->options['general']['revoke_cookies'], false ) . ' /> <label for="cn_revoke_cookies">' . __( 'Allow users to revoke their consent.', 'accessible-cookie-notice' ) . '</label></p>
			<div id="cn_revoke_opt_container"' . ( Accessible_Cookie_Notice()->options['general']['revoke_cookies'] ? '' : ' style="display: none;"' ) . '>
				<p>
				<label for="revoke_message_text">' . __( 'Revoke Message Text', 'accessible-cookie-notice' ) . '</label>
				<textarea id="revoke_message_text" name="acn_settings[revoke_message_text]" class="large-text" cols="50" rows="2">' . esc_textarea( Accessible_Cookie_Notice()->options['general']['revoke_message_text'] ) . '</textarea>
				</p>
				<p>
				<label for="revoke_text">' . __( 'Revoke Button Text', 'accessible-cookie-notice' ) . '</label>
				<input type="text" class="regular-text" name="acn_settings[revoke_text]" value="' . esc_attr( Accessible_Cookie_Notice()->options['general']['revoke_text'] ) . '" />
				</p>
		<fieldset>
			<legend>' . __( 'Method to display button to revoke consent.', 'accessible-cookie-notice' ) . '</legend>
			<ul class="checkboxes">';

		foreach ( $this->revoke_opts as $value => $label ) {
			echo '<li><input id="cn_revoke_cookies-' . $value . '" type="radio" name="acn_settings[revoke_cookies_opt]" value="' . $value . '" ' . checked( $value, Accessible_Cookie_Notice()->options['general']['revoke_cookies_opt'], false ) . ' aria-describedby="revoke_explain" /> <label for="cn_revoke_cookies-' . $value . '">' . esc_html( $label ) . '</label></li>';
		}

		echo '	</ul>
				<p class="description" id="revoke_explain">' . __( 'Select the method for displaying the revoke button - automatic (in the banner) or manual using <code>[cookies_revoke]</code> shortcode or widget.', 'accessible-cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Redirection on cookie accept.
	 */
	public function acn_redirection() {
		echo '<p class="checkboxes"><input id="cn_redirection" type="checkbox" name="acn_settings[redirection]" value="1" ' . checked( true, Accessible_Cookie_Notice()->options['general']['redirection'], false ) . ' /> <label for="cn_redirection">' . __( 'Reload after the notice is accepted.', 'accessible-cookie-notice' ) . '</label></p>';
	}

	/**
	 * Privacy policy link option.
	 */
	public function acn_see_more() {
		echo '<div class="acn-privacy-policy">';
		echo '<p class="checkboxes"><input id="cn_see_more" type="checkbox" name="acn_settings[see_more]" value="1" ' . checked( true, Accessible_Cookie_Notice()->options['general']['see_more'], false ) . ' /> <label for="cn_see_more">' . __( 'Add privacy policy link.', 'accessible-cookie-notice' ) . '</label></p>
			<div id="cn_see_more_opt"' . ( false === Accessible_Cookie_Notice()->options['general']['see_more'] ? ' style="display: none;"' : '') . '>';
		
		echo '
				<p id="cn_see_more_opt_link">
					<label for="see_more_link">' . __( 'Privacy Policy URL', 'accessible-cookie-notice' ) . '</label>
					<input id="see_more_link" type="text" class="regular-text" name="acn_settings[see_more_opt][link]" value="' . esc_attr( Accessible_Cookie_Notice()->options['general']['see_more_opt']['link'] ) . '" />
				</p>';
		echo '</div>';
	}

	/**
	 * Expiration time option.
	 */
	public function acn_time() {
		echo '
			<div id="cn_time">
				<p>
				<label for="acn_settings_time">' . __( 'Re-request cookie acceptance after', 'accessible-cookie-notice' ) . '</label>
				<select id="acn_settings_time" name="acn_settings[time]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, Accessible_Cookie_Notice()->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				</p>
			</div>';
	}

	/**
	 * Script placement option.
	 */
	public function acn_script_location() {
		echo '
		<fieldset>
			<legend>' . __( 'Cookie script insertion location', 'accessible-cookie-notice' ) . '</legend>
			<ul class="checkboxes">';

		foreach ( $this->script_locations as $value => $label ) {
			echo '<li><input id="cn_script_location-' . $value . '" type="radio" name="acn_settings[script_location]" value="' . esc_attr( $value ) . '" ' . checked( $value, Accessible_Cookie_Notice()->options['general']['script_location'], false ) . ' /> <label for="cn_script_location-' . $value . '">' . esc_html( $label ) . '</label></li>';
		}

		echo '
			</ul>
		</fieldset>';
	}

	/**
	 * Position option.
	 */
	public function acn_position() {
		echo '
		<fieldset>
			<legend>' . __( 'Display location for the Cookie notice', 'accessible-cookie-notice' ) . '</legend>
			<ul class="checkboxes" id="cn_position">';

		foreach ( $this->positions as $value => $label ) {
			$value = esc_attr( $value );

			echo '<li><input id="cn_position-' . $value . '" type="radio" name="acn_settings[position]" value="' . $value . '" ' . checked( $value, Accessible_Cookie_Notice()->options['general']['position'], false ) . ' /> <label for="cn_position-' . $value . '">' . esc_html( $label ) . '</label></li>';
		}

		echo '
			</ul>
		</fieldset>';
	}

	/**
	 * Animation effect option.
	 */
	public function acn_hide_effect() {
		echo '
		<fieldset>
			<legend>' . __( 'Animation style', 'accessible-cookie-notice' ) . '</legend>
			<ul id="cn_hide_effect" class="checkboxes">';

		foreach ( $this->effects as $value => $label ) {
			$value = esc_attr( $value );

			echo '<li><input id="cn_hide_effect-' . $value . '" type="radio" name="acn_settings[hide_effect]" value="' . $value . '" ' . checked( $value, Accessible_Cookie_Notice()->options['general']['hide_effect'], false ) . ' /> <label for=""cn_hide_effect-' . $value . '">' . esc_html( $label ) . '</label></li>';
		}

		echo '
			</ul>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function acn_css_style() {
		echo '
		<fieldset>
			<legend>' . __( 'Color scheme', 'accessible-cookie-notice' ) . '</legend>
			<ul id="cn_css_style" class="checkboxes">';

		foreach ( $this->styles as $value => $label ) {
			$value = esc_attr( $value );

			echo '<li><input id="cn_css_style-' . $value . '" type="radio" name="acn_settings[css_style]" value="' . $value . '" ' . checked( $value, Accessible_Cookie_Notice()->options['general']['css_style'], false ) . ' /> <label for="cn_css_style-' . $value . '">' . esc_html( $label ) . '</label></li>';
		}

		echo '
			</ul>
		</fieldset>';
	}

	/**
	 * Colors option.
	 */
	public function acn_colors() {
		echo '
		<fieldset>
			<legend>' . __( 'Banner Colors', 'accessible-cookie-notice' ) . '</legend>
			<ul class="checkboxes">';

		foreach ( $this->colors as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<li class="color-selector">
				<label for="cn_colors-' . $value . '">' . esc_html( $label ) . '</label>
				<input id="cn_colors-' . $value . '" class="cn_color" type="text" name="acn_settings[colors][' . $value . ']" value="' . esc_attr( Accessible_Cookie_Notice()->options['general']['colors'][$value] ) . '" />' .
			'</li>';
		}

		echo '
			</ul>
		</fieldset>';
	}

	/**
	 * Validate options.
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_options( $input ) {
		if ( ! current_user_can( apply_filters( 'acn_management_permissions', 'manage_options' ) ) ) {
			return $input;
		}

		// get main instance.
		$acn = Accessible_Cookie_Notice();

		if ( isset( $_POST['save_acn_settings'] ) ) {
			// position.
			$input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $acn->defaults['general']['position'] );

			// colors.
			$input['colors']['highlight']   = sanitize_text_field( isset( $input['colors']['highlight'] ) && $input['colors']['highlight'] !== '' && 1 === preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['highlight'] ) ? $input['colors']['highlight'] : $acn->defaults['general']['colors']['highlight'] );
			$input['colors']['banner'] = sanitize_text_field( isset( $input['colors']['banner'] ) && $input['colors']['banner'] !== '' && 1 === preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['banner'] ) ? $input['colors']['banner'] : $acn->defaults['general']['colors']['banner'] );

			// texts.
			$input['message_text']        = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $acn->defaults['general']['message_text'] );
			$input['accept_text']         = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $acn->defaults['general']['accept_text'] );
			$input['refuse_text']         = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $acn->defaults['general']['refuse_text'] );
			$input['revoke_message_text'] = wp_kses_post( isset( $input['revoke_message_text'] ) && $input['revoke_message_text'] !== '' ? $input['revoke_message_text'] : $acn->defaults['general']['revoke_message_text'] );
			$input['revoke_text']         = sanitize_text_field( isset( $input['revoke_text'] ) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $acn->defaults['general']['revoke_text'] );
			$input['refuse_opt']          = (bool) isset( $input['refuse_opt'] );
			$input['revoke_cookies']      = isset( $input['revoke_cookies'] );
			$input['revoke_cookies_opt']  = isset( $input['revoke_cookies_opt'] ) && array_key_exists( $input['revoke_cookies_opt'], $this->revoke_opts ) ? $input['revoke_cookies_opt'] : $acn->defaults['general']['revoke_cookies_opt'];

			// get allowed HTML.
			$allowed_html = $acn->get_allowed_html();

			// body refuse code.
			$input['acceptance_code_footer'] = wp_kses( isset( $input['acceptance_code_footer'] ) && $input['acceptance_code_footer'] !== '' ? $input['acceptance_code_footer'] : $acn->defaults['general']['acceptance_code_footer'], $allowed_html );

			// head refuse code.
			$input['acceptance_code_head'] = wp_kses( isset( $input['acceptance_code_head'] ) && $input['acceptance_code_head'] !== '' ? $input['acceptance_code_head'] : $acn->defaults['general']['acceptance_code_head'], $allowed_html );

			// banner style.
			$input['css_style'] = sanitize_text_field( isset( $input['css_style'] ) && in_array( $input['css_style'], array_keys( $this->styles ) ) ? $input['css_style'] : $acn->defaults['general']['css_style'] );

			// time.
			$input['time']          = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $acn->defaults['general']['time'] );

			// script placement.
			$input['script_location'] = sanitize_text_field( isset( $input['script_location'] ) && in_array( $input['script_location'], array_keys( $this->script_locations ) ) ? $input['script_location'] : $acn->defaults['general']['script_location'] );

			// hide effect.
			$input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $acn->defaults['general']['hide_effect'] );

			// redirection.
			$input['redirection'] = isset( $input['redirection'] );

			// privacy policy.
			$input['see_more']                  = isset( $input['see_more'] );
			$input['see_more_opt']['text']      = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $acn->defaults['general']['see_more_opt']['text'] );

			$input['translate'] = false;

			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', 'Message in the notice', $input['message_text'] );
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', 'Button text', $input['accept_text'] );
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', 'Refuse button text', $input['refuse_text'] );
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', 'Revoke message text', $input['revoke_message_text'] );
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', 'Revoke button text', $input['revoke_text'] );
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', 'Privacy policy text', $input['see_more_opt']['text'] );

			}

			add_settings_error( 'cn_acn_settings', 'save_acn_settings', __( 'Accessible Cookie Notice Settings saved.', 'accessible-cookie-notice' ), 'updated' );
		}

		return $input;
	}

	/**
	 * Load scripts and styles - admin.
	 */
	public function admin_enqueue_scripts( $page ) {
		if ( 'toplevel_page_accessible-cookie-notice' !== $page ) {
			return;
		}

		wp_enqueue_script( 'accessible-cookie-notice-admin', plugins_url( '../js/admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), Accessible_Cookie_Notice()->defaults['version'] );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'accessible-cookie-notice-admin', plugins_url( '../css/admin.css', __FILE__ ) );
	}

	/**
	 * Register WPML (>= 3.2) strings if needed.
	 *
	 * @return	void
	 */
	private function register_wpml_strings() {
		// get main instance.
		$acn = $acn;

		global $wpdb;

		// prepare strings
		$strings = array(
			'Message in the notice' => $acn->options['general']['message_text'],
			'Button text'           => $acn->options['general']['accept_text'],
			'Refuse button text'    => $acn->options['general']['refuse_text'],
			'Revoke message text'   => $acn->options['general']['revoke_message_text'],
			'Revoke button text'    => $acn->options['general']['revoke_text'],
			'Privacy policy text'   => $acn->options['general']['see_more_opt']['text'],
			'Custom link'           => $acn->options['general']['see_more_opt']['link']
		);

		// get query results.
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "icl_strings WHERE context = %s", 'Accessible Cookie Notice' ) );

		// check results
		foreach( $strings as $string => $value ) {
			// string does not exist.
			if ( ! in_array( $string, $results, true ) ) {
				// register string.
				do_action( 'wpml_register_single_string', 'Accessible Cookie Notice', $string, $value );
			}
		}
	}

	/**
	 * Display errors and notices.
	 *
	 * @global string $pagenow
	 */
	public function settings_errors() {
		global $pagenow;

		// force display notices in top menu settings page.
		if ( 'options-general.php' === $pagenow ) {
			return;
		}

		settings_errors( 'cn_acn_settings' );
	}

}