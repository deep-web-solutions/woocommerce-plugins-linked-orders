<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles customizations to the WC edit order page.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class Order extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers the WC order meta-box.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service   Instance of the settings service.
	 */
	public function register_settings( SettingsService $settings_service ): void {

	}

	// endregion
}
