<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Integrations;

use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Integration for the WC Sequential Order Numbers Pro plugin by SkyVerge.
 *
 * @since   1.2.0
 * @version 1.2.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class WCSequentialOrderNumbersPro extends AbstractPluginIntegration {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 */
	public function get_dependent_plugin(): array {
		return array(
			'plugin'        => 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers-pro.php',
			'fallback_name' => 'WooCommerce Sequential Order Numbers Pro',
			'min_version'   => '1.18',
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( dws_lowc_get_hook_tag( 'created_linked_order' ), $this, 'set_sequential_order_number' );
	}

	// endregion

	// region HOOKS

	/**
	 * Sets the proper sequential order number on the newly created order.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \WC_order   $order      The object of the newly created order.
	 *
	 * @return  void
	 */
	public function set_sequential_order_number( \WC_order $order ) {
		if ( \function_exists( 'wc_seq_order_number_pro' ) ) {
			\wc_seq_order_number_pro()->set_sequential_order_number( $order );
		}
	}

	// endregion
}
