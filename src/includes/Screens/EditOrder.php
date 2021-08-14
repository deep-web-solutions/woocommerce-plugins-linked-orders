<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Screens;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\LinkingPermissions;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\ScreensPermissions;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeSettingsServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsServiceAwareInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles customizations to the WC edit order page.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Screens
 */
class EditOrder extends AbstractPluginFunctionality implements SettingsServiceAwareInterface {
	// region TRAITS

	use InitializeSettingsServiceTrait;
	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers the WC order meta-box.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service   Instance of the settings service.
	 */
	public function register_settings( SettingsService $settings_service ): void {
		if ( ! Users::has_capabilities( array( 'edit_shop_orders', ScreensPermissions::SEE_METABOX ) ) ) {
			return;
		}

		$settings_service->register_generic_group(
			'dws-wc-linked-orders',
			\_x( 'Linked Orders', 'settings', 'linked-orders-for-woocommerce' ),
			array(),
			array( 'shop_order' ),
			array(
				'priority' => 'default',
				'context'  => 'side',
				'callback' => array( $this, 'output_metabox' ),
			),
		);
	}

	// endregion

	// region METHODS

	/**
	 * Output a meta-box containing the unlocking settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function output_metabox(): void {
		$order = $this->get_current_order();
		if ( empty( $order ) ) {
			return;
		}

		$dws_order = dws_wc_lo_get_order_node( $order );
		if ( empty( $dws_order ) ) {
			return;
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
				<?php if ( ! $dws_order->can_create_linked_order() ) : ?>
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
		if ( $dws_order->can_create_linked_order() ) {
			$link = \wp_nonce_url( \admin_url( 'admin-ajax.php?action=dws_wc_lo_create_empty_linked_order&order_id=' . $order->get_id() ), 'dws-lo-create-empty-linked-order' );
			?>

			<a class="button button-alt" href="<?php echo \esc_url( $link ); ?>">
				<?php \esc_html_e( 'Add new child order', 'linked-orders-for-woocommerce' ); ?>
			</a>

			<?php
		}
	}

	// endregion

	// region HELPERS

	/**
	 * Returns the WC order object of the order currently being edited.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  \WC_Order|null
	 */
	protected function get_current_order(): ?\WC_Order {
		$post_id = Integers::maybe_cast_input( INPUT_GET, 'post', 0 );
		if ( empty( $post_id ) || 'shop_order' !== \get_post_type( $post_id ) ) {
			return null;
		}

		return \wc_get_order( $post_id ) ?: null;
	}

	/**
	 * Returns a formatted name for the given order.
	 *
	 * @param   \DWS_Order_Node   $order      Order to format the name of.
	 *
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function format_order_name( \DWS_Order_Node $order ): string {
		return sprintf(
			/* translators: 1. Order number; 2. Order status label. */
			__( 'Order #%1$s - %2$s', 'linked-orders-for-woocommerce' ),
			$order->get_order_number(),
			wc_get_order_status_name( $order->get_status() )
		);
	}

	// endregion
}
