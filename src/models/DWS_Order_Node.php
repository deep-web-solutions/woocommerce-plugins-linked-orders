<?php

use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Exceptions\NotSupportedException;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Hierarchy\NodeInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Hierarchy\NodeTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Users;

defined( 'ABSPATH' ) || exit;

/**
 * Models the functions needed to interact with a linked order.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class DWS_Order_Node implements NodeInterface {
	// region TRAITS

	use NodeTrait;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * The WC order object storing the current linked order.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     WC_Order
	 * @access  protected
	 */
	protected WC_Order $order;

	/**
	 * Whether the current object's metadata has been ready from the database yet or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     bool
	 * @access  protected
	 */
	protected bool $is_read = false;

	// endregion

	// region MAGIC METHODS

	/**
	 * DWS_Linked_Order constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int|WC_Order    $order      WC order ID or order object.
	 *
	 * @throws  NotSupportedException   Thrown if the order passed on is not proper.
	 */
	public function __construct( $order ) {
		if ( ! is_object( $order ) ) {
			$order = wc_get_order( absint( $order ) );
		}

		if ( ! is_a( $order, WC_Order::class ) ) {
			throw new NotSupportedException( 'Order is not supported' );
		}

		$this->order = $order;
	}

	/**
	 * Transparently access the WC Order data.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   Name of the WC Order property to return.
	 *
	 * @return  mixed|null
	 */
	public function __get( string $name ) {
		return property_exists( $this->order, $name )
			? $this->order->{$name} : null;
	}

	/**
	 * Transparently call the WC Order methods.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   Name of the WC Order method to call.
	 * @param   array   $args   Arguments to pass on to the called method.
	 *
	 * @return  null|mixed
	 */
	public function __call( string $name, array $args ) {
		return method_exists( $this->order, $name )
			? call_user_func_array( array( $this->order, $name ), $args ) : null;
	}

	// endregion

	// region METHODS

	/**
	 * Returns whether the object's metadata has been populated yet or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_read(): bool {
		return $this->is_read;
	}

	/**
	 * Populates the object's metadata from the database if not done already.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_read() {
		if ( ! $this->is_read() ) {
			$this->read();
		}
	}

	/**
	 * Forces repopulating the object's metadata from the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function read() {
		$this->depth = Integers::maybe_cast( $this->order->get_meta( '_dws_lo_depth' ), 0 );
		if ( $this->depth > 0 ) {
			$this->parent = new DWS_Order_Node( $this->order->get_meta( '_dws_lo_parent' ) );
		}

		$this->children = array_map(
			fn( $child_id ) => new DWS_Order_Node( $child_id ),
			$this->order->get_meta( '_dws_lo_children' ) ?: array()
		);

		$this->is_read = true;
	}

	/**
	 * Saves the objects' fields to the order's metadata.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function save() {
		$this->order->update_meta_data( '_dws_lo_depth', $this->depth );
		$this->order->update_meta_data( '_dws_lo_parent', $this->parent ? $this->parent->get_id() : null );
		$this->order->update_meta_data( '_dws_lo_children', array_map( fn( $child ) => $child->get_id(), $this->children ) );

		$this->order->save();
	}

	/**
	 * Checks whether a given user can create new linked orders for the current order or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int|null    $user_id    The ID of the user that the checks are being performed for.
	 *
	 * @return  bool
	 */
	public function can_create_linked_order( ?int $user_id = null ): bool {
		$permission = Users::has_capabilities( array( 'create_dws_linked_order', 'edit_shop_orders' ), array( $this->order->get_id() ), $user_id ) ?? false;
		$max_depth  = dws_wc_lo_get_validated_setting( 'general_max-depth' ) > $this->get_depth();

		return apply_filters( dws_wc_lo_instance()->get_hook_tag( 'can_create_linked_order' ), $permission && $max_depth, $this->order, $this );
	}

	// endregion
}
