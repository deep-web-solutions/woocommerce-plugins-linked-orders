<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;

use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
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
class GeneralSettings extends AbstractSettingsGroup {
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
		return \__( 'General Settings', 'linked-orders-for-woocommerce' );
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
				'max-depth'                => array(
					'title'             => \__( 'Maximum linked orders depth', 'linked-orders-for-woocommerce' ),
					'type'              => 'number',
					'custom_attributes' => array(
						'min'  => 1,
						'max'  => 99,
						'step' => 1,
					),
					'default'           => $this->get_default_value( 'general/max-depth' ),
					'desc_tip'          => \__( 'The maximum number of linked orders levels. Root orders are considered level 0.', 'linked-orders-for-woocommerce' ),
				),
				'autocomplete-descendants' => array(
					'title'    => \__( 'Autocomplete descendant orders?', 'linked-orders-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/autocomplete-descendants' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => \__( 'If enabled, all descendant orders will be set to completed automatically when the parent order is completed.', 'linked-orders-for-woocommerce' ),
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
			case 'max-depth':
				$value = \absint( $this->validate( $value, "general/$field_id", ValidationTypesEnum::INTEGER ) );
				break;
			case 'autocomplete-descendants':
				$value = $this->validate( $value, "plugin/$field_id", ValidationTypesEnum::BOOLEAN );
				break;
		}

		return \apply_filters( $this->get_hook_tag( 'validated-setting', array( 'general' ) ), $value, $field_id );
	}

	// endregion
}
