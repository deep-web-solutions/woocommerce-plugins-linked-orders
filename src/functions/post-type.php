<?php

use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

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
