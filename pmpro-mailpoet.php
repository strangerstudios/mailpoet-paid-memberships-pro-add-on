<?php
/*
Plugin Name: Paid Memberships Pro - MailPoet Add On
Plugin URI: http://www.paidmembershipspro.com/pmpro-mailpoet/
Description: Sync your WordPress users and members with MailPoet lists.
Version: 0.1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
Text Domain: pmpro-mailpoet
*/

define( 'PMPRO_MAILPOET_BASE_FILE', __FILE__ );
define( 'PMPRO_MAILPOET_DIR', dirname( __FILE__ ) );

require_once PMPRO_MAILPOET_DIR . '/includes/functions.php';               // General plugin functions.
require_once PMPRO_MAILPOET_DIR . '/includes/members-lists-functions.php'; // Handle adding/removing users from lists on level change.
require_once PMPRO_MAILPOET_DIR . '/includes/opt-in-lists-functions.php';  // Handle adding/removing users from opt-in lists.
require_once PMPRO_MAILPOET_DIR . '/includes/api-wrapper.php';             // Abstract API interaction.
require_once PMPRO_MAILPOET_DIR . '/includes/settings.php';                // Set up settings page.

/**
 * Shows a notice on the PMPro MailPoet settings page if MailPoet V3 isn't installed.
 */
function pmpro_mailpoet_show_notice() {
	global $msg, $msgt;

	// MailPoet V3 is installed, just bail.
	if ( function_exists( 'mailpoet_deactivate_plugin' ) ) {
		return;
	}
	// Show the notice here.
	if ( ! empty( $_REQUEST['page'] ) && sanitize_text_field( $_REQUEST['page'] ) == 'pmpro-mailpoet' ) {
		$mailpoet_v3_org = 'https://wordpress.org/plugins/mailpoet/';
		$msgt            = sprintf(
			__( "In order for <strong>Paid Memberships Pro - MailPoet Integration</strong> to function correctly, you must install or activate the latest version of <a href='%s' target='_blank'>MailPoet v3</a>.", 'pmpro-mailpoet' ),
			esc_url( $mailpoet_v3_org )
		);

		pmpro_setMessage( $msgt, 'error' );
		pmpro_showMessage();
	}

}
add_action( 'admin_notices', 'pmpro_mailpoet_show_notice' );

/**
 * Load the languages folder for translations.
 *
 * @since TBD
 */
function pmpro_mailpoet_load_textdomain() {
	load_plugin_textdomain( 'pmpro-mailpoet', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pmpro_mailpoet_load_textdomain' );

/*
 * Load CSS, JS files.
 *
 * @since TBD
 */
function pmpro_mailpoet_scripts() {
	wp_enqueue_style( 'pmprorh_frontend', plugins_url( 'css/pmpromailpoet.css', PMPRO_MAILPOET_BASE_FILE ), null, '' );
}
add_action( 'admin_enqueue_scripts', 'pmpro_mailpoet_scripts' );
add_action( 'wp_enqueue_scripts', 'pmpro_mailpoet_scripts' );

/**
 * Add links to the plugin action links
 *
 * @since TBD
 *
 * @param $links (array) - The existing link array
 * @return array -- Array of links to use
 */
function pmpro_mailpoet_add_action_links( $links ) {

	$new_links = array(
		'<a href="' . get_admin_url( null, 'options-general.php?page=pmpro_mailpoet_options' ) . '">' . __( 'Settings', 'pmpro-mailpoet' ) . '</a>',
	);
	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pmpro_mailpoet_add_action_links' );

/**
 * Add links to the plugin row meta
 *
 * @since TBD
 *
 * @param $links - Links for plugin
 * @param $file - main plugin filename
 * @return array - Array of links
 */
function pmpro_mailpoet_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-mailpoet.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/pmpro-mailpoet-integration/' ) . '" title="' . esc_attr( __( 'View Documentation', 'pmpro-mailpoet' ) ) . '">' . __( 'Docs', 'pmpro-mailpoet' ) . '</a>',
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/support/' ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro-mailpoet' ) ) . '">' . __( 'Support', 'pmpro-mailpoet' ) . '</a>',
		);
		$links     = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpro_mailpoet_plugin_row_meta', 10, 2 );
