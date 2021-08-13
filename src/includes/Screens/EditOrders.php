<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Screens;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupScriptsStylesTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Assets\Handlers\ScriptsHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Assets\Handlers\StylesHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles customizations to the WC orders archive page.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Screens
 */
class OrdersArchive extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;
	use SetupScriptsStylesTrait;

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
		$hooks_service->add_filter( 'request', $this, 'maybe_filter_request_query', 999 );
	}

	/**
	 * Registers scripts and styles with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ScriptsHandler  $scripts_handler    Instance of the scripts handler.
	 * @param   StylesHandler   $styles_handler     Instance of the styles handler.
	 */
	public function register_scripts_and_styles( ScriptsHandler $scripts_handler, StylesHandler $styles_handler ): void {
		$scripts_handler->enqueue_admin_script(
			$this->get_asset_handle(),
			Plugin::get_plugin_assets_base_relative_url() . 'dist/js/orders-archive.js',
			$this->get_plugin()->get_plugin_version(),
			array( 'jquery' ),
			true,
			array( 'edit.php' )
		);

		$styles_handler->enqueue_admin_style(
			$this->get_asset_handle(),
			Plugin::get_plugin_assets_base_relative_url() . 'dist/css/orders-archive.css',
			$this->get_plugin()->get_plugin_version(),
			array(),
			'all',
			array( 'edit.php' )
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Registers a new WC order action for viewing all of a customer's orders.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $actions    WC order actions.
	 * @param   \WC_Order   $order      WC order the actions belong to.
	 *
	 * @return  array
	 */
	public function register_view_all_action( array $actions, \WC_Order $order ): array {
		$new_actions = array();

		if ( ! empty( $order->get_customer_id() ) ) {
			if ( ! isset( $_GET['_customer_user'] ) ) { // phpcs:ignore
				$new_actions['view_all_customer_orders'] = array(
					'url'    => \admin_url( 'edit.php?post_type=shop_order&_customer_user=' . $order->get_customer_id() ),
					'name'   => \__( 'View all customer orders', 'linked-orders-for-woocommerce' ),
					'action' => 'view-all-customer-orders',
				);
			}
		} elseif ( ! empty( $order->get_billing_email() ) ) {
			if ( ! isset( $_GET['_guest_customer_user'] ) ) { // phpcs:ignore
				$new_actions['view_all_customer_orders'] = array(
					'url'    => \admin_url( 'edit.php?post_type=shop_order&_guest_customer_user=' . \rawurldecode( $order->get_billing_email() ) ),
					'name'   => \__( 'View all customer orders', 'linked-orders-for-woocommerce' ),
					'action' => 'view-all-customer-orders',
				);
			}
		}

		if ( Users::has_capabilities( array( Permissions::CREATE_LINKED_ORDERS ) ) ) {
			$new_actions['create_empty_linked_order'] = array(
				'url'    => \wp_nonce_url( \admin_url( 'admin-ajax.php?action=dws_wc_lo_create_empty_linked_order&order_id=' . $order->get_id() ), 'dws-lo-create-empty-linked-order' ),
				'name'   => \__( 'Create new linked order', 'linked-orders-for-woocommerce' ),
				'action' => 'create-empty-linked-order',
			);
		}

		return \array_merge( $new_actions, $actions );
	}

	/**
	 * Handle any filters.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $query_vars     Query vars.
	 *
	 * @return  array
	 */
	public function maybe_filter_request_query( array $query_vars ) {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) ) {
			$guest_customer = Strings::maybe_cast_input( INPUT_GET, '_guest_customer_user' );
			if ( ! empty( $guest_customer ) ) {
				// @codingStandardsIgnoreStart.
				$query_vars['meta_query'] = array(
					array(
						'key'     => '_billing_email',
						'value'   => $guest_customer,
						'compare' => '=',
					),
				);
				// @codingStandardsIgnoreEnd
			}
		}

		return $query_vars;
	}

	// endregion
}
