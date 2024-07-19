<?php

class PMProMailPoet_Member_Edit_Panel extends PMPro_Member_Edit_Panel {
	/**
	 * Set up the panel.
	 */
	public function __construct() {
		$this->slug = 'pmpro-mailpoet';
		$this->title = esc_html__( 'MailPoet Opt-In Lists', 'pmpro-mailpoet' );
		$this->submit_text = current_user_can( 'edit_users' ) ? __( 'Update Opt-In Lists', 'paid-memberships-pro' ) : '';
	}

	/**
	 * Display the panel contents.
	 */
	protected function display_panel_contents() {
		// Get the user being edited.
		$user = self::get_user();

		?>
		<fieldset id="pmpro_mailpoet_additional_lists" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fieldset' ) ); ?>">
			<?php
			pmpro_mailpoet_show_optin_checkboxes( $user->ID );
			?>
		</fieldset>
		<?php
	}

	/**
	 * Save panel data.
	 */
	public function save() {
		// Save the user's opt-in list preferences.
		pmpro_mailpoet_save_optin_list_selections( self::get_user()->ID );
	}
}
