<?php
/**
 * Add the non-member tags to users when they register without a level.
 *
 * @since TBD
 *
 * @param int $user_id that was registered.
 */
function pmpro_mailpoet_tags_user_register( $user_id ) {
	$options = pmpro_mailpoet_get_options();
	if ( ! empty( $options['nonmember_tags'] ) && ( ! function_exists( 'pmpro_is_checkout' ) || ! pmpro_is_checkout() ) ) {
		// Registering for site without receiving a level. Add the non-member tags.
		pmpro_mailpoet_add_user_to_tags( $user_id, $options['nonmember_tags'] );
	}
}
add_action( 'user_register', 'pmpro_mailpoet_tags_user_register' );

/**
 * When users change levels, add/remove their level-specific and non-member tags.
 *
 * Tags always reflect the user's current levels. Only tags that are managed by
 * this plugin (i.e. configured in the settings) are ever added or removed, so
 * tags applied manually in MailPoet are never touched.
 *
 * @since TBD
 *
 * @param array $pmpro_old_user_levels Array of users and levels that the user was in before.
 */
function pmpro_mailpoet_tags_after_all_membership_level_changes( $pmpro_old_user_levels ) {
	// Get plugin options.
	$options = pmpro_mailpoet_get_options();

	// Get the full set of tags this plugin manages. If none, there is nothing to do.
	$managed_tags = pmpro_mailpoet_get_managed_tag_ids();
	if ( empty( $managed_tags ) ) {
		return;
	}

	// Loop through all users who have changed levels.
	foreach ( $pmpro_old_user_levels as $user_id => $old_levels ) {
		// Get current tags.
		$current_tags = pmpro_mailpoet_get_user_tag_ids( $user_id );

		// Get the tags the user should have based on their current levels.
		$desired_tags = array();
		$user_levels  = pmpro_getMembershipLevelsForUser( $user_id );
		if ( ! empty( $user_levels ) ) {
			foreach ( $user_levels as $level ) {
				if ( ! empty( $options[ 'level_' . $level->id . '_tags' ] ) ) {
					$desired_tags = array_merge( $desired_tags, $options[ 'level_' . $level->id . '_tags' ] );
				}
			}
		} elseif ( ! empty( $options['nonmember_tags'] ) ) {
			// No active levels. Apply the non-member tags.
			$desired_tags = $options['nonmember_tags'];
		}

		// Remove duplicate tag elements.
		$current_tags = array_unique( $current_tags );
		$desired_tags = array_unique( $desired_tags );

		// Calculate tags to add/remove, only ever touching plugin-managed tags.
		$add_tags    = array_diff( $desired_tags, $current_tags ); // Add desired tags the user doesn't already have.
		$remove_tags = array_diff( array_intersect( $managed_tags, $current_tags ), $desired_tags ); // Remove managed tags the user has but should no longer have.

		// Remove any managed tags that the user had but should no longer have.
		pmpro_mailpoet_remove_user_from_tags( $user_id, $remove_tags );

		// Add any desired tags that the user does not already have.
		pmpro_mailpoet_add_user_to_tags( $user_id, $add_tags );
	}
}
add_action( 'pmpro_after_all_membership_level_changes', 'pmpro_mailpoet_tags_after_all_membership_level_changes', 10, 1 );
