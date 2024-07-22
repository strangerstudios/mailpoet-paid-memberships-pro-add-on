<?php
/**
 * Dispaly additional opt-in list fields on checkout
 *
 * @since 3.0
 */
function pmpro_mailpoet_additional_lists_on_checkout() {
	// If no opt-in lists are set, bail.
	$options = pmpro_mailpoet_get_options();
	if ( empty( $options['opt-in_lists'] ) ) {
		return;
	}

	// Don't show if the user is returning from PayPal Express.
	global $pmpro_review;
	if ( ! empty( $pmpro_review ) ) {
		return;
	}

	// Show the opt-in lists at checkout.
	?>
	<fieldset id="pmpro_mailpoet_additional_lists" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fieldset' ) ); ?>">
		<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card' ) ); ?>">
			<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card_content' ) ); ?>">
				<legend class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_legend' ) ); ?>">
					<h2 class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_heading pmpro_font-large' ) ); ?>"><?php echo esc_html( _n( 'Join Our Mailing List', 'Join Our Mailing Lists', count( $options['opt-in_lists'] ), 'pmpro-mailpoet' ) ); ?></h2>
				</legend>
				<?php
				global $current_user;
				pmpro_mailpoet_show_optin_checkboxes( empty( $current_user->ID ) ? null : $current_user->ID );
				?>
			</div>
		</div>
	</fieldset>
	<?php
}
add_action( 'pmpro_checkout_boxes', 'pmpro_mailpoet_additional_lists_on_checkout', 15 );

/**
 * Preserve info when going off-site for payment w/offsite payment gateway (PayPal Express).
 * Sets Session variables.
 *
 * @since 3.0
 */
function pmpro_mailpoet_pmpro_paypalexpress_session_vars() {
	if ( isset( $_REQUEST['pmpro_mailpoet_opt-in_lists_showing'] ) ) {
		$_SESSION['pmpro_mailpoet_opt-in_lists_showing'] = pmpro_sanitize( $_REQUEST['pmpro_mailpoet_opt-in_lists_showing'] );
		$_SESSION['pmpro_mailpoet_opt-in_lists']         = isset( $_REQUEST['pmpro_mailpoet_opt-in_lists'] ) ? pmpro_sanitize( $_REQUEST['pmpro_mailpoet_opt-in_lists'] ) : array();
	}
}
add_action( 'pmpro_paypalexpress_session_vars', 'pmpro_mailpoet_pmpro_paypalexpress_session_vars' );

/*
	Add opt-in Lists to the user profile/edit user page.
*/
function pmpro_mailpoet_show_optin_list_profile_fields( $user ) {
	// If we are running PMPro 3.0, bail. We will show this info on the Edit Membrer page.
	if ( class_exists( 'PMPro_Subscription' ) ) {
		return;
	}

	// If no opt-in lists are set, bail.
	$options = pmpro_mailpoet_get_options();
	if ( empty( $options['opt-in_lists'] ) ) {
		return;
	}

	// Show opt-in lists setting.
	?>
		<h3><?php esc_html_e( 'Opt-in MailPoet Lists', 'mailpoet-paid-memberships-pro-add-on' ); ?></h3>

		<table class="form-table">
			<tr>
				<th>
					<label><?php esc_html_e( 'Mailing Lists', 'mailpoet-paid-memberships-pro-add-on' ); ?></label>
				</th>
				<td>
					<?php pmpro_mailpoet_show_optin_checkboxes( $user->ID ); ?>
				</td>
			</tr>
		</table>
	<?php
}
add_action( 'show_user_profile', 'pmpro_mailpoet_show_optin_list_profile_fields', 12 );
add_action( 'edit_user_profile', 'pmpro_mailpoet_show_optin_list_profile_fields', 12 );


