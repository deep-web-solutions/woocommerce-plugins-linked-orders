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
 * Handles the creation of new linked orders and linking them to their parent.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class LinkingManager extends AbstractPluginFunctionality {
	// region TRAITS

	use AdminNoticesHelpersTrait;
	use InitializeAdminNoticesServiceTrait;
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
		$hooks_service->add_action( 'wp_ajax_dws_wc_lo_create_empty_linked_order', $this, 'ajax_create_empty_linked_order' );
	}

	// endregion

	// region METHODS

	/**
	 * Creates a new empty order linked to a given parent order.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int     $parent_order_id    The ID of the order that should be the parent of the new linked order.
	 *
	 * @return  \DWS_Order_Node|\WP_Error
	 */
	public function create_empty_linked_order( int $parent_order_id ) {
		// Validate parent order.
		$parent_order = \wc_get_order( $parent_order_id );
		if ( ! \is_a( $parent_order, \WC_Order::class ) ) {
			return new \WP_Error( 'dws_wc_lo_cannot_create', __( 'Invalid parent order ID', 'linked-orders-for-woocommerce' ), $parent_order_id );
		}

		// Create child order.
		$linked_order = \wc_create_order(
			\apply_filters(
				$this->get_hook_tag( 'empty_linked_order_args' ),
				array(
					'status'      => 'pending',
					'customer_id' => $parent_order->get_customer_id(),
					'created_via' => 'dws-linking',
				),
				$parent_order
			)
		);
		if ( \is_wp_error( $linked_order ) ) {
			return new \WP_Error( 'dws_wc_lo_cannot_create', __( 'Cannot create new WC Order', 'linked-orders-for-woocommerce' ), $linked_order );
		}

		// Copy info from parent order.
		$linked_order->set_address( $parent_order->get_address( 'billing' ), 'billing' );
		$linked_order->set_address( $parent_order->get_address( 'shipping' ), 'shipping' );
		$linked_order->add_meta_data( '_dws_lo_created_by', \get_current_user_id() );

		\do_action( $this->get_hook_tag( 'created_empty_linked_order' ), $linked_order, $parent_order );

		// Link orders.
		$dws_parent_order = dws_lowc_get_order_node( $parent_order );
		$dws_child_order  = dws_lowc_get_order_node( $linked_order );

		$dws_parent_order->add_child( $dws_child_order );
		$dws_parent_order->save();
		$dws_child_order->save();

		return $dws_child_order;
	}

	// endregion

	// region HOOKS

	/**
	 * Trigger the creation of a new linked order via AJAX.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function ajax_create_empty_linked_order() {
		$parent_order_id  = Integers::maybe_cast_input( INPUT_GET, 'order_id', 0 );
		$dws_parent_order = new \DWS_Order_Node( $parent_order_id );

		if ( ! \check_admin_referer( 'dws-lo-create-empty-linked-order' ) || ! $dws_parent_order->can_create_linked_order() ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleNotice( $this->get_admin_notice_handle( 'missing-permissions' ), \__( 'You are not authorized to create empty linked orders.', 'linked-orders-for-woocommerce' ), AdminNoticeTypesEnum::ERROR ),
				'user-meta'
			);
		} else {
			$linked_order = $this->create_empty_linked_order( $parent_order_id );
			if ( \is_wp_error( $linked_order ) ) {
				$this->log_event_and_finalize( $linked_order->get_error_message(), array( $linked_order ), LogLevel::ERROR );
				$this->get_admin_notices_service()->add_notice(
					new DismissibleNotice(
						$this->get_admin_notice_handle( 'missing-permissions' ),
						/* translators: error message contents */
						\sprintf( \__( 'Failed to create a new linked order. Error message: %s', 'linked-orders-for-woocommerce' ), $linked_order->get_error_message() ),
						AdminNoticeTypesEnum::ERROR
					),
					'user-meta'
				);
			} else {
				\wp_safe_redirect( \get_edit_post_link( $linked_order->get_id(), 'redirect' ) );
				exit;
			}
		}

		\wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=shop_order' ) );
		exit;
	}

	// endregion
}
