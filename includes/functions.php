<?php

/**
 * Get the options for this plugin.
 *
 * @since TBD
 *
 * @return array
 */
function pmpro_mailpoet_get_options() {
	$defualt_options = array(
		'unsubscribe_on_level_change' => 1,
		'nonmember_lists'             => array(),
		'opt-in_lists'                => array(),
	);
	$options = get_option( 'pmpro_mailpoet_options', array() );
	return array_merge( $defualt_options, $options );
}

/**
 * Get all PMPro levels.
 *
 * @since TBD
 *
 * @return array
 */
function pmpro_mailpoet_get_all_levels() {
	if ( function_exists( 'pmpro_getAllLevels' ) ) {
		return pmpro_getAllLevels();
	} else {
		return array();
	}
}