/*
	Add opt-in Lists to the user profile/edit user page.
*/
function pmpro_mailpoet_show_optin_list_profile_fields_frontend( $user ) {
	// If no opt-in lists are set, bail.
	$options = pmpro_mailpoet_get_options();
	if ( empty( $options['opt-in_lists'] ) ) {
		return;
	}

	// Show opt-in lists setting.
	?>
	<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_spacer' ) ); ?>"></div>
	<fieldset id="pmpro_mailpoet_additional_lists" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fieldset' ) ); ?>">
		<legend class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_legend' ) ); ?>">
			<h2 class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_heading pmpro_font-large' ) ); ?>"><?php echo esc_html( _n( 'Join Our Mailing List', 'Join Our Mailing Lists', count( $options['opt-in_lists'] ), 'pmpro-mailpoet' ) ); ?></h2>
		</legend>
		<?php
		global $current_user;
		pmpro_mailpoet_show_optin_checkboxes( empty( $current_user->ID ) ? null : $current_user->ID );
		?>
	</fieldset>
	<?php
}
add_action( 'pmpro_show_user_profile', 'pmpro_mailpoet_show_optin_list_profile_fields_frontend', 12 );

/**
 * Show opt-in mailing lists checkboxes.
 *
 * @since 3.0
 *
 * @param int|null $user_id User to preset checkboxes for.
 */
function pmpro_mailpoet_show_optin_checkboxes( $user_id = null ) {
	// Get plugin options.
	$options = pmpro_mailpoet_get_options();

	// Get opt-in list IDs.
	$optin_list_ids = ! empty( $options['opt-in_lists'] ) ? $options['opt-in_lists'] : array();

	// Get all lists from MailPoet.
	$all_lists = pmpro_mailpoet_get_all_lists();

	// Get full data for opt-in lists.
	$optin_lists = array();
	foreach ( $all_lists as $list ) {
		if ( in_array( $list['id'], $optin_list_ids ) ) {
			$optin_lists[] = $list;
		}
	}

	// If no opt-in lists, bail.
	if ( empty( $optin_lists ) ) {
		return;
	}

	// Get the user's current lists.
	if ( ! empty( $user_id ) ) {
		$user_list_ids = pmpro_mailpoet_get_user_list_ids( $user_id );
	} else {
		$user_list_ids = array();
	}

	// Show opt-in lists setting.
	?>
	<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fields' ) ); ?>">
		<input type="hidden" name="pmpro_mailpoet_opt-in_lists_showing" value="1" />
		<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field pmpro_form_field pmpro_form_field-checkbox_grouped' ) ); ?>">
			<ul class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_list pmpro_list-plain' ) ); ?>">
				<?php
				foreach ( $optin_lists as $optin_list ) {
					$checked_modifier = ( in_array( $optin_list['id'], $user_list_ids ) ) ? ' checked' : '';
					?>
					<li class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_list_item' ) ); ?>">
						<span class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field-checkbox-grouped-item' ) ); ?>">
							<input name="pmpro_mailpoet_opt-in_lists[]" type="checkbox" value="<?php echo esc_attr( $optin_list['id'] ) . "' id='pmpro_mailpoet_opt-in_lists_" . esc_attr( $optin_list['id'] ); ?>" id="pmpro_mailpoet_opt-in_lists_<?php echo esc_attr( $optin_list['id'] ); ?>" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_input pmpro_form_input-checkbox' ) ); ?>" <?php echo $checked_modifier; ?>>
							<label for="pmpro_mailpoet_opt-in_lists_<?php echo esc_attr( $optin_list['id'] ); ?>" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_label pmpro_form_label-inline pmpro_clickable' ) ); ?>"><?php echo esc_html( $optin_list['name'] ); ?></label>
						</span>
					</li>
					<?php
				}
				?>
			</ul> <!-- end pmpro_list pmpro_list-plain -->
		</div> <!-- end pmpro_form_field -->
	</div> <!-- end pmpro_form_fields -->
	<?php
}

/**
 * Save opt-in mailing lists checkboxes.
 *
 * @since 3.0
 *
 * @param int $user_id User ID to save checkboxes for.
 */
