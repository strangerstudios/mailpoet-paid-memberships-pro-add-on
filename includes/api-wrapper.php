<?php
/**
 * Get the MailPoet API.
 *
 * @since 3.0
 *
 * @return object|null MailPoet API object or null if not available.
 */
function pmpro_mailpoet_get_api() {
	return class_exists( \MailPoet\API\API::class ) ? \MailPoet\API\API::MP('v1') : null;
}

/**
 * Log a MailPoet API error without interrupting the request.
 *
 * The MailPoet API can throw exceptions during otherwise-successful operations
 * (e.g. a list change succeeds but the confirmation email fails to send). We
 * never want such a failure to produce a fatal error during checkout or a
 * level change, so callers catch the exception and log it here instead.
 *
 * @since 3.4
 *
 * @param string $message The error message to log.
 */
function pmpro_mailpoet_log_api_error( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'PMPro MailPoet: ' . $message );
	}
}

/**
 * Get all MailPoet lists.
 *
 * @since 3.0
 *
 * @return array
 */
function pmpro_mailpoet_get_all_lists() {
	// Check to see if we have already retrieved the lists.
	static $cached;
	if ( is_array( $cached ) ) {
		return $cached;
	}

	// We don't have the lists yet. Get them and cache them.
	$mailpoet_api = pmpro_mailpoet_get_api();
	$cached = empty( $mailpoet_api ) ? array() : $mailpoet_api->getLists();

	// Remove trashed lists from results.
	foreach( $cached as $key => $list ) {
		if ( $list['deleted_at'] ) {
			unset( $cached[$key] );
		}
	}

	return $cached;
}

/**
 * Get the MailPoet Subsciber for a user.
 *
 * @since 3.0
 *
 * @param int $user_id The user to get the Subscriber for.
 * @param null|array $updated_subscriber Pass a subscriber array to update the cache.
 * @return array The Subscriber data or an empty array if not available.
 */
function pmpro_mailpoet_get_subscriber( $user_id, $updated_subscriber = null ) {
	static $cache;
	if ( empty( $cache ) ) {
		$cache = array();
	}

	// If we have an updated subscriber, update the cache.
	if ( ! empty( $updated_subscriber ) ) {
		$cache[ $user_id ] = $updated_subscriber;
	}

	// Return the cached subscriber if we have one.
	if ( isset( $cache[ $user_id ] ) ) {
		return $cache[ $user_id ];
	}

	// Get the MailPoet API.
	$mailpoet_api = pmpro_mailpoet_get_api();
	if ( empty( $mailpoet_api ) ) {
		// MailPoet API not available.
		$cache[ $user_id ] = array();
		return $cache[ $user_id ];
	}

	// Get the email address for the user.
	$user = get_userdata( $user_id );
	if ( empty( $user->user_email ) ) {
		$cache[ $user_id ] = array();
		return $cache[ $user_id ];
	}

	// Get the user's lists.
	$cache[ $user_id ] = $mailpoet_api->getSubscriber( $user->user_email );
	return $cache[ $user_id ];
}

/**
 * Get all MailPoet lists that a user is subscribed to.
 *
 * @since 3.0
 *
 * @param int $user_id The user to get lists for.
 * @return array An array of list ids that the user is subscribed to.
 */
function pmpro_mailpoet_get_user_list_ids( $user_id ) {
	$subscriber = pmpro_mailpoet_get_subscriber( $user_id );
	$user_lists = ! empty( $subscriber['subscriptions'] ) ? $subscriber['subscriptions'] : array();
	$subscribed_list_ids = array();
	foreach ( $user_lists as $list ) {
		if ( $list['status'] === 'subscribed' ) {
			$subscribed_list_ids[] = $list['segment_id'];
		}
	}
	return $subscribed_list_ids;
}

/**
 * Add user to MailPoet list.
 *
 * @since 3.0
 *
 * @param int $user_id The user to add to the list.
 * @param int[] $list_ids The list to add the user to.
 */
