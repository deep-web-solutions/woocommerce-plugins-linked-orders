<?php

use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Callables;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_LO_Deps\Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the order types that support linking. They must all be compatible with @see WC_Order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  array
 */
function dws_lowc_get_supported_order_types(): array {
	return apply_filters(
		dws_lowc_get_hook_tag( 'post_type', array( 'supported_order_types' ) ),
		array( 'shop_order' )
	);
}

/**
 * Returns whether the a given order is of a supported type for linking.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int|WP_Post|WC_Order    $order      Order reference.
 *
 * @return  bool|null
 */
function dws_lowc_is_supported_order( $order ): ?bool {
	if ( empty( $order ) ) {
		return null;
	}

	$order = wc_get_order( $order );
	return is_a( $order, WC_Order::class )
		? in_array( $order->get_type(), dws_lowc_get_supported_order_types(), true )
		: null;
}

/**
 * Returns the list of statuses that an order can be in to be possible to add new children to it.
 * By default, that's all statuses.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $order_type     The order type the query is for.
 * @param   WC_Order|null   $order          The order object to check for. Null for default statuses.
 *
 * @return  array
 */
function dws_lowc_get_valid_statuses_for_new_child( string $order_type = 'shop_order', ?WC_Order $order = null ): array {
	return apply_filters(
		dws_lowc_get_hook_tag( 'post_type', array( 'valid_statuses_for_new_child' ) ),
		array_map(
			fn( string $status_key ) => Strings::maybe_unprefix( $status_key, 'wc-' ),
			array_keys( wc_get_order_statuses() )
		),
		$order_type,
		$order
	);
}

/**
 * Creates a new empty order linked to a given parent order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int     $parent_order_id    The ID of the order to be set as the parent.
 * @param   array   $args               Additional order arguments.
 *
 * @return  int|null|WP_Error
 */
function dws_lowc_create_linked_order( int $parent_order_id, array $args = array() ) {
	// Ensure the parent order is supported.
	$parent_order = wc_get_order( $parent_order_id );
	if ( ! $parent_order || true !== dws_lowc_is_supported_order( $parent_order ) ) {
		return null;
	}

	// Set order creation arguments.
	$args = wp_parse_args(
		$args,
		array(
			'status'          => 'pending',
			'customer_id'     => $parent_order->get_customer_id(),
			'created_via'     => 'dws-linking',
			'create_function' => 'wc_create_order',
		)
	);
	$args = apply_filters( dws_lowc_get_hook_tag( 'create_linked_order_args' ), $args, $parent_order );

	// Create an empty linked order object.
	$args['create_function'] = Callables::validate( $args['create_function'] );
	if ( is_null( $args['create_function'] ) ) {
		dws_lowc_instance()->log_event( "Cannot create linked order since {$args['create_function']} is not callable" )
							->set_log_level( LogLevel::ERROR )
							->doing_it_wrong( __FUNCTION__, '1.0.0' )
							->finalize();
		return null;
	}

	$linked_order = call_user_func( $args['create_function'], $args );
	if ( is_wp_error( $linked_order ) ) {
		dws_lowc_instance()->log_event_and_finalize( "Failed to create linked order. Error: {$linked_order->get_error_message()}", array(), LogLevel::ERROR );
		return $linked_order;
	} elseif ( true !== dws_lowc_is_supported_order( $linked_order ) ) {
		dws_lowc_instance()->log_event( "Value returned by {$args['create_function']} is not supported" )
							->set_log_level( LogLevel::ERROR )
							->doing_it_wrong( __FUNCTION__, '1.0.0' )
							->finalize();
		return null;
	}

	// Copy info from parent order.
	$linked_order->set_address( $parent_order->get_address( 'billing' ), 'billing' );
	$linked_order->set_address( $parent_order->get_address( 'shipping' ), 'shipping' );
	$linked_order->add_meta_data( '_dws_lo_created_by', \get_current_user_id() );

	do_action( dws_lowc_get_hook_tag( 'created_linked_order' ), $linked_order, $parent_order );

	$linked_order->save();

	// Link orders.
	dws_lowc_link_orders( $parent_order_id, $linked_order->get_id() );

	return $linked_order->get_id();
}
