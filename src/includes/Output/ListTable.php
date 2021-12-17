<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Output;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\OutputPermissions;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Helpers\AssetsHelpersTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\Users;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Outputs a new column, filters, and actions on the orders archive page.
 *
 * @since   1.0.0
 * @version 1.2.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class ListTable extends AbstractPluginFunctionality {
	// region TRAITS

	use ActiveLocalTrait;
	use AssetsHelpersTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	public function is_active_local(): bool {
		return Users::has_capabilities( OutputPermissions::SEE_TABLE_COLUMN ) ?? false;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'plugins_loaded', $this, 'hook_columns_for_supported_order_types', 99 );

		$hooks_service->add_action( 'restrict_manage_posts', $this, 'output_filters', 20 );
		$hooks_service->add_filter( 'request', $this, 'filter_request_query', 999 );

		$hooks_service->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_scripts' );
		$hooks_service->add_filter( 'woocommerce_admin_order_actions', $this, 'register_actions', 999, 2 );
	}

	// endregion

	// region HOOKS

	/**
	 * Hooks the column registration and output methods for all supported order types.
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 *
	 * @return  void
	 */
	public function hook_columns_for_supported_order_types() {
		foreach ( dws_lowc_get_supported_order_types() as $order_type ) {
			\add_filter( "manage_edit-{$order_type}_columns", array( $this, 'register_column' ) );
			\add_action( "manage_{$order_type}_posts_custom_column", array( $this, 'output_column' ), 10, 2 );
		}
	}

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
	public function register_column( array $columns ): array {
		$insert_after = \apply_filters( $this->get_hook_tag( 'column', 'insert_after' ), 'order_total', \get_post_type(), $columns );

		return Arrays::insert_after(
			$columns,
			$insert_after,
			array(
				'dws_lo_depth' => \_x( 'Depth', 'list table heading', 'linked-orders-for-woocommerce' ),
			)
		);
	}

	/**
	 * Populates the new table columns.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $column     The column being populated.
	 * @param   int     $post_id    The ID of the row post.
	 */
	public function output_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'dws_lo_depth':
				$dws_node = dws_lowc_get_order_node( $post_id );
				$depth    = $dws_node->get_depth();

				if ( 0 === $depth ) {
					$depth_label = \sprintf(
						/* translators: %s: order type singular name */
						\_x( 'Root %s', 'list table content', 'linked-orders-for-woocommerce' ),
						$dws_node->get_post_type()->labels->singular_name
					);
				} else {
					$depth_label = \sprintf(
						/* translators: %s: order type singular name; %d: numerical depth of the order */
						\_x( 'Child %1$s (level %2$d)', 'list table content', 'linked-orders-for-woocommerce' ),
						$dws_node->get_post_type()->labels->singular_name,
						$depth + 1 // +1 adjustment for non-programmers
					);
				}

				$depth_label = \apply_filters( $this->get_hook_tag( 'column', 'content' ), $depth_label, $dws_node );
				echo \esc_html( $depth_label );

				break;
		}
	}

	/**
	 * Outputs HTML for new table filters.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 */
	public function output_filters() {
		global $typenow;

		if ( \in_array( $typenow, dws_lowc_get_supported_order_types(), true ) ) {
			$post_type_object = \get_post_type_object( $typenow );
			$max_depth        = $this->get_max_depth( $typenow );

			?>

			<!-- DWS Linked Orders filter for guest orders made with the same billing email. -->
			<input type="hidden"
					name="_guest_customer_user"
					value="<?php echo \esc_attr( \wc_clean( Strings::maybe_cast_input( INPUT_GET, '_guest_customer_user', '' ) ) ); ?>"
			/>

			<!-- DWS Linked Orders filter for storing the pivot order. -->
			<input type="hidden"
					name="_dws_linked_order_id"
					value="<?php echo \esc_attr( \wc_clean( Strings::maybe_cast_input( INPUT_GET, '_dws_linked_order_id', '' ) ) ); ?>"
			/>

			<!-- DWS Linked Orders filter for linking depth. -->
			<select name="_dws_lo_depth" id="dws_lo_depth_filter_dropdown">
				<option value=""">
					<?php
						echo \esc_html(
							\sprintf(
								/* translators: %s: order type singular name */
								\__( 'All %s depths', 'linked-orders-for-woocommerce' ),
								$post_type_object->labels->singular_name
							)
						);
					?>
				</option>
				<option value="0" <?php \selected( 0, Integers::maybe_cast_input( INPUT_GET, '_dws_lo_depth' ) ); ?>>
					<?php
						echo \esc_html(
							\sprintf(
								/* translators: %s: order type plural name */
								\_x( 'Root %s', 'list table filter', 'linked-orders-for-woocommerce' ),
								$post_type_object->label
							)
						);
					?>
				</option>
				<?php for ( $i = 1; $i <= $max_depth; $i++ ) : ?>
					<option value="<?php echo \esc_attr( $i ); ?>" <?php \selected( $i, Integers::maybe_cast_input( INPUT_GET, '_dws_lo_depth' ) ); ?>>
						<?php
							echo \esc_html(
								\sprintf(
									/* translators: %s: order type plural name; %d: numerical depth of the order */
									\_x( 'Child %1$s (level %2$d)', 'list table filter', 'linked-orders-for-woocommerce' ),
									$post_type_object->label,
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
	public function filter_request_query( array $query_vars ): array {
		global $typenow;

		if ( \in_array( $typenow, dws_lowc_get_supported_order_types(), true ) ) {
			// Filter orders made by the same guest email address.
			$guest_customer = \wc_clean( Strings::maybe_cast_input( INPUT_GET, '_guest_customer_user' ) );
			if ( ! empty( $guest_customer ) ) {
				// @codingStandardsIgnoreStart.
				$query_vars['meta_query']   = $query_vars['meta_query'] ?? array();
				$query_vars['meta_query'][] = array(
					'key'     => '_billing_email',
					'value'   => $guest_customer,
					'compare' => '=',
				);
				// @codingStandardsIgnoreEnd
			}

			// Filter orders based on their linking depth.
			$order_depth = Integers::maybe_cast_input( INPUT_GET, '_dws_lo_depth' );
			if ( ! \is_null( $order_depth ) && $order_depth >= 0 ) {
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

			// Filter orders based on the pivot order.
			$pivot_order = Integers::maybe_cast_input( INPUT_GET, '_dws_linked_order_id' );
			if ( ! empty( $pivot_order ) ) {
				$root_order             = dws_lowc_get_root_order( $pivot_order );
				$query_vars['post__in'] = dws_lowc_get_orders_tree( $root_order->get_id() );
			}
		}

		return $query_vars;
	}

	/**
	 * Enqueues the necessary scripts and styles on the orders list table page.
	 *
	 * @since   1.1.0
	 * @version 1.1.1
	 *
	 * @param   string  $hook_suffix    The WordPress admin page suffix.
	 */
	public function enqueue_admin_scripts( string $hook_suffix ) {
		if ( 'edit.php' !== $hook_suffix || ! \in_array( $GLOBALS['typenow'] ?? '', dws_lowc_get_supported_order_types(), true ) ) {
			return;
		}

		$plugin        = $this->get_plugin();
		$minified_path = $this->maybe_get_minified_asset_path( $plugin::get_plugin_assets_url() . 'dist/css/orders-list-table.css' );
		\wp_enqueue_style(
			$this->get_asset_handle(),
			$minified_path,
			array( 'woocommerce_admin_styles' ),
			$this->maybe_get_asset_mtime_version( $minified_path, $plugin->get_plugin_version() )
		);

		$minified_path = $this->maybe_get_minified_asset_path( $plugin::get_plugin_assets_url() . 'dist/js/orders-list-table.js' );
		\wp_enqueue_script(
			$this->get_asset_handle(),
			$minified_path,
			array( 'jquery' ),
			$this->maybe_get_asset_mtime_version( $minified_path, $plugin->get_plugin_version() ),
			true
		);
	}

	/**
	 * Registers new order-level actions in the list table.
	 *
	 * @since   1.0.0
	 * @version 1.1.0
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @param   array       $actions    WC order actions.
	 * @param   \WC_Order   $order      WC order the actions belong to.
	 *
	 * @return  array
	 */
	public function register_actions( array $actions, \WC_Order $order ): array {
		$dws_node = dws_lowc_get_order_node( $order );
		if ( \is_null( $dws_node ) ) {
			return $actions;
		}

		$new_actions = array();

		// Action for viewing all of a customer's orders in one click.
		if ( ! empty( $order->get_customer_id() ) ) {
			if ( empty( Strings::maybe_cast_input( INPUT_GET, '_customer_user' ) ) ) {
				$new_actions['view_all_customer_orders'] = array(
					'url'    => \add_query_arg(
						array(
							'post_type'      => $dws_node->get_post_type()->name,
							'_customer_user' => $order->get_customer_id(),
						),
						\admin_url( 'edit.php' )
					),
					'name'   => \sprintf(
						/* translators: %s: the post type label, e.g. orders */
						\__( 'View all customer %s', 'linked-orders-for-woocommerce' ),
						$dws_node->get_post_type()->label
					),
					'action' => 'view-all-customer-orders',
				);
			}
		} elseif ( ! empty( $order->get_billing_email() ) ) {
			if ( empty( Strings::maybe_cast_input( INPUT_GET, '_guest_customer_user' ) ) ) {
				$new_actions['view_all_customer_orders'] = array(
					'url'    => \add_query_arg(
						array(
							'post_type'            => $dws_node->get_post_type()->name,
							'_guest_customer_user' => \rawurlencode( $order->get_billing_email() ),
						),
						\admin_url( 'edit.php' )
					),
					'name'   => \sprintf(
						/* translators: %s: the post type label, e.g. orders */
						\__( 'View all customer %s', 'linked-orders-for-woocommerce' ),
						$dws_node->get_post_type()->label
					),
					'action' => 'view-all-customer-orders',
				);
			}
		}

		// Action for viewing all linked orders to a given one.
		if ( empty( Strings::maybe_cast_input( INPUT_GET, '_dws_linked_order_id' ) ) ) {
			if ( $dws_node->has_parent() || $dws_node->has_children() ) {
				$new_actions['view_all_linked_orders'] = array(
					'url'    => \add_query_arg(
						array(
							'post_type'            => $dws_node->get_post_type()->name,
							'_dws_linked_order_id' => $order->get_id(),
						),
						\admin_url( 'edit.php' )
					),
					'name'   => \sprintf(
						/* translators: %s: the post type label, e.g. orders */
						\__( 'View all linked %s', 'linked-orders-for-woocommerce' ),
						$dws_node->get_post_type()->label
					),
					'action' => 'view-all-linked-orders',
				);
			}
		}

		// Action for creating a new linked order one level down from the current one.
		if ( true === $dws_node->can_create_child() ) {
			$new_actions['create_empty_linked_child'] = array(
				'url'    => \wp_nonce_url(
					\add_query_arg(
						array(
							'action'    => 'dws_lowc_create_linked_child',
							'parent_id' => $order->get_id(),
						),
						\admin_url( 'admin-post.php' )
					),
					'dws_create_linked_child'
				),
				'name'   => \sprintf(
					/* translators: %s: the post type singular label, e.g. order */
					\__( 'Create empty linked %s', 'linked-orders-for-woocommerce' ),
					$dws_node->get_post_type()->labels->singular_name
				),
				'action' => 'create-empty-linked-child',
			);
		}

		return \array_merge( $new_actions, $actions );
	}

	// endregion

	// region HELPERS

	/**
	 * In order to ensure that it's always possible to filter existing orders, we need to ensure that we get the maximum
	 * used order depth. That can be different from the settings if the maximum allowed depth was lowered after already
	 * creating deeper children.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param   string  $post_type  The post type to return the value for.
	 *
	 * @return  int
	 */
	protected function get_max_depth( string $post_type ): int {
		global $wpdb;

		$max_database = $wpdb->get_var( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT MAX( meta_value ) FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE post_type = %s AND meta_key = '_dws_lo_depth'",
				$post_type
			)
		);
		$max_database = Integers::maybe_cast( $max_database, 0 );

		$max_settings = dws_lowc_get_validated_setting( 'max-depth', 'general' );

		return \max( $max_settings, $max_database );
	}

	// endregion
}