function pmpro_mailpoet_add_user_to_lists( $user_id, $list_ids ) {
	if ( ! is_array( $list_ids ) || empty( $list_ids ) ) {
		return;
	}

	$subscriber = pmpro_mailpoet_get_subscriber( $user_id );
	if ( ! empty( $subscriber['id'] ) ) {
		// Since we already have the subscriber, we know that API is available.
		try {
			$new_subscriber = pmpro_mailpoet_get_api()->subscribeToLists( $subscriber['id'], $list_ids );

			// Cache the new subscriber.
			pmpro_mailpoet_get_subscriber( $user_id, $new_subscriber );
		} catch ( \Exception $e ) {
			// Log the error but don't break the request (e.g. checkout). The MailPoet API
			// can throw if a confirmation email fails to send even though the list change succeeded.
			pmpro_mailpoet_log_api_error( sprintf( 'Error subscribing user %d to lists: %s', $user_id, $e->getMessage() ) );
		}
	} else {
		// No MailPoet subscriber exists for this user yet, so there is nothing to subscribe.
		// Subscribers are created by MailPoet's own WordPress user sync, not by this plugin.
		pmpro_mailpoet_log_api_error( sprintf( 'No MailPoet subscriber found for user %d; skipping list subscribe. Ensure MailPoet is set to sync WordPress users.', $user_id ) );
	}
}

/**
 * Remove user from MailPoet list.
 *
 * @since 3.0
 *
 * @param int $user_id The user to remove from the list.
 * @param int[] $list_ids The list to remove the user from.
 */
function pmpro_mailpoet_remove_user_from_lists( $user_id, $list_ids ) {
	if ( ! is_array( $list_ids ) || empty( $list_ids ) ) {
		return;
	}

	$subscriber = pmpro_mailpoet_get_subscriber( $user_id );
	if ( ! empty( $subscriber['id'] ) ) {
		// Since we already have the subscriber, we know that API is available.
		try {
			$new_subscriber = pmpro_mailpoet_get_api()->unsubscribeFromLists( $subscriber['id'], $list_ids );

			// Cache the new subscriber.
			pmpro_mailpoet_get_subscriber( $user_id, $new_subscriber );
		} catch ( \Exception $e ) {
			// Log the error but don't break the request (e.g. checkout).
			pmpro_mailpoet_log_api_error( sprintf( 'Error unsubscribing user %d from lists: %s', $user_id, $e->getMessage() ) );
		}
	} else {
		// No MailPoet subscriber exists for this user, so there is nothing to unsubscribe.
		pmpro_mailpoet_log_api_error( sprintf( 'No MailPoet subscriber found for user %d; skipping list unsubscribe.', $user_id ) );
	}
}

/**
 * Get all MailPoet tags.
 *
 * @since 3.4
 *
 * @return array
 */
function pmpro_mailpoet_get_all_tags() {
	// Check to see if we have already retrieved the tags.
	static $cached;
	if ( is_array( $cached ) ) {
		return $cached;
	}

	// We don't have the tags yet. Get them and cache them.
	$mailpoet_api = pmpro_mailpoet_get_api();
	$cached = empty( $mailpoet_api ) ? array() : $mailpoet_api->getTags();

	return $cached;
}

/**
 * Get all MailPoet tag IDs that a user is tagged with.
 *
 * @since 3.4
 *
 * @param int $user_id The user to get tags for.
 * @return array An array of tag ids that the user is tagged with.
 */
function pmpro_mailpoet_get_user_tag_ids( $user_id ) {
	$subscriber = pmpro_mailpoet_get_subscriber( $user_id );
	$user_tags  = ! empty( $subscriber['tags'] ) ? $subscriber['tags'] : array();
	$tag_ids    = array();
	foreach ( $user_tags as $tag ) {
		if ( ! empty( $tag['tag_id'] ) ) {
			$tag_ids[] = $tag['tag_id'];
		}
	}
	return $tag_ids;
}

/**
 * Add tags to a user.
 *
 * @since 3.4
 *
 * @param int $user_id The user to add tags to.
 * @param int[] $tag_ids The tags to add to the user.
 */
