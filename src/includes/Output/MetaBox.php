<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Output;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\OutputPermissions;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Actions\Outputtable\OutputFailureException;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Actions\Outputtable\OutputLocalTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Actions\OutputtableInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;

\defined( 'ABSPATH' ) || exit;

/**
 * Outputs the linked orders metabox on the edit order screen.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Output
 */
class MetaBox extends AbstractPluginFunctionality implements OutputtableInterface {
	// region TRAITS

	use ActiveLocalTrait;
	use OutputLocalTrait;
	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_active_local(): bool {
		return Users::has_capabilities( array( OutputPermissions::SEE_METABOX ) ) ?? false;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_settings( SettingsService $settings_service ): void {
		$settings_service->register_generic_group(
			'dws-linked-orders',
			function() {
				return \_x( 'Linked Orders', 'metabox heading', 'linked-orders-for-woocommerce' );
			},
			array(),
			array( 'shop_order' ),
			array(
				'priority' => 'default',
				'context'  => 'side',
				'callback' => array( $this, 'output' ),
			),
		);
	}

	// endregion

	// region OUTPUT

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function output_local(): ?OutputFailureException {
		$order_id  = \get_the_ID();
		$dws_order = dws_wc_lo_get_order_node( $order_id );
		if ( empty( $dws_order ) ) {
			return new OutputFailureException( 'Metabox is being outputted in an invalid context' );
		}

		// Output parent order information.
		if ( $dws_order->get_depth() > 0 ) {
			$dws_order_parent = $dws_order->get_parent();
			?>

			<div class="dws-linked-orders__parent">
				<?php \esc_html_e( 'Parent order: ', 'linked-orders-for-woocommerce' ); ?>
				<a href="<?php echo \esc_url( \get_edit_post_link( $dws_order_parent->get_id() ) ); ?>" target="_blank">
					<?php echo \wp_kses_post( $this->format_order_name( $dws_order_parent ) ); ?>
				</a>
			</div>

			<hr/>

			<?php
		}

		// Output children orders information.
		if ( empty( $dws_order->get_children() ) ) {
			?>

			<div class="dws-linked-orders__no-children">
				<?php \esc_html_e( 'There are no child orders attached.', 'linked-orders-for-woocommerce' ); ?>
				<?php if ( true !== $dws_order->can_create_linked_order() ) : ?>
					<?php \esc_html_e( 'New child orders cannot be added to this order.', 'linked-orders-for-woocommerce' ); ?>
				<?php endif; ?>
			</div>

			<?php
		} else {
			?>

			<div class="dws-linked-orders__children">
				<?php \esc_html_e( 'Child orders: ', 'linked-orders-for-woocommerce' ); ?>
				<ul class="dws-linked-orders__children-list">
					<?php foreach ( $dws_order->get_children() as $dws_child ) : ?>
						<li id="linked-order-<?php echo \esc_attr( $dws_child->get_id() ); ?>" class="dws-linked-order">
							<a href="<?php echo \esc_url( \get_edit_post_link( $dws_child->get_id() ) ); ?>" target="_blank">
								<?php echo \wp_kses_post( $this->format_order_name( $dws_child ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php
		}

		// Maybe output button for creating a new child order.
		if ( true === $dws_order->can_create_linked_order() ) {
			$link = \wp_nonce_url( \admin_url( 'admin-ajax.php?action=dws_lowc_create_empty_linked_order&order_id=' . $order_id ), 'dws_create_empty_linked_order' );
			?>

			<a class="button button-alt" href="<?php echo \esc_url( $link ); ?>">
				<?php \esc_html_e( 'Add new child order', 'linked-orders-for-woocommerce' ); ?>
			</a>

			<?php
		}

		return null;
	}

	// endregion

	// region HELPERS

	/**
	 * Returns a formatted name for the given order.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \DWS_Order_Node   $order      Order to format the name of.
	 *
	 * @return  string
	 */
	protected function format_order_name( \DWS_Order_Node $order ): string {
		return \sprintf(
			/* translators: 1. Order number; 2. Order status label. */
			\__( 'Order #%1$s - %2$s', 'linked-orders-for-woocommerce' ),
			$order->get_order_number(),
			\wc_get_order_status_name( $order->get_status() )
		);
	}

	// endregion
}
