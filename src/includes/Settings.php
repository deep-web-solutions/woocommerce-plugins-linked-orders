<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings\PluginSettings;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Actions\UninstallableInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeValidatedSettingsServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsServiceAwareInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Utilities\ValidatedSettingsServiceAwareTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareInterface;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class Settings extends AbstractPluginFunctionality implements UninstallableInterface, SettingsServiceAwareInterface, ValidationServiceAwareInterface {
	// region TRAITS

	use InitializeValidatedSettingsServiceTrait;
	use ValidatedSettingsServiceAwareTrait;
	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the functionality's children in the plugin tree.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_di_container_children(): array {
		return array( PluginSettings::class );
	}

	/**
	 * Register the admin settings on a dedicated WC settings section.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service       Instance of the settings service.
	 */
	protected function register_settings( SettingsService $settings_service ): void {
		$settings_service->register_submenu_page(
			'advanced',
			'',
			function() {
				return \_x( 'Linked Orders', 'settings', 'linked-orders-for-woocommerce' );
			},
			'dws-linked-orders',
			'manage_woocommerce',
			array(),
			'woocommerce'
		);
	}

	/**
	 * Retrieves a setting field's value in a raw format.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the field within the settings to read from the database.
	 *
	 * @return  mixed
	 */
	public function get_setting( string $field_id ) {
		return \apply_filters( $this->get_hook_tag( 'setting' ), null, $field_id );
	}

	/**
	 * Retrieves a setting field's value and runs it through a validation callback.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the field within the settings to read from the database.
	 *
	 * @return  mixed
	 */
	public function get_validated_setting( string $field_id ) {
		return \apply_filters( $this->get_hook_tag( 'validated-setting' ), null, $field_id );
	}

	// endregion

	// region INSTALLATION

	/**
	 * Removes all the plugin's options from the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  UninstallFailureException|null
	 */
	public function uninstall(): ?UninstallFailureException {
		$remove_data = dws_wc_lo_get_validated_setting( 'plugin_remove-data-uninstall' );

		if ( true === $remove_data ) {
			$result = $GLOBALS['wpdb']->query( "DELETE FROM {$GLOBALS['wpdb']->options} WHERE option_name LIKE 'dws-wc-linked-orders_%'" ); // phpcs:ignore
			if ( false === $result ) {
				return new UninstallFailureException( \__( 'Failed to delete the plugin options from the database', 'linked-orders-for-woocommerce' ) );
			}
		}

		return null;
	}

	// endregion
}