function pmpro_mailpoet_add_user_to_tags( $user_id, $tag_ids ) {
	if ( ! is_array( $tag_ids ) || empty( $tag_ids ) ) {
		return;
	}

	$subscriber = pmpro_mailpoet_get_subscriber( $user_id );
	if ( ! empty( $subscriber['id'] ) ) {
		// Since we already have the subscriber, we know that API is available.
		// MailPoet tags one subscriber at a time, so loop through the tags.
		$new_subscriber = null;
		foreach ( $tag_ids as $tag_id ) {
			try {
				$new_subscriber = pmpro_mailpoet_get_api()->tagSubscriber( $subscriber['id'], $tag_id );
			} catch ( \Exception $e ) {
				// Log the error but don't break the request (e.g. checkout) or skip the remaining tags.
				pmpro_mailpoet_log_api_error( sprintf( 'Error adding tag %s to user %d: %s', $tag_id, $user_id, $e->getMessage() ) );
			}
		}

		// Cache the latest subscriber response. After a partial failure this is the response
		// from the last successful tagSubscriber() call, so the cache may lag the true state
		// by one tag until the next lookup refreshes it.
		if ( ! empty( $new_subscriber ) ) {
			pmpro_mailpoet_get_subscriber( $user_id, $new_subscriber );
		}
	} else {
		// No MailPoet subscriber exists for this user yet, so the tags cannot be applied.
		// Subscribers are created by MailPoet's own WordPress user sync, not by this plugin.
		pmpro_mailpoet_log_api_error( sprintf( 'No MailPoet subscriber found for user %d; skipping tag add. Ensure MailPoet is set to sync WordPress users.', $user_id ) );
	}
}

/**
 * Remove tags from a user.
 *
 * @since 3.4
 *
 * @param int $user_id The user to remove tags from.
 * @param int[] $tag_ids The tags to remove from the user.
 */
function pmpro_mailpoet_remove_user_from_tags( $user_id, $tag_ids ) {
	if ( ! is_array( $tag_ids ) || empty( $tag_ids ) ) {
		return;
	}

	$subscriber = pmpro_mailpoet_get_subscriber( $user_id );
	if ( ! empty( $subscriber['id'] ) ) {
		// Since we already have the subscriber, we know that API is available.
		// MailPoet untags one subscriber at a time, so loop through the tags.
		$new_subscriber = null;
		foreach ( $tag_ids as $tag_id ) {
			try {
				$new_subscriber = pmpro_mailpoet_get_api()->untagSubscriber( $subscriber['id'], $tag_id );
			} catch ( \Exception $e ) {
				// Log the error but don't break the request (e.g. checkout) or skip the remaining tags.
				pmpro_mailpoet_log_api_error( sprintf( 'Error removing tag %s from user %d: %s', $tag_id, $user_id, $e->getMessage() ) );
			}
		}

		// Cache the latest subscriber response. After a partial failure this is the response
		// from the last successful untagSubscriber() call, so the cache may lag the true state
		// by one tag until the next lookup refreshes it.
		if ( ! empty( $new_subscriber ) ) {
			pmpro_mailpoet_get_subscriber( $user_id, $new_subscriber );
		}
	} else {
		// No MailPoet subscriber exists for this user, so there are no tags to remove.
		pmpro_mailpoet_log_api_error( sprintf( 'No MailPoet subscriber found for user %d; skipping tag removal.', $user_id ) );
	}
}

/**
 * Get all MailPoet tag IDs that are managed by this plugin.
 *
 * Used to ensure that we only ever add or remove tags that are configured in
 * the plugin settings. Tags applied manually in MailPoet are never touched.
 *
 * @since 3.4
 *
 * @return array An array of tag ids referenced in any level or non-member tag setting.
 */
function pmpro_mailpoet_get_managed_tag_ids() {
	$options      = pmpro_mailpoet_get_options();
	$managed_tags = array();

	// Non-member tags.
	if ( ! empty( $options['nonmember_tags'] ) && is_array( $options['nonmember_tags'] ) ) {
		$managed_tags = array_merge( $managed_tags, $options['nonmember_tags'] );
	}

	// Per-level tags.
	$levels = pmpro_mailpoet_get_all_levels();
	foreach ( $levels as $level ) {
		$key = 'level_' . (int) $level->id . '_tags';
		if ( ! empty( $options[ $key ] ) && is_array( $options[ $key ] ) ) {
			$managed_tags = array_merge( $managed_tags, $options[ $key ] );
		}
	}

	return array_unique( $managed_tags );
}
