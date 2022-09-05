<?php
/*
 Plugin Name: Paid Memberships Pro - MailPoet Add On
 Plugin URI: https://www.paidmembershipspro.com/add-ons/mailpoet-integration/
 Description: Automatically subscribe members to MailPoet lists or allow them to opt-in to specific MailPoet newsletters.
 Version: 1.0.0
 Author: Paid Memberships Pro
 Author URI: https://www.paidmembershipspro.com
 Text Domain: mailpoet_paid_memberships_pro_addon
 Domain Path: /languages
  */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_Paid_Memberships_Pro_Addon' ) ) {

/**
 * Main MailPoet Paid Memberships Pro Add-on Class
 *
 * @class MailPoet_Paid_Memberships_Pro_Addon
 * @version 1.0.0
 */
final class MailPoet_Paid_Memberships_Pro_Addon {

	/**
	 * Constants
	 */

	// Slug
	const slug = 'mailpoet-paid-memberships-pro-addon';

	// Text Domain
	const text_domain = 'mailpoet_paid_memberships_pro_addon';

	/**
	 * Global Variables
	 */

	/**
	 * The Plug-in name.
	 *
	 * @var string
	 */
	public $name = "MailPoet Paid Memberships Pro Add-on";

	/**
	 * The Plug-in version.
	 *
	 * @var string
	 */
	public $version = "1.0.0";

	/**
	 * The WordPress version the plugin requires minimum.
	 *
	 * @var string
	 */
	public $wp_version_min = "3.7.1";

	/**
	 * The single instance of the class
	 *
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * The Plug-in URL.
	 *
	 * @var string
	 */
	public $web_url = "http://www.wordpress.org/plugins/mailpoet-paid-memberships-pro-add-on/";

	/**
	 * The Plug-in documentation URL.
	 *
	 * @var string
	 */
	public $doc_url = "https://github.com/seb86/MailPoet-Paid-Memberships-Pro-Add-on/wiki/";

	/**
	 * GitHub Repo URL
	 *
	 * @var string
	 */
	public $github_repo_url = "https://github.com/username/MailPoet-Paid-Memberships-Pro-Add-on/";

