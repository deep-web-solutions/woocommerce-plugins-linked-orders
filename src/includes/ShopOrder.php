<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeAdminNoticesServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticeTypesEnum;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Helpers\AdminNoticesHelpersTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Notices\DismissibleNotice;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DWS_LO_Deps\Psr\Log\LogLevel;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles changes to the lifecylce of WC orders.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
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
		$should_autocomplete = dws_wc_lo_get_validated_setting( 'general_autocomplete-descendants' );
		if ( true === $should_autocomplete ) {
			$descendants = dws_wc_lo_get_orders_tree( $order_id );
			foreach ( $descendants as $descendant_id ) {
				$descendant_order = wc_get_order( $descendant_id );
				if ( \is_a( $descendant_order, \WC_Order::class ) ) {
					$descendant_order->update_status(
						'completed',
						\__( 'Child order autocompleted.', 'linked-orders-for-woocommerce' )
					);
				}
			}
		}
	}

	// endregion
}
