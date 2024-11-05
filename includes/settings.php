<?php
/*
 * Add Menu Item for "MailPoet".
 *
 * @since 3.0
 */
function pmpro_mailpoet_add_admin_page() {
	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}

	if ( version_compare( PMPRO_VERSION, '2.0' ) >= 0 ) {
		add_submenu_page( 'pmpro-dashboard', __( 'MailPoet', 'mailpoet-paid-memberships-pro-add-on' ), __( 'PMPro MailPoet', 'mailpoet-paid-memberships-pro-add-on' ), 'manage_options', 'pmpro-mailpoet', 'pmpro_mailpoet_render_adminpage' );
	}
}
add_action( 'admin_menu', 'pmpro_mailpoet_add_admin_page', 20 );

/**
 * Add MailPoet settings menu to admin bar.
 *
 * @since 3.0
 */
function pmpro_mailpoet_admin_bar_menu() {
	global $wp_admin_bar;
	if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
		return;
	}
	$wp_admin_bar->add_menu(
		array(
			'id'     => 'pmpro-mailpoet',
			'parent' => 'paid-memberships-pro',
			'title'  => esc_html__( 'MailPoet', 'mailpoet-paid-memberships-pro-add-on' ),
			'href'   => get_admin_url(
				null,
				'/admin.php?page=pmpro-mailpoet'
			),
		)
	);
}
add_action( 'admin_bar_menu', 'pmpro_mailpoet_admin_bar_menu', 1000 );

/**
 * Render the MailPoet settings page.
 *
 * @since 3.0
 */
function pmpro_mailpoet_render_adminpage() {
	?>
	<div class="wrap pmpro_admin pmpro_admin-pmpro-mailpoet">
		<h1><?php esc_html_e( 'MailPoet Integration Settings', 'mailpoet-paid-memberships-pro-add-on' ); ?></h2>

		<?php pmpro_mailpoet_admin_warnings(); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'pmpro_mailpoet_options' ); ?>
			<?php do_settings_sections( 'pmpro_mailpoet_options' ); ?>

			<p class="submit">
				<input type="hidden" name="pmpro_mailpoet_options[set]" value="1"/>
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'mailpoet-paid-memberships-pro-add-on' ); ?>">
			</p>

		</form>
	</div>
	<?php
}

/*
 * Set up MailPoet settings.
 *
 * @since 3.0
 */
