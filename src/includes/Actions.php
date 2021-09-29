<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeAdminNoticesServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticeTypesEnum;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Helpers\AdminNoticesHelpersTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Notices\DismissibleNotice;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DWS_LO_Deps\Psr\Log\LogLevel;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the registration of AJAX actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class Actions extends AbstractPluginFunctionality {
	// region TRAITS

	use AdminNoticesHelpersTrait;
	use InitializeAdminNoticesServiceTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'admin_post_dws_lowc_create_linked_order', $this, 'create_linked_order' );
	}

	// endregion

	// region AJAX

	/**
	 * Trigger the creation of a new linked order.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function create_linked_order() {
		\check_admin_referer( 'dws_create_linked_order' );

		$parent_order_id = Integers::maybe_cast_input( INPUT_GET, 'order_id', 0 );
		$dws_parent_node = dws_lowc_get_order_node( $parent_order_id );

		if ( empty( $dws_parent_node ) ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleNotice( $this->get_admin_notice_handle( 'invalid-parent-id' ), \__( 'Invalid parent ID.', 'linked-orders-for-woocommerce' ), AdminNoticeTypesEnum::ERROR ),
				'user-meta'
			);
		} elseif ( true !== $dws_parent_node->can_create_child() ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleNotice( $this->get_admin_notice_handle( 'missing-permissions' ), \__( 'You are not authorized to create linked orders.', 'linked-orders-for-woocommerce' ), AdminNoticeTypesEnum::ERROR ),
				'user-meta'
			);
		} else {
			$linked_order_id = dws_lowc_create_linked_order( $parent_order_id );
			if ( \is_wp_error( $linked_order_id ) ) {
				$this->get_admin_notices_service()->add_notice(
					new DismissibleNotice(
						$this->get_admin_notice_handle( 'creation-error' ),
						/* translators: error message contents */
						\sprintf( \__( 'Failed to create a new linked order. Error message: %s', 'linked-orders-for-woocommerce' ), $linked_order_id->get_error_message() ),
						AdminNoticeTypesEnum::ERROR
					),
					'user-meta'
				);
			} else {
				\wp_safe_redirect( \get_edit_post_link( $linked_order_id, 'redirect' ) );
				exit;
			}
		}

		\wp_safe_redirect( \wp_get_referer() ?: \admin_url( 'edit.php?post_type=' . \get_post_type( $parent_order_id ) ) );
		exit;
	}

	// endregion
}