function pmpro_mailpoet_save_optin_list_selections( $user_id ) {
	// Only try to save if opt-in lists were shown.
	if ( empty( $_REQUEST['pmpro_mailpoet_opt-in_lists_showing'] ) && empty( $_SESSION['pmpro_mailpoet_opt-in_lists_showing'] ) ) {
		return;
	}

	// Get plugin options.
	$options = pmpro_mailpoet_get_options();

	// Get all opt-in lists IDs.
	$all_optin_list_ids = ! empty( $options['opt-in_lists'] ) ? $options['opt-in_lists'] : array();

	// Get user's new opt-in lists.
	if ( ! empty( $_REQUEST['pmpro_mailpoet_opt-in_lists_showing'] ) ) {
		// Pull from $_REQUEST.
		$selected_optin_list_ids = ! empty( $_REQUEST['pmpro_mailpoet_opt-in_lists'] ) ? pmpro_sanitize( $_REQUEST['pmpro_mailpoet_opt-in_lists'] ) : array();
	} else {
		// Pull from $_SESSION.
		$selected_optin_list_ids = ! empty( $_SESSION['pmpro_mailpoet_opt-in_lists'] ) ? pmpro_sanitize( $_SESSION['pmpro_mailpoet_opt-in_lists'] ) : array();
	}

	// Get user's current lists.
	$user_list_ids = pmpro_mailpoet_get_user_list_ids( $user_id );

	// Get lists to add.
	$add_lists = array_diff( $selected_optin_list_ids, $user_list_ids ); // Add user to all selected lists that they are not already in.

	// Get lists to remove.
	$remove_lists = array_diff( array_intersect( $all_optin_list_ids, $user_list_ids ), $selected_optin_list_ids ); // Remove user from all opt-in lists that they already have but that where not selected.

	// Remove user from any lists that they were in but should no longer be in.
	pmpro_mailpoet_remove_user_from_lists( $user_id, $remove_lists );

	// Add user to any lists that they are not already in.
	pmpro_mailpoet_add_user_to_lists( $user_id, $add_lists );

	// Clear out the session.
	if ( isset( $_SESSION['pmpro_mailpoet_opt-in_lists_showing'] ) ) {
		unset( $_SESSION['pmpro_mailpoet_opt-in_lists_showing'] );
	}
	if ( isset( $_SESSION['pmpro_mailpoet_opt-in_lists'] ) ) {
		unset( $_SESSION['pmpro_mailpoet_opt-in_lists'] );
	}
}
add_action( 'personal_options_update', 'pmpro_mailpoet_save_optin_list_selections' );
add_action( 'edit_user_profile_update', 'pmpro_mailpoet_save_optin_list_selections' );
add_action( 'pmpro_personal_options_update', 'pmpro_mailpoet_save_optin_list_selections' );
add_action( 'pmpro_after_checkout', 'pmpro_mailpoet_save_optin_list_selections', 15 );

/**
 * Add Mailpoet to edit member panels.
 *
 * @since TBD
 *
 * @param array $panels Array of panels.
 */
function pmpro_mailpoet_member_edit_panels( $panels ) {
	// If no opt-in lists are set, bail. We don't need to show the panel.
	$options = pmpro_mailpoet_get_options();
	if ( empty( $options['opt-in_lists'] ) ) {
		return;
	}

	// If the class doesn't exist and the abstract class does, require the class.
	if ( ! class_exists( 'PMProMailPoet_Member_Edit_Panel' ) && class_exists( 'PMPro_Member_Edit_Panel' ) ) {
		require_once( PMPRO_MAILPOET_DIR . '/classes/pmpromailpoet-class-member-edit-panel.php' );
	}

	// If the class exists, add a panel.
	if ( class_exists( 'PMProMailPoet_Member_Edit_Panel' ) ) {
		$panels[] = new PMProMailPoet_Member_Edit_Panel();
	}

	return $panels;
}
add_filter( 'pmpro_member_edit_panels', 'pmpro_mailpoet_member_edit_panels' );
