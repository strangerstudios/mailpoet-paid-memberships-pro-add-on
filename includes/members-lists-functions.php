<?php

/**
 * Subscribe users to nonmember lists when they register.
 *
 * @since TBD
 *
 * @param int $user_id that was registered.
 */
function pmpro_mailpoet_user_register( $user_id ) {
	$options = pmpro_mailpoet_get_options();
	if ( ! empty( $options['nonmember_lists'] ) && ( ! function_exists( 'pmpro_is_checkout' ) || ! pmpro_is_checkout() ) ) {
		// Registering for site without recieving level. Add to non-member lists.
		pmpro_mailpoet_add_user_to_lists( $user_id, $options['nonmember_lists'] );
	}
}
add_action( 'user_register', 'pmpro_mailpoet_user_register' );

/**
 * When users change levels, add/remove them to level-specific lists.
 *
 * @since TBD
 *
 * @param array $pmpro_old_user_levels Array of users and levels that the user was in before.
 */
function pmpro_mailpoet_after_all_membership_level_changes( $pmpro_old_user_levels ) {
	// Get plugin options.
	$options = pmpro_mailpoet_get_options();

	// Loop through all users who have changed levels.
	foreach ( $pmpro_old_user_levels as $user_id => $old_levels ) {
		// Get current lists.
		$current_lists = pmpro_mailpoet_get_user_list_ids( $user_id );

		// Get lists for old levels.
		$old_lists = array();
		if ( ! empty( $options['unsubscribe_on_level_change'] ) ) {
			if ( ! empty( $old_levels ) ) {
				foreach ( $old_levels as $level ) {
					if ( ! empty( $options[ 'level_' . $level->id . '_lists' ] ) ) {
						$old_lists = array_merge( $old_lists, $options[ 'level_' . $level->id . '_lists' ] );
					}
				}
			} elseif ( ! empty( $options['nonmember_lists'] ) ) {
				$old_lists = $options['nonmember_lists'];
			}
		}

		// Get lists for new levels.
		$new_lists   = array();
		$user_levels = pmpro_getMembershipLevelsForUser( $user_id );
		if ( ! empty( $user_levels ) ) {
			foreach ( $user_levels as $level ) {
				if ( ! empty( $options[ 'level_' . $level->id . '_lists' ] ) ) {
					$new_lists = array_merge( $new_lists, $options[ 'level_' . $level->id . '_lists' ] );
				}
			}
		} elseif ( ! empty( $options['nonmember_lists'] ) ) {
			$new_lists = $options['nonmember_lists'];
		}

		// Remove duplicate list elements.
		$current_lists = array_unique( $current_lists );
		$old_lists     = array_unique( $old_lists );
		$new_lists     = array_unique( $new_lists );

		// Calculate lists to add/remove.
		$add_lists = array_diff( $new_lists, $current_lists ); // Add user to all lists that they are not already in.
		$remove_lists = array_diff( array_intersect( $old_lists, $current_lists ), $new_lists ); // Remove user from all lists that they already have and are not being added to above.

		// Remove user from any lists that they were in but should no longer be in.
		pmpro_mailpoet_remove_user_from_lists( $user_id, $remove_lists );
		
		// Add user to any lists that they are not already in.
		pmpro_mailpoet_add_user_to_lists( $user_id, $add_lists );
	}
}
add_action( 'pmpro_after_all_membership_level_changes', 'pmpro_mailpoet_after_all_membership_level_changes', 10, 1 );