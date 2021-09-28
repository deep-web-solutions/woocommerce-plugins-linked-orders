<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;

use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum;
use DWS_LO_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\PluginComponents\WC_AbstractValidatedOptionsGroupFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's General Settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Settings
 */
class GeneralSettings extends WC_AbstractValidatedOptionsGroupFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_group_title(): string {
		return \__( 'General Settings', 'linked-orders-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_group_fields_helper(): array {
		return array(
			'max-depth'                => array(
				'title'             => \__( 'Maximum linked orders depth', 'linked-orders-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'max'  => 99,
					'step' => 1,
				),
				'default'           => $this->get_default_value( 'max-depth' ),
				'desc_tip'          => \__( 'The maximum number of linked orders levels. Root orders are considered level 0.', 'linked-orders-for-woocommerce' ),
			),
			'autocomplete-descendants' => array(
				'title'    => \__( 'Autocomplete descendant orders?', 'linked-orders-for-woocommerce' ),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'default'  => $this->get_default_value( 'autocomplete-descendants' ),
				'options'  => $this->get_supported_options_trait( 'boolean' ),
				'desc_tip' => \__( 'If enabled, all descendant orders will be set to completed automatically when the parent order is completed.', 'linked-orders-for-woocommerce' ),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function validate_option_value_helper( $value, string $field_id ) {
		switch ( $field_id ) {
			case 'max-depth':
				$value = \max( $this->validate( $value, $field_id, ValidationTypesEnum::INTEGER ), 1 );
				break;
			case 'autocomplete-descendants':
				$value = $this->validate_value( $value, $field_id, ValidationTypesEnum::BOOLEAN );
				break;
		}

		return $value;
	}

	// endregion
}
