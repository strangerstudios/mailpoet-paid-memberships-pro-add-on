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
	add_settings_field( 'pmpro_mailpoet_option_nonmember_tags', esc_html__( 'Non-Member Tags', 'mailpoet-paid-memberships-pro-add-on' ), 'pmpro_mailpoet_option_nonmember_tags', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );
	add_settings_field( 'pmpro_mailpoet_option_opt_in_lists', esc_html__( 'Opt-in Lists', 'mailpoet-paid-memberships-pro-add-on' ), 'pmpro_mailpoet_option_opt_in_lists', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_opt_in_lists' );

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

	// Membership Tags.
	add_settings_section(
		'pmpro_mailpoet_section_membership_tags',
		'',
		'pmpro_mailpoet_section_membership_tags',
		'pmpro_mailpoet_options',
		array(
			'before_section' => '<div class="pmpro_section">',
			'after_section'  => '</div></div>',
		)
	);
	foreach ( $levels as $level ) {
		add_settings_field( 'pmpro_mailpoet_option_memberships_tags_' . (int) $level->id, esc_html( $level->name ), 'pmpro_mailpoet_option_memberships_tags', 'pmpro_mailpoet_options', 'pmpro_mailpoet_section_membership_tags', array( $level ) );
	}
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

	// Checkboxes of lists and tags to save.
	$mailpoet_lists_settings = array(
		'nonmember_lists',
		'opt-in_lists',
		'nonmember_tags',
	);

	$levels = pmpro_mailpoet_get_all_levels();
	foreach ( $levels as $level ) {
		$mailpoet_lists_settings[] = 'level_' . (int) $level->id . '_lists';
		$mailpoet_lists_settings[] = 'level_' . (int) $level->id . '_tags';
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
			<?php esc_html_e( 'General Settings', 'mailpoet-paid-memberships-pro-add-on' ); ?>
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
			<?php esc_html_e( 'Membership Lists', 'mailpoet-paid-memberships-pro-add-on' ); ?>
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
 * Add description for Membership Tags section.
 *
 * @since 3.4
 */
function pmpro_mailpoet_section_membership_tags() {
	?>
	<div id="pmpro-mailpoet-membership-tags" class="pmpro_section_toggle" data-visibility="hidden" data-activated="false">
		<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
			<span class="dashicons dashicons-arrow-up-alt2"></span>
			<?php esc_html_e( 'Membership Tags', 'mailpoet-paid-memberships-pro-add-on' ); ?>
		</button>
	</div>
	<div class="pmpro_section_inside">
		<p><?php esc_html_e( 'Members will automatically be assigned the selected tags when they receive the corresponding membership level. Tags are removed when the member no longer has the level.', 'mailpoet-paid-memberships-pro-add-on' ); ?></p>
	<?php
}

/**
 * Show the membership tags setting for the given level.
 *
 * @since 3.4
 *
 * @param object $level The level to show the tags for.
 */
function pmpro_mailpoet_option_memberships_tags( $level ) {
	pmpro_mailpoet_settings_build_tag_checkboxes_helper( 'level_' . (int) $level[0]->id . '_tags' );
}

/**
 * Show the "Non-Member Tags" setting.
 *
 * @since 3.4
 */
function pmpro_mailpoet_option_nonmember_tags() {
	pmpro_mailpoet_settings_build_tag_checkboxes_helper( 'nonmember_tags' );
	echo '<p class="description">' . esc_html__( 'Members will automatically be tagged with these tags when they register without a membership level or when their membership level is removed (e.g. on cancellation).', 'mailpoet-paid-memberships-pro-add-on' ) . '</p>';
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
 * Show the "Opt-in Lists" setting.
 *
 * @since 3.0
 */
function pmpro_mailpoet_option_opt_in_lists() {
	pmpro_mailpoet_settings_build_list_checkboxes_helper( 'opt-in_lists' );
	echo '<p class="description">' . esc_html__( 'Give users the option to subscribe to additional lists at checkout and on their profile page.', 'mailpoet-paid-memberships-pro-add-on' ) . '</p>';

}

/**
 * Helper function to show checkboxes for MailPoet lists or tags.
 *
 * @since 3.0
 *
 * @param string $option_name The name of the option to show the checkboxes for.
 * @param array|null $items The items (lists or tags) to show checkboxes for. Defaults to all MailPoet lists.
 */
function pmpro_mailpoet_settings_build_list_checkboxes_helper( $option_name, $items = null ) {
	$pmpro_mailpoet_items = is_array( $items ) ? $items : pmpro_mailpoet_get_all_lists();
	$options              = pmpro_mailpoet_get_options();

	if ( isset( $options[ $option_name ] ) && is_array( $options[ $option_name ] ) ) {
		$selected_items = $options[ $option_name ];
	} else {
		$selected_items = array();
	}

	if ( ! empty( $pmpro_mailpoet_items ) ) { ?>
		<div class="pmpro_checkbox_box <?php echo ( count( $pmpro_mailpoet_items ) > 6 ) ? 'pmpro_scrollable' : ''; ?>">
		<?php
			foreach ( $pmpro_mailpoet_items as $item ) {
				$checked_modifier = in_array( $item['id'], $selected_items ) ? ' checked' : '';
				echo '<div class="pmpro_clickable">';
				echo( "<input type='checkbox' name='pmpro_mailpoet_options[" . esc_attr( $option_name ) . "][]' value='" . esc_attr( $item['id'] ) . "' id='pmpro_mailpoet_" . esc_attr( $option_name ) . '_' . esc_attr( $item['id'] ) . "'" . $checked_modifier . '>' );
				echo( "<label for='pmpro_mailpoet_" . esc_attr( $option_name ) . '_' . esc_attr( $item['id'] ) . "' class='pmpromailpoet-checkbox-label'>" . esc_html( $item['name'] ) . '</label>' );
				echo '</div>';
			}
		?>
		</div> <!-- end pmpro_checkbox_box -->
	<?php } else {
		esc_html_e( 'No lists found.', 'mailpoet-paid-memberships-pro-add-on' );
	}
}

/**
 * Helper function to show checkboxes for MailPoet tags.
 *
 * @since 3.4
 *
 * @param string $option_name The name of the option to show the checkboxes for.
 */
function pmpro_mailpoet_settings_build_tag_checkboxes_helper( $option_name ) {
	$pmpro_mailpoet_tags = pmpro_mailpoet_get_all_tags();
	if ( empty( $pmpro_mailpoet_tags ) ) {
		echo '<p class="description">' . esc_html__( 'No tags found. Add tags in MailPoet to assign them to members.', 'mailpoet-paid-memberships-pro-add-on' ) . '</p>';
		return;
	}
	pmpro_mailpoet_settings_build_list_checkboxes_helper( $option_name, $pmpro_mailpoet_tags );
}
