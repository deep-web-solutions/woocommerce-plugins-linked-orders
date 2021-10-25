<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings\GeneralSettings;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings\PluginSettings;
use DWS_LO_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\PluginComponents\WC_AbstractValidatedOptionsSectionFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Settings extends WC_AbstractValidatedOptionsSectionFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		return array( GeneralSettings::class, PluginSettings::class );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_options_name_prefix(): string {
		return 'dws-lowc_';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_page_slug(): string {
		return 'dws-linked-orders';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_page_title(): string {
		return \_x( 'Linked Orders', 'settings', 'linked-orders-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_section_parent_slug(): string {
		return 'advanced';
	}

	// endregion
}
