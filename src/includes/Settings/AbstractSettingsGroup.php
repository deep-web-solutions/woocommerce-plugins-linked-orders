<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeValidatedSettingsServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsServiceAwareInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsServiceAwareTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Template to encapsulate the most often needed functionalities for registering a group of options on the plugin's WC section.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Settings
 */
abstract class AbstractSettingsGroup extends AbstractPluginFunctionality implements SettingsServiceAwareInterface, ValidationServiceAwareInterface {
	// region TRAITS

	use InitializeValidatedSettingsServiceTrait;
	use SettingsServiceAwareTrait;
	use SetupHooksTrait;
	use SetupSettingsTrait;
	use ValidationServiceAwareTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers actions and filters with the hooks service instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( $this->get_container_entry( 'settings' )->get_hook_tag( 'setting' ), $this, 'maybe_get_raw_setting', 10, 2 );
		$hooks_service->add_filter( $this->get_container_entry( 'settings' )->get_hook_tag( 'validated-setting' ), $this, 'maybe_get_validated_setting', 10, 2 );
	}

	/**
	 * Registers the general settings options group with WC.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service   Instance of the settings service.
	 */
	public function register_settings( SettingsService $settings_service ): void {
		$settings_service->register_options_group(
			'dws-wc-linked-orders_' . $this->get_settings_group_slug(),
			array( $this, 'get_group_title' ),
			array( $this, 'get_settings_definition' ),
			'advanced',
			array( 'section' => 'dws-linked-orders' ),
			'woocommerce'
		);
	}

	/**
	 * Retrieves a setting field's value in raw format.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the settings' field to retrieve.
	 *
	 * @return  mixed
	 */
	public function get_setting( string $field_id ) {
		return $this->get_option( $field_id, 'dws-wc-linked-orders_' . $this->get_settings_group_slug(), array( 'default' => null ), 'woocommerce' );
	}

	// endregion

	// region HOOKS

	/**
	 * Retrieves a setting field's value in raw format.
	 *
	 * @param   mixed       $value      The value so far.
	 * @param   string      $field_id   The database ID of the setting.
	 *
	 * @return  mixed
	 */
	public function maybe_get_raw_setting( $value, string $field_id ) {
		return Strings::starts_with( $field_id, $this->get_settings_group_slug() . '_' )
			? $this->get_setting( substr( $field_id, strlen( $this->get_settings_group_slug() ) + 1 ) )
			: $value;
	}

	/**
	 * Retrieves a setting field's value and runs it through a validation callback.
	 *
	 * @param   mixed       $value      The value so far.
	 * @param   string      $field_id    The database ID of the setting.
	 *
	 * @return  mixed
	 */
	public function maybe_get_validated_setting( $value, string $field_id ) {
		return Strings::starts_with( $field_id, $this->get_settings_group_slug() . '_' )
			? $this->get_validated_setting( substr( $field_id, strlen( $this->get_settings_group_slug() ) + 1 ) )
			: $value;
	}

	// endregion

	// region METHODS

	/**
	 * Returns the settings group's slug suffix.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_settings_group_slug(): string {
		return \str_replace( '_settings', '', self::get_safe_name() );
	}

	/**
	 * Returns the settings group's title.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	abstract public function get_group_title(): string;

	/**
	 * Returns the settings fields' definition.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array[]
	 */
	abstract public function get_settings_definition(): array;

	/**
	 * Retrieves the value of a setting, validated.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the general option field to retrieve and validate.
	 *
	 * @return  mixed
	 */
	abstract public function get_validated_setting( string $field_id );

	// endregion
}