	/**
	 * Main MailPoet Paid Memberships Pro Add-on Instance
	 *
	 * Ensures only one instance of  is loaded or can be loaded.
	 *
	 * @access public static
	 * @see MailPoet_Paid_Memberships_Pro_Addon()
	 * @return MailPoet Paid Memberships Pro Add-on - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		// Check plugin requirements
		$this->check_requirements();

		// Include required files
		$this->includes();

		// Hooks
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'init', array( &$this, 'init_mailpoet_paid_memberships_pro_addon' ), 0 );
	}

	/**
	 * Plugin action links.
	 *
	 * @access public
	 * @param mixed $links
	 * @return void
	 */
	public function action_links( $links ) {
		// List your action links
		if( current_user_can( 'manage_options' ) ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'options-general.php?page=pmpro-mailpoet' ) . '">' . __( 'Settings', MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_TEXT_DOMAIN ) . '</a>',
			);
		}

		return array_merge( $links, $plugin_links );
	}

	/**
	 * Plugin row meta links
	 *
	 * @access public
	 * @param array $input already defined meta links
	 * @param string $file plugin file path and name being processed
	 * @return array $input
	 */
	public function plugin_row_meta( $input, $file ) {
		if ( plugin_basename( __FILE__ ) !== $file ) {
			return $input;
		}

		$links = array(
			'<a href="' . esc_url( $this->doc_url ) . '">' . __( 'Documentation', MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_TEXT_DOMAIN ) . '</a>',
		);

		$input = array_merge( $input, $links );

		return $input;
	}

	/**
	 * Define Constants
	 *
	 * @access private
	 */
	private function define_constants() {
		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON', $this->name ); // Plugin Name
		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_FILE', __FILE__ ); // Plugin file name
		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_VERSION', $this->version ); // Plugin version
		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_WP_VERSION_REQUIRE', $this->wp_version_min ); // WordPress requires to be...
		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_TEXT_DOMAIN', self::text_domain );

		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_README_FILE', 'http://plugins.svn.wordpress.org/mailpoet-paid-memberships-pro-add-on/trunk/readme.txt' );

		define( 'GITHUB_REPO_URL' , $this->github_repo_url );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		define( 'MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_SCRIPT_MODE', $suffix );

	}

	/**
	 * Checks that the WordPress setup meets the plugin requirements.
	 *
	 * @access private
	 * @global string $wp_version
	 * @return boolean
	 */
	private function check_requirements() {
		global $wp_version;

		require_once(ABSPATH.'/wp-admin/includes/plugin.php');

		if ( !version_compare( $wp_version, MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_WP_VERSION_REQUIRE, '>=' ) ) {
			add_action('admin_notices', array( &$this, 'display_req_notice' ) );
			return false;
		}

		if( function_exists( 'is_plugin_active' ) ) {
			if ( !is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) && !is_plugin_active( 'wysija-newsletters/index.php' ) ) {
				add_action('admin_notices', array( &$this, 'display_req_notice_mailpoet' ) );
				add_action('admin_notices', array( &$this, 'display_req_notice_paid_memberships_pro' ) );
				return false;
			}

			if ( !is_plugin_active( 'wysija-newsletters/index.php' ) ) {
				add_action('admin_notices', array( &$this, 'display_req_notice_mailpoet' ) );
				return false;
			}

			if ( !is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ) {
				add_action('admin_notices', array( &$this, 'display_req_notice_paid_memberships_pro' ) );
				return false;
			}

			return true;
		}
	}

	/**
	 * Display the requirement notice for WordPress.
	 *
	 * @access static
	 */
	static function display_req_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires WordPress ' . MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_WP_VERSION_REQUIRE . ' or higher. Please <a href="%s">upgrade</a> your WordPress setup.', MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_TEXT_DOMAIN), MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON, admin_url( 'update-core.php' ) );
		echo '</p></div>';
	}

	/**
	 * Display the requirement notice for Paid Memberships Pro.
	 *
	 * @access static
	 */
	static function display_req_notice_paid_memberships_pro() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires Paid Memberships Pro for this plugin to work. Please install and activate <strong><a href="%s">Paid Memberships Pro</a></strong> first.', MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_TEXT_DOMAIN), MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON, admin_url('plugin-install.php?tab=search&type=term&s=Paid+Memberships+Pro') );
		echo '</strong></p></div>';
	}

	/**
	 * Display the requirement notice for MailPoet.
	 *
	 * @access static
	 */
	static function display_req_notice_mailpoet() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires MailPoet Newsletters for this plugin to work. Please install and activate <strong><a href="%s">MailPoet Newsletters</a></strong> first.', MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON_TEXT_DOMAIN), MAILPOET_PAID_MEMBERSHIPS_PRO_ADDON, admin_url('plugin-install.php?tab=search&type=term&s=MailPoet+Newsletters+%28formerly+Wysija%29') );
		echo '</p></div>';
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @access public
	 * @return void
	 */
	public function includes() {
		include_once( 'includes/mailpoet-paid-memberships-pro-addon-core-functions.php' ); // Contains core functions for the front/back end.

		if ( is_admin() ) {
			$this->admin_includes();
		}

		if ( ! is_admin() || defined('DOING_AJAX') ) {
			$this->frontend_includes();
		}

	}

	/**
	 * Include required admin files.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_includes() {
		//include_once( 'includes/admin/mailpoet-paid-memberships-pro-addon-admin-hooks.php' ); // Hooks used in the admin
		include_once( 'includes/admin/class-mailpoet-paid-memberships-pro-addon-install.php' ); // Install plugin
		include_once( 'includes/admin/class-mailpoet-paid-memberships-pro-addon-admin.php' ); // Admin section
	}

	/**
	 * Include required frontend files.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_includes() {
		include_once( 'includes/mailpoet-paid-memberships-pro-addon-functions.php' ); // Contains functions for various front-end events
		include_once( 'includes/mailpoet-paid-memberships-pro-addon-hooks.php' ); // Hooks used in the frontend
	}

	/**
	 * Runs when the plugin is initialized.
	 *
	 * @access public
	 */
	public function init_mailpoet_paid_memberships_pro_addon() {
		// Set up localisation
		$this->load_plugin_textdomain();

		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();

		// This will run on the frontend and for ajax requests
		if ( ! is_admin() || defined('DOING_AJAX') ) {

		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any 
	 * following ones if the same translation is present.
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), self::text_domain );

		load_textdomain( self::text_domain, WP_LANG_DIR . "/" . self::slug . "/" . $locale . ".mo" );

		// Set Plugin Languages Directory
		// Plugin translations can be filed in the mailpoet-paid-memberships-pro-addon/languages/ directory
		// Wordpress translations can be filed in the wp-content/languages/ directory
		load_plugin_textdomain( self::text_domain, false, dirname( plugin_basename( __FILE__ ) ) . "/languages" );
	}

	/** Helper functions ******************************************************/

	/**
	 * Get the plugin url.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Registers and enqueues stylesheets and javascripts 
	 * for the administration panel and the front of the site.
	 *
	 * @access private
	 */
	private function register_scripts_and_styles() {
		global $wp_locale;

		if ( is_admin() ) {
			// Stylesheet
			$this->load_file( self::slug . '_admin_style', '/assets/css/admin/mailpoet-paid-memberships-pro-addon.css' );
		}
		else {
			// Stylesheet
			$this->load_file( self::slug . '-style', '/assets/css/mailpoet-paid-memberships-pro-addon.css' );
		} // end if/else
	} // end register_scripts_and_styles

	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 *
	 * @access private
	 */
	private function load_file( $name, $file_path, $is_script = false, $support = array(), $version = '' ) {
		$url = $this->plugin_url() . $file_path;
		$file = $this->plugin_path() . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, $support, $version );
				wp_enqueue_script( $name );
			}
			else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if

	} // end load_file

} // end class

} // end if class exists

/**
 * Returns the main instance of MailPoet_Paid_Memberships_Pro_Addon to prevent the need to use globals.
 *
 * @return 
 */
function MailPoet_Paid_Memberships_Pro_Addon() {
	return MailPoet_Paid_Memberships_Pro_Addon::instance();
}

// Global for backwards compatibility.
$GLOBALS['mailpoet_paid_memberships_pro_addon'] = MailPoet_Paid_Memberships_Pro_Addon();

?>