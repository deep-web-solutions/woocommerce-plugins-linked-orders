<?php

defined( 'ABSPATH' ) || exit;

/**
 * Converts a WC Order reference to a DWS_Linked_Order object and reads its metadata from the database.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order  Order to retrieve.
 *
 * @return  DWS_Order_Node|null
 */
function dws_wc_lo_get_order_node( $order ): ?DWS_Order_Node {
	try {
		$dws_order = new DWS_Order_Node( $order );
		$dws_order->read();

		return $dws_order;
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
 * @param   WC_Order|int    $order  Order to retrieve.
 *
 * @return  bool|null
 */
function dws_wc_lo_is_root_order( $order ): ?bool {
	$dws_order = dws_wc_lo_get_order_node( $order );
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
function dws_wc_lo_get_root_order( $order ): ?DWS_Order_Node {
	$dws_order = dws_wc_lo_get_order_node( $order );
	if ( is_null( $dws_order ) ) {
		return null;
	}

	return 0 === $dws_order->get_depth()
		? $dws_order
		: dws_wc_lo_get_root_order( $dws_order->get_parent()->get_id() );
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
function dws_wc_lo_get_orders_tree( $order ): ?array {
	$dws_order = dws_wc_lo_get_order_node( $order );
	if ( is_null( $dws_order ) ) {
		return null;
	}

	$descendants = array();
	foreach ( $dws_order->get_children() as $child ) {
		$descendants = array_merge( $descendants, dws_wc_lo_get_orders_tree( $child->get_id() ) );
	}

	return array_merge( array( $dws_order->get_id() ), $descendants );
}

/**
 * Determines whether a given user can create linked orders for a given order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order      Order to retrieve.
 * @param   int|null        $user_id    The ID of the user to check for.
 *
 * @return bool|null
 */
function dws_wc_lo_can_create_linked_order( $order, ?int $user_id = null ): ?bool {
	$order = dws_wc_lo_get_order_node( $order );
	if ( is_null( $order ) ) {
		return null;
	}

	return $order->can_create_linked_order( $user_id );
}
