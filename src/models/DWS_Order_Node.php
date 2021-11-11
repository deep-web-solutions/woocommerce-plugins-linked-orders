<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Exceptions\NotSupportedException;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Hierarchy\NodeInterface;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Hierarchy\NodeTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Hierarchy\ParentInterface;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\Users;

defined( 'ABSPATH' ) || exit;

/**
 * Models the functions needed to interact with a linked order.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Order_Node implements NodeInterface {
	// region TRAITS

	use NodeTrait {
		set_parent as protected set_parent_trait;
		set_children as protected set_children_trait;
		add_child as protected add_child_trait;
	}

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * The WC order object storing the current linked order.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     WC_Order
	 */
	protected WC_Order $order;

	/**
	 * The WordPress post type object for the current order (@see dws_lowc_get_supported_order_types()).
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     WP_Post_Type
	 */
	protected WP_Post_Type $post_type;

	/**
	 * Whether the current object's metadata has been ready from the database yet or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     bool
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
		$order = wc_get_order( $order );
		if ( true !== dws_lowc_is_supported_order( $order ) ) {
			throw new NotSupportedException( 'Order type is not supported' );
		}

		$this->order     = $order;
		$this->post_type = get_post_type_object( $order->get_type() );
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

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_depth(): int {
		$this->maybe_read();
		return $this->depth;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function set_depth( int $depth ) {
		$this->maybe_read();
		$this->depth = $depth;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_parent(): ?DWS_Order_Node {
		$this->maybe_read();

		/* @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function set_parent( ParentInterface $parent ) {
		$this->maybe_read();
		$this->set_parent_trait( $parent );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  DWS_Order_Node[]
	 */
	public function get_children(): array {
		$this->maybe_read();
		return $this->children;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function set_children( array $children ) {
		$this->maybe_read();
		$this->set_children_trait( $children );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_child( $child ): bool {
		$this->maybe_read();
		return $this->add_child_trait( $child );
	}

	// endregion

	// region GETTERS

	/**
	 * Returns the internal order object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  WC_Order
	 */
	public function get_order(): WC_Order {
		return $this->order;
	}

	/**
	 * Returns the order object's post type object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  WP_Post_Type
	 */
	public function get_post_type(): WP_Post_Type {
		return $this->post_type;
	}

	// endregion

	// region METHODS

	/**
	 * Returns the formatted name for the node's order.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_formatted_name(): string {
		$status_name = apply_filters(
			dws_lowc_get_hook_tag( 'node', array( 'status_name' ) ),
			wc_get_order_status_name( $this->order->get_status() ),
			$this->post_type,
			$this->order,
			$this
		);
		$node_name   = \sprintf(
			/* translators: 1. Post type name. 2. Order number; 3. Order status label. */
			\__( '%1$s #%2$s - %3$s', 'linked-orders-for-woocommerce' ),
			$this->post_type->labels->singular_name,
			$this->order->get_order_number(),
			$status_name
		);

		return apply_filters( dws_lowc_get_hook_tag( 'node', array( 'get_formatted_name' ) ), $node_name, $this );
	}

	/**
	 * Checks whether a given user is allowed to create a linked child for the current order object or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int|null    $user_id    The ID of the user that the checks are being performed for.
	 *
	 * @return bool
	 */
	public function can_create_child( ?int $user_id = null ): bool {
		$permission = Users::has_capabilities( array( Permissions::CREATE_LINKED_CHILDREN, 'edit_shop_orders' ), array( $this->order->get_id() ), $user_id ) ?? false;
		$max_depth  = dws_lowc_get_validated_setting( 'max-depth', 'general' ) > $this->get_depth();
		$statuses   = $this->order->has_status( dws_lowc_get_valid_statuses_for_new_child( $this->post_type->name, $this->order ) );

		return apply_filters( dws_lowc_get_hook_tag( 'node', array( 'can_create_child' ) ), $permission && $max_depth && $statuses, $user_id, $this->order, $this );
	}

	/**
	 * Saves the objects' fields to the order's metadata.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function save() {
		$this->order->update_meta_data( '_dws_lo_depth', $this->get_depth() );
		$this->order->update_meta_data( '_dws_lo_parent', $this->get_parent() ? $this->get_parent()->get_id() : null );
		$this->order->update_meta_data( '_dws_lo_children', array_map( fn( $child ) => $child->get_id(), $this->get_children() ) );

		$this->order->save();
	}

	// endregion

	// region HELPERS

	/**
	 * The linking metadata is read lazily. This is to avoid infinite loops and to minimize database reads.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function maybe_read(): void {
		if ( true === $this->is_read ) {
			return;
		}

		$this->depth = Integers::maybe_cast( $this->order->get_meta( '_dws_lo_depth' ), 0 );
		if ( $this->depth > 0 ) {
			$this->parent = dws_lowc_get_order_node( $this->order->get_meta( '_dws_lo_parent' ) );
		}

		$this->children = array_filter(
			array_map(
				fn( $child_id ) => dws_lowc_get_order_node( $child_id ),
				Arrays::validate( $this->order->get_meta( '_dws_lo_children' ), array() )
			)
		);

		$this->is_read = true;
	}

	// endregion
}
