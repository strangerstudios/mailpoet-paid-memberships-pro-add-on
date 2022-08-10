<?php
/*
 * Add Menu Item for "MailPoet".
 *
 * @since TBD
 */
function pmpro_mailpoet_add_admin_page() {
	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}

	if ( version_compare( PMPRO_VERSION, '2.0' ) >= 0 ) {
		add_submenu_page( 'pmpro-dashboard', __( 'MailPoet', 'pmpro-mailpoet' ), __( 'PMPro MailPoet', 'pmpro-mailpoet' ), 'manage_options', 'pmpro-mailpoet', 'pmpro_mailpoet_render_adminpage' );
	}
}
add_action( 'admin_menu', 'pmpro_mailpoet_add_admin_page', 20 );

/**
 * Add MailPoet settings menu to admin bar.
 *
 * @since TBD
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
			'title'  => esc_html__( 'MailPoet', 'pmpro-mailpoet' ),
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
 * @since TBD
 */
function pmpro_mailpoet_render_adminpage() {
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php esc_html_e( 'Paid Memberships Pro - MailPoet Integration Settings', 'pmpro-mailpoet' ); ?></h2>

		<?php pmpro_mailpoet_admin_warnings(); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'pmpro_mailpoet_options' ); ?>
			<?php do_settings_sections( 'pmpro_mailpoet_options' ); ?>

			<p><br/></p>

			<div class="bottom-buttons">
				<input type="hidden" name="pmpro_mailpoet_options[set]" value="1"/>
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'pmpro-mailpoet' ); ?>">
			</div>

		</form>
	</div>
	<?php
}

/*
 * Set up MailPoet settings.
 *
 * @since TBD
 */
function pmpro_mailpoet_admin_init() {
	register_setting( 'pmpro_mailpoet_options', 'pmpro_mailpoet_options', 'pmpro_mailpoet_options_validate' );
	
	// General Settings.
	add_settings_section( 'pmpro_mailpoet_section_opt_in_lists', esc_html__( 'General Settings', 'pmpro-mailpoet' ), '', 'pmpro_mailpoet_options' );
	add_settings_field( 'pmpro_mailpoet_option_nonmember_lists', esc_html__( 'Non-Member Lists', 'pmpro-mailpoet' ), 'pmpro_mailpoet_option_nonmember_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );
	add_settings_field( 'pmpro_mailpoet_option_opt_in_lists', esc_html__( 'Opt-in Lists', 'pmpro-mailpoet' ), 'pmpro_mailpoet_option_opt_in_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );

	// Membership List Settings.
	add_settings_section( 'pmpro_mailpoet_section_membership_lists', esc_html__( 'Membership Lists', 'pmpro-mailpoet' ), 'pmpro_mailpoet_section_membership_lists', 'pmpro_mailpoet_options' );
	$levels = pmpro_mailpoet_get_all_levels();
	foreach ( $levels as $level ) {
		add_settings_field( 'pmpro_mailpoet_option_memberships_lists_' . (int) $level->id, esc_html( $level->name ), 'pmpro_mailpoet_option_memberships_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_membership_lists', array( $level ) );
	}
	add_settings_field( 'pmpro_mailpoet_option_unsubscribe_on_level_change', esc_html__( 'Unsubscribe on Level Change?', 'pmpro-mailpoet' ), 'pmpro_mailpoet_option_unsubscribe_on_level_change', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_membership_lists' );
}
add_action( 'admin_init', 'pmpro_mailpoet_admin_init' );

/**
 * Validate the MailPoet settings on save.
 *
 * @since TBD
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
 * @since TBD
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
			<p><strong><?php esc_html_e( 'Membership lists lists cannot also be set as opt-in lists.', 'pmpro-mailpoet' ); ?></strong></p>
		</div>
		<?php
	}
}

/**
 * Add description for Membership Lists section.
 *
 * @since TBD
 */
function pmpro_mailpoet_section_membership_lists() {
	?>
	<p><?php esc_html_e( 'Users will automatically be subscribed to selected lists when they receive the corresponding membership level.', 'pmpro-mailpoet' ); ?></p>
	<?php
}

/**
 * Show the membership lists setting for the given level.
 *
 * @since TBD
 *
 * @param object $level The level to show the lists for.
 */
function pmpro_mailpoet_option_memberships_lists( $level ) {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'level_' . (int) $level[0]->id . '_lists' );
}

/**
 * Show the "Non-Member Lists" setting.
 *
 * @since TBD
 */
function pmpro_mailpoet_option_nonmember_lists() {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'nonmember_lists' );
	echo '<p class="description">' . esc_html__( 'Users will automatically be subscribed to non-member lists when they register without purchasing a membership level or when their membership level is removed.', 'pmpro-mailpoet' ) . '</p>';
}

/**
 * Show the "Unsubscribe on Level Change" setting.
 *
 * @since TBD
 */
function pmpro_mailpoet_option_unsubscribe_on_level_change() {
	$options = pmpro_mailpoet_get_options();

	?>
	<select name="pmpro_mailpoet_options[unsubscribe_on_level_change]">
		<option value="0" <?php selected( $options['unsubscribe_on_level_change'], 0 ); ?>><?php esc_html_e( 'No.', 'pmpro-mailpoet' ); ?></option>
		<option value="1" <?php selected( $options['unsubscribe_on_level_change'], 1 ); ?>><?php esc_html_e( 'Yes, unsubscribe from old membership lists on level change.', 'pmpro-mailpoet' ); ?></option>
	</select>
	<?php
}


/**
 * Show the "Opt-in Lists" setting.
 *
 * @since TBD
 */
function pmpro_mailpoet_option_opt_in_lists() {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'opt-in_lists' );
	echo '<p class="description">' . esc_html__( 'Give users the option to subscribe to additional lists at checkout and on their profile page.', 'pmpro-mailpoet' ) . '</p>';

}

/**
 * Helper function to show checkboxes for MailPoet lists.
 *
 * @since TBD
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

	if ( ! empty( $pmpro_mailpoet_lists ) ) {
		?>
		<div 
		<?php
		if ( count( $pmpro_mailpoet_lists ) > 5 ) {
			?>
			class="pmpromailpoet-checkbox-list-scrollable"<?php } ?>>
		<?php
		foreach ( $pmpro_mailpoet_lists as $list ) {
			$checked_modifier = in_array( $list['id'], $selected_lists ) ? ' checked' : '';
			echo( "<input type='checkbox' name='pmpro_mailpoet_options[" . esc_attr( $option_name ) . "][]' value='" . esc_attr( $list['id'] ) . "' id='pmpro_mailpoet_" . esc_attr( $option_name ) . '_' . esc_attr( $list['id'] ) . "'" . $checked_modifier . '>' );
			echo( "<label for='pmpro_mailpoet_" . esc_attr( $option_name ) . '_' . esc_attr( $list['id'] ) . "' class='pmpromailpoet-checkbox-label'>" . esc_html( $list['name'] ) . '</label><br>' );
		}
		echo '</div>';
	} else {
		esc_html_e( 'No lists found.', 'pmpro-mailpoet' );
	}

}