function pmpro_mailpoet_admin_init() {
	register_setting( 'pmpro_mailpoet_options', 'pmpro_mailpoet_options', 'pmpro_mailpoet_options_validate' );
	
	// General Settings.
	add_settings_section(
		'pmpro_mailpoet_section_opt_in_lists',
		'',
		'pmpro_mailpoet_section_general_lists',
		'pmpro_mailpoet_options',
		array(
			'before_section' => '<div class="pmpro_section">',
			'after_section' => '</div></div>',
		)
	);
	add_settings_field( 'pmpro_mailpoet_option_nonmember_lists', esc_html__( 'Non-Member Lists', 'mailpoet-paid-memberships-pro-add-on' ), 'pmpro_mailpoet_option_nonmember_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );
	add_settings_field( 'pmpro_mailpoet_option_opt_in_lists', esc_html__( 'Opt-in Lists', 'mailpoet-paid-memberships-pro-add-on' ), 'pmpro_mailpoet_option_opt_in_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );

	//SendWP Email Deliverability.
	add_settings_field( 'pmpro_mailpoet_sendwp_cta', 'Email Deliverability', 'pmpro_mailpoet_sendwp_cta', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );

	add_settings_section(
		'pmpro_mailpoet_section_membership_lists',
		'',
		'pmpro_mailpoet_section_membership_lists',
		'pmpro_mailpoet_options',
		array(
			'before_section' => '<div class="pmpro_section">',
			'after_section' => '</div></div>'
		)
	);
	$levels = pmpro_mailpoet_get_all_levels();
	foreach ( $levels as $level ) {
		add_settings_field( 'pmpro_mailpoet_option_memberships_lists_' . (int) $level->id, esc_html( $level->name ), 'pmpro_mailpoet_option_memberships_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_membership_lists', array( $level ) );
	}
	add_settings_field( 'pmpro_mailpoet_option_unsubscribe_on_level_change', esc_html__( 'Unsubscribe on Level Change?', 'mailpoet-paid-memberships-pro-add-on' ), 'pmpro_mailpoet_option_unsubscribe_on_level_change', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_membership_lists' );	
}
add_action( 'admin_init', 'pmpro_mailpoet_admin_init' );

/**
 * Validate the MailPoet settings on save.
 *
 * @since 3.0
 *
 * @param array $input The input to validate.
 * @return array The validated input.
 */
function pmpro_mailpoet_options_validate( $input ) {
	$newinput = array();

	// Unsubscribe on level change.
	$newinput['unsubscribe_on_level_change'] = isset( $input['unsubscribe_on_level_change'] ) ? preg_replace( '[^a-zA-Z0-9\-]', '', $input['unsubscribe_on_level_change'] ) : null;

	// Checkboxes of lists to save.
	$mailpoet_lists_settings = array(
		'nonmember_lists',
		'opt-in_lists',
	);

	$levels = pmpro_mailpoet_get_all_levels();
	foreach ( $levels as $level ) {
		$mailpoet_lists_settings[] = 'level_' . (int) $level->id . '_lists';
	}

	foreach ( $mailpoet_lists_settings as $setting ) {
		if ( ! empty( $input[ $setting ] ) && is_array( $input[ $setting ] ) ) {
			$count = count( $input[ $setting ] );
			for ( $i = 0; $i < $count; $i++ ) {
				$newinput[ $setting ][] = trim( preg_replace( '[^a-zA-Z0-9\-]', '', $input[ $setting ][ $i ] ) );
			}
		}
	}

	return $newinput;
}

/**
 * Show any warnings on PMPro MailPoet settings page.
 *
 * @since 3.0
 */
function pmpro_mailpoet_admin_warnings() {
	$levels     = pmpro_mailpoet_get_all_levels();
	$options    = pmpro_mailpoet_get_options();
	$show_error = false;

	if ( empty( $options['opt-in_lists'] ) ) {
		return;
	}

	foreach ( $levels as $level ) {
		if ( ! empty( $options[ 'level_' . $level->id . '_lists' ] ) && ! empty( array_intersect( $options['opt-in_lists'], $options[ 'level_' . $level->id . '_lists' ] ) ) ) {
			$show_error = true;
		}
	}

	if ( ! empty( $options['nonmember_lists'] ) && ! empty( array_intersect( $options['opt-in_lists'], $options['nonmember_lists'] ) ) ) {
		$show_error = true;
	}

	if ( $show_error ) {
		?>
		<div class="notice notice-error">
			<p><strong><?php esc_html_e( 'Membership lists lists cannot also be set as opt-in lists.', 'mailpoet-paid-memberships-pro-add-on' ); ?></strong></p>
		</div>
		<?php
	}
}

/**
 * Add description for General Settings section.
 *
 * @since 3.2
 */
function pmpro_mailpoet_section_general_lists() {
	?>
	<div id="pmpro-mailpoet-general-lists" class="pmpro_section_toggle" data-visibility="hidden" data-activated="false">
		<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
			<span class="dashicons dashicons-arrow-up-alt2"></span>
			<?php esc_html_e( 'General Settings', 'pmpro-mailpoet' ); ?>
		</button>
	</div>
	<div class="pmpro_section_inside">
	<?php
}

/**
 * Add description for Membership Lists section.
 *
 * @since 3.0
 */
function pmpro_mailpoet_section_membership_lists() {
	?>
	<div id="pmpro-mailpoet-membership-lists" class="pmpro_section_toggle" data-visibility="hidden" data-activated="false">
		<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
			<span class="dashicons dashicons-arrow-up-alt2"></span>
			<?php esc_html_e( 'Membership Lists', 'pmpro-mailpoet' ); ?>
		</button>
	</div>
	<div class="pmpro_section_inside">
		<p><?php esc_html_e( 'Users will automatically be subscribed to selected lists when they receive the corresponding membership level.', 'mailpoet-paid-memberships-pro-add-on' ); ?></p>
	<?php
}

/**
 * Show the membership lists setting for the given level.
 *
 * @since 3.0
 *
 * @param object $level The level to show the lists for.
 */
function pmpro_mailpoet_option_memberships_lists( $level ) {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'level_' . (int) $level[0]->id . '_lists' );
}

/**
 * Show the "Non-Member Lists" setting.
 *
 * @since 3.0
 */
function pmpro_mailpoet_option_nonmember_lists() {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'nonmember_lists' );
	echo '<p class="description">' . esc_html__( 'Users will automatically be subscribed to non-member lists when they register without purchasing a membership level or when their membership level is removed.', 'mailpoet-paid-memberships-pro-add-on' ) . '</p>';
}

/**
 * Show the "Unsubscribe on Level Change" setting.
 *
 * @since 3.0
 */
function pmpro_mailpoet_option_unsubscribe_on_level_change() {
	$options = pmpro_mailpoet_get_options();

	?>
	<select name="pmpro_mailpoet_options[unsubscribe_on_level_change]">
		<option value="0" <?php selected( $options['unsubscribe_on_level_change'], 0 ); ?>><?php esc_html_e( 'No.', 'mailpoet-paid-memberships-pro-add-on' ); ?></option>
		<option value="1" <?php selected( $options['unsubscribe_on_level_change'], 1 ); ?>><?php esc_html_e( 'Yes, unsubscribe from old membership lists on level change.', 'mailpoet-paid-memberships-pro-add-on' ); ?></option>
	</select>
	<?php
}

/**
 * Add SendWP connectivity settings callback. Copied  logic from PMPro core.
 *
 * @since 3.0
 */
function pmpro_mailpoet_sendwp_cta() {
	?>			
	<p><?php
			$allowed_email_troubleshooting_html = array (
				'a' => array (
					'href' => array(),
					'target' => array(),
					'title' => array(),
					'rel' => array(),
				),
				'em' => array(),
			);
			echo sprintf( wp_kses( __( 'If you are having issues with email delivery from your server, <a href="%s" title="Paid Memberships Pro - Subscription Delays Add On" target="_blank" rel="nofollow noopener">please read our email troubleshooting guide</a>. As an alternative, Paid Memberships Pro offers built-in integration for SendWP. <em>Optional: SendWP is a third-party service for transactional email in WordPress. <a href="%s" title="Documentation on SendWP and Paid Memberships Pro" target="_blank" rel="nofollow noopener">Click here to learn more about SendWP and Paid Memberships Pro</a></em>.', 'mailpoet-paid-memberships-pro-add-on' ), $allowed_email_troubleshooting_html ), 'https://www.paidmembershipspro.com/troubleshooting-email-issues-sending-sent-spam-delivery-delays/?utm_source=plugin&utm_medium=pmpro-emailsettings&utm_campaign=blog&utm_content=email-troubleshooting', 'https://www.paidmembershipspro.com/documentation/member-communications/email-delivery-sendwp/?utm_source=plugin&utm_medium=pmpro-emailsettings&utm_campaign=documentation&utm_content=sendwp' );
		?></p>

		<?php
			// Check to see if connected or not.
			$sendwp_connected = function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() ? true : false;

			if ( ! $sendwp_connected ) { ?>
				<p><button id="pmpro-sendwp-connect" class="button"><?php esc_html_e( 'Connect to SendWP', 'mailpoet-paid-memberships-pro-add-on' ); ?></button></p>
			<?php } else { ?>
				<p><button id="pmpro-sendwp-disconnect" class="button-primary"><?php esc_html_e( 'Disconnect from SendWP', 'mailpoet-paid-memberships-pro-add-on' ); ?></button></p>
				<?php
				// Update SendWP status to see if email forwarding is enabled or not.
				$sendwp_email_forwarding = function_exists( 'sendwp_forwarding_enabled' ) && sendwp_forwarding_enabled() ? true : false;
				
				// Messages for connected or not.
				$connected = __( 'Your site is connected to SendWP.', 'mailpoet-paid-memberships-pro-add-on' ) . " <a href='https://app.sendwp.com/dashboard/' target='_blank' rel='nofollow noopener'>" . __( 'View Your SendWP Account', 'mailpoet-paid-memberships-pro-add-on' ) . "</a>";
				$disconnected = ' ' . sprintf( __( 'Please enable email sending inside %s.', 'mailpoet-paid-memberships-pro-add-on' ), '<a href="' . admin_url('/tools.php?page=sendwp') . '">SendWP Settings</a>' );
				?>
				<p class="description" id="pmpro-sendwp-description"><?php echo $sendwp_email_forwarding ? $connected : $disconnected; ?></p>
			<?php }
		?>
	<?php
}

/**
 * Show the "Opt-in Lists" setting.
 *
 * @since 3.0
 */
function pmpro_mailpoet_option_opt_in_lists() {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'opt-in_lists' );
	echo '<p class="description">' . esc_html__( 'Give users the option to subscribe to additional lists at checkout and on their profile page.', 'mailpoet-paid-memberships-pro-add-on' ) . '</p>';

}

