<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles changes to the lifecylce of WC orders.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class ShopOrder extends AbstractPluginFunctionality {
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
		$hooks_service->add_action( 'woocommerce_order_status_completed', $this, 'maybe_autocomplete_descendants' );
	}

	// endregion

	// region HOOKS

	/**
	 * Maybe autocomplete all descendant orders of the currently completed one.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int     $order_id   The ID of the order that was just completed.
	 */
	public function maybe_autocomplete_descendants( int $order_id ) {
		if ( true !== dws_lowc_is_supported_order( $order_id ) ) {
			return;
		}

		$should_autocomplete = dws_lowc_get_validated_setting( 'autocomplete-descendants', 'general' );
		if ( true !== $should_autocomplete ) {
			return;
		}

		$descendants = dws_lowc_get_orders_tree( $order_id );
		foreach ( $descendants as $descendant_id ) {
			if ( true === dws_lowc_is_supported_order( $descendant_id ) && true === \apply_filters( $this->get_hook_tag( 'should_autocomplete' ), true, $descendant_id ) ) {
				$descendant_order = wc_get_order( $descendant_id );
				$descendant_order->update_status(
					'completed',
					\__( 'Child order autocompleted.', 'linked-orders-for-woocommerce' )
				);
			}
		}
	}

	// endregion
}
