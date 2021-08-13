<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;

use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's General Settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Settings
 */
class PluginSettings extends AbstractSettingsGroup {
	// region INHERITED METHODS

	/**
	 * Returns the settings group's title.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_group_title(): string {
		return \__( 'Plugin Settings', 'linked-orders-for-woocommerce' );
	}

	/**
	 * Returns the settings fields' definition.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array[]
	 */
	public function get_settings_definition(): array {
		return \apply_filters(
			$this->get_hook_tag( 'definition' ),
			array(
				'remove-data-uninstall' => array(
					'title'    => \__( 'Remove all data on uninstallation?', 'linked-orders-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'plugin/remove-data-uninstall' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => \__( 'If enabled, the plugin will remove all database data when removed and you will need to reconfigure everything if you install it again at a later time.', 'linked-orders-for-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Retrieves the value of a general setting, validated.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the general option field to retrieve and validate.
	 *
	 * @return  mixed|void
	 */
	public function get_validated_setting( string $field_id ) {
		$value = $this->get_setting( $field_id );

		switch ( $field_id ) {
			case 'remove-data-uninstall':
				$value = $this->validate( $value, "plugin/$field_id", ValidationTypesEnum::BOOLEAN );
				break;
		}

		return \apply_filters( $this->get_hook_tag( 'validated-setting', array( 'plugin' ) ), $value, $field_id );
	}

	// endregion
}
