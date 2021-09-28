<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;

use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum;
use DWS_LO_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\PluginComponents\WC_AbstractValidatedOptionsGroupFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin-level settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Settings
 */
class PluginSettings extends WC_AbstractValidatedOptionsGroupFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_group_title(): string {
		return \__( 'Plugin Settings', 'linked-orders-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_group_fields_helper(): array {
		return array(
			'remove-data-uninstall' => array(
				'title'    => \__( 'Remove all data on uninstallation?', 'linked-orders-for-woocommerce' ),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'default'  => $this->get_default_value( 'remove-data-uninstall' ),
				'options'  => $this->get_supported_options_trait( 'boolean' ),
				'desc_tip' => \__( 'If enabled, the plugin will remove all database data when removed and you will need to reconfigure everything if you install it again at a later time.', 'linked-orders-for-woocommerce' ),
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
			case 'remove-data-uninstall':
				$value = $this->validate_value( $value, $field_id, ValidationTypesEnum::BOOLEAN );
				break;
		}

		return $value;
	}

	// endregion
}
