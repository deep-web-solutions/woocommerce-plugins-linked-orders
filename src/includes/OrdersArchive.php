<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles customizations to the WC orders archive page.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class OrdersArchive extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers actions and filters with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service  Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'woocommerce_admin_order_actions', $this, 'register_view_all_action', 999, 2 );
	}

	// endregion

	// region HOOKS

	/**
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $actions
	 * @param   \WC_Order   $order
	 *
	 * @return  array
	 */
	public function register_view_all_action( array $actions, \WC_Order $order ): array {

		if ( ! empty( $order->get_customer_id() ) ) {
			$actions['view_all'] = array(
				'url'    => \admin_url( 'edit.php?post_type=shop_order&_customer_user=' . $order->get_customer_id() ),
				'name'   => \__( 'Show all customer orders', 'linked-orders-for-woocommerce' ),
				'action' => 'dws_all_customer_orders _blank dws_custom-extensions_action',
			);
		}


		return $actions;
	}

	// endregion
}
