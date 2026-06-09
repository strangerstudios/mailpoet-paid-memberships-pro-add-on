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
		$new_subscriber = pmpro_mailpoet_get_api()->subscribeToLists( $subscriber['id'], $list_ids );

		// Cache the new subscriber.
		pmpro_mailpoet_get_subscriber( $user_id, $new_subscriber );
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
		$new_subscriber = pmpro_mailpoet_get_api()->unsubscribeFromLists( $subscriber['id'], $list_ids );

		// Cache the new subscriber.
		pmpro_mailpoet_get_subscriber( $user_id, $new_subscriber );
	}
}