/**
 * Helper function to show checkboxes for MailPoet lists.
 *
 * @since 3.0
 *
 * @param string $option_name The name of the option to show the checkboxes for.
 */
function pmpro_mailpoet_settings_build_list_checkboxes_helper( $option_name ) {
	$pmpro_mailpoet_lists = pmpro_mailpoet_get_all_lists();
	$options              = pmpro_mailpoet_get_options();

	if ( isset( $options[ $option_name ] ) && is_array( $options[ $option_name ] ) ) {
		$selected_lists = $options[ $option_name ];
	} else {
		$selected_lists = array();
	}

	if ( ! empty( $pmpro_mailpoet_lists ) ) { ?>
		<div class="pmpro_checkbox_box <?php echo ( count( $pmpro_mailpoet_lists ) > 6 ) ? 'pmpro_scrollable' : ''; ?>">
		<?php
			foreach ( $pmpro_mailpoet_lists as $list ) {
				$checked_modifier = in_array( $list['id'], $selected_lists ) ? ' checked' : '';
				echo '<div class="pmpro_clickable">';
				echo( "<input type='checkbox' name='pmpro_mailpoet_options[" . esc_attr( $option_name ) . "][]' value='" . esc_attr( $list['id'] ) . "' id='pmpro_mailpoet_" . esc_attr( $option_name ) . '_' . esc_attr( $list['id'] ) . "'" . $checked_modifier . '>' );
				echo( "<label for='pmpro_mailpoet_" . esc_attr( $option_name ) . '_' . esc_attr( $list['id'] ) . "' class='pmpromailpoet-checkbox-label'>" . esc_html( $list['name'] ) . '</label>' );
				echo '</div>';
			}
		?>
		</div> <!-- end pmpro_checkbox_box -->
	<?php } else {
		esc_html_e( 'No lists found.', 'mailpoet-paid-memberships-pro-add-on' );
	}
}
