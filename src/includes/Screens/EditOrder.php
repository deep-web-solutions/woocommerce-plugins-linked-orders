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
class ShopOrder extends AbstractPluginFunctionality implements SettingsServiceAwareInterface {
	// region TRAITS

	use InitializeSettingsServiceTrait;
	use SetupHooksTrait;
	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers hooks with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service  Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		if ( Users::has_capabilities( array( LinkingPermissions::EDIT_LINKED_ORDERS ) ) ) {
			$hooks_service->add_action( 'woocommerce_process_shop_order_meta', $this, 'save_linked_orders' );
		}
	}

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
				'priority' => 'high',
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

		$linked_orders = $this->get_field( '_dws_wc_linked_orders', $order->get_id() );
		if ( empty( $linked_orders ) ) {
			echo \esc_html__( 'There are no linked orders', 'linked-orders-for-woocommerce' );
		}
	}

	// endregion

	// region HOOKS

	/**
	 * Saves the linked orders list.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int     $order_id   The ID of the WC order being saved.
	 */
	public function save_linked_orders( int $order_id ): void {
		$linked_orders = Arrays::maybe_cast_input( INPUT_POST, '_dws_wc_linked_orders', array() );
		$this->update_field( '_dws_wc_linked_orders', $linked_orders, $order_id );
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

		return \wc_get_order( $post_id ) ?: null; // phpcs:ignore
	}

	// endregion
}
