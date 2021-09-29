<?php

use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Exceptions\NotSupportedException;

defined( 'ABSPATH' ) || exit;

/**
 * Converts a WC Order reference to a DWS_Order_Node object.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order      Order reference.
 *
 * @return  DWS_Order_Node|null
 */
function dws_lowc_get_order_node( $order ): ?DWS_Order_Node {
	try {
		return new DWS_Order_Node( $order );
	} catch ( NotSupportedException $exception ) {
		return null;
	}
}

/**
 * Determines whether a given order is a root order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order      Order reference.
 *
 * @return  bool|null
 */
function dws_lowc_is_root_order( $order ): ?bool {
	$dws_order = dws_lowc_get_order_node( $order );
	if ( is_null( $dws_order ) ) {
		return null;
	}

	return 0 === $dws_order->get_depth();
}

/**
 * Goes up a linking tree and retrieves the root order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order  Order to retrieve the root for.
 *
 * @return  DWS_Order_Node|null
 */
function dws_lowc_get_root_order( $order ): ?DWS_Order_Node {
	$dws_order = dws_lowc_get_order_node( $order );
	if ( is_null( $dws_order ) ) {
		return null;
	}

	return 0 === $dws_order->get_depth()
		? $dws_order
		: dws_lowc_get_root_order( $dws_order->get_parent()->get_id() );
}

/**
 * Returns the full list of orders linked to the given one as the root.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order  Order to retrieve the root for.
 *
 * @return  DWS_Order_Node[]|null
 */
function dws_lowc_get_orders_tree( $order ): ?array {
	$dws_order = dws_lowc_get_order_node( $order );
	if ( is_null( $dws_order ) ) {
		return null;
	}

	$descendants = array();
	foreach ( $dws_order->get_children() as $child ) {
		$descendants = array_merge( $descendants, dws_lowc_get_orders_tree( $child->get_id() ) );
	}

	return array_merge( array( $dws_order->get_id() ), $descendants );
}
