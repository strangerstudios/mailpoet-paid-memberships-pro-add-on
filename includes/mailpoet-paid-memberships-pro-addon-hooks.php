<?php
/**
 * MailPoet Paid Memberships Pro Add-on Hooks
 *
 * Hooks for various functions used.
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet Paid Memberships Pro Add-on/Functions
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Actions
add_action( 'pmpro_checkout_after_billing_fields', 'mailpoet_pmpro_addon_checkout_checkbox' );
add_action( 'pmpro_after_checkout', 'mailpoet_pmpro_addon_after_checkout', 10, 1 );

// Filters
add_filter( 'pmpro_valid_gateways', 'mailpoet_pmpro_addon_valid_gateways' );
add_filter( 'pmpro_email_body', 'mailpoet_pmpro_addon_email_body', 10, 2 );

?>