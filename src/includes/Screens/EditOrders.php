<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Screens;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
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
class EditOrders extends AbstractPluginFunctionality {
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
		$hooks_service->add_filter( 'manage_edit-shop_order_columns', $this, 'register_table_columns' );
		$hooks_service->add_action( 'manage_shop_order_posts_custom_column', $this, 'populate_columns', 10, 2 );

		$hooks_service->add_action( 'restrict_manage_posts', $this, 'output_table_filters', 20 );
		$hooks_service->add_filter( 'request', $this, 'maybe_filter_request_query', 999 );

		//$hooks_service->add_filter( 'woocommerce_admin_order_actions', $this, 'register_view_all_action', 999, 2 );
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
			array( 'woocommerce_admin_styles' ),
			'all',
			array( 'edit.php' )
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Registers new table columns.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $columns    The shop order columns.
	 *
	 * @return  array
	 */
	public function register_table_columns( array $columns ): array {
		return Arrays::insert_after(
			$columns,
			'order_total',
			array(
				'dws_lo_depth' => \_x( 'Depth', 'orders table', 'linked-orders-for-woocommerce' ),
			)
		);
	}

	/**
	 * Populates the new table columns.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $column
	 * @param   int     $post_id
	 */
	public function populate_columns( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'dws_lo_depth':
				$dws_order = dws_wc_lo_get_order_node( $post_id );
				$depth     = $dws_order->get_depth();

				if ( 0 === $depth ) {
					$depth_label = \_nx( 'Root order', 'Root orders', 1, 'orders table', 'linked-orders-for-woocommerce' );
				} else {
					/* translators: numerical depth of the order */
					$depth_label = \_nx( 'Child order (level %d)', 'Child orders (level %d)', 1, 'orders table', 'linked-orders-for-woocommerce' );
				}

				$depth_label = \apply_filters( $this->get_hook_tag( 'depth_label' ), $depth_label, $dws_order );
				if ( false !== \strpos( $depth_label, '%d' ) ) {
					$depth_label = \sprintf( $depth_label, $depth + 1 ); // +1 adjustment for non-programmers
				}

				echo \esc_html( $depth_label );

				break;
		}
	}

	/**
	 * Outputs HTML for new table filters.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function output_table_filters() {
		global $typenow;

		if ( 'shop_order' === $typenow ) {
			$max_depth = dws_wc_lo_get_validated_setting( 'general_max-depth' );

			?>

			<select name="_dws_lo_depth" id="dws_lo_depth_filter_dropdown">
				<option value=""">
					<?php \esc_html_e( 'All order depths', 'linked-orders-for-woocommerce' ); ?>
				</option>
				<option value="0" <?php selected( 0, Integers::maybe_cast_input( INPUT_GET, '_dws_lo_depth' ) ); ?>>
					<?php echo \esc_html( \_nx( 'Root order', 'Root orders', PHP_INT_MAX, 'orders table', 'linked-orders-for-woocommerce' ) ); ?>
				</option>
				<?php for ( $i = 1; $i <= $max_depth; $i++ ) : ?>
					<option value="<?php echo \esc_attr( $i ); ?>" <?php selected( $i, Integers::maybe_cast_input( INPUT_GET, '_dws_lo_depth' ) ); ?>>
						<?php
							echo \esc_html(
								\sprintf(
									/* translators: numerical depth of the order */
									\_nx( 'Child order (level %d)', 'Child orders (level %d)', PHP_INT_MAX, 'orders table', 'linked-orders-for-woocommerce' ),
									$i + 1 // +1 for non-programmers
								)
							);
						?>
					</option>
				<?php endfor; ?>
			</select>

			<?php
		}
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

		if ( \in_array( $typenow, \wc_get_order_types( 'order-meta-boxes' ), true ) ) {
			$order_depth = Integers::maybe_cast_input( INPUT_GET, '_dws_lo_depth' );
			if ( ! \is_null( $order_depth ) ) {
				// @codingStandardsIgnoreStart.
				$query_vars['meta_query'] = $query_vars['meta_query'] ?? array();
				if ( 0 === $order_depth ) {
					$query_vars['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'key'     => '_dws_lo_depth',
							'value'   => 0,
							'compare' => '='
						),
						array(
							'key'     => '_dws_lo_depth',
							'compare' => 'NOT EXISTS'
						)
					);
				} else {
					$query_vars['meta_query'][] = array(
						'key'     => '_dws_lo_depth',
						'value'   => $order_depth,
						'compare' => '='
					);
				}
				// @codingStandardsIgnoreEnd
			}

			/*
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
			*/
		}

		return $query_vars;
	}




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

		if ( dws_wc_lo_can_create_linked_order( $order ) ) {
			$new_actions['create_empty_linked_order'] = array(
				'url'    => \wp_nonce_url( \admin_url( 'admin-ajax.php?action=dws_wc_lo_create_empty_linked_order&order_id=' . $order->get_id() ), 'dws-lo-create-empty-linked-order' ),
				'name'   => \__( 'Create new linked order', 'linked-orders-for-woocommerce' ),
				'action' => 'create-empty-linked-order',
			);
		}

		return \array_merge( $new_actions, $actions );
	}



	// endregion
}
