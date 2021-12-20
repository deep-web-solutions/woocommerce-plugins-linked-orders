<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Actions\InitializeAdminNoticesServiceTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticeTypesEnum;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Helpers\AdminNoticesHelpersTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Notices\DismissibleAdminNotice;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the registration of AJAX actions.
 *
 * @since   1.0.0
 * @version 1.2.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
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
		$hooks_service->add_action( 'admin_post_dws_lowc_create_linked_child', $this, 'create_linked_child' );
	}

	// endregion

	// region HOOKS

	/**
	 * Trigger the creation of a new linked order.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 */
	public function create_linked_child() {
		\check_admin_referer( 'dws_create_linked_child' );

		$parent_order_id = Integers::maybe_cast_input( INPUT_GET, 'parent_id', 0 );
		$dws_parent_node = dws_lowc_get_order_node( $parent_order_id );
		if ( \is_null( $dws_parent_node ) ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleAdminNotice(
					$this->get_admin_notice_handle( 'invalid-parent-id' ),
					\sprintf(
						/* translators: %d: parent order ID. */
						\__( 'Invalid parent ID -- cannot create linked child for order %d.', 'linked-orders-for-woocommerce' ),
						$parent_order_id
					),
					AdminNoticeTypesEnum::ERROR
				),
				'user-meta'
			);

			\wp_safe_redirect( \wp_get_referer() ?: \admin_url( 'index.php' ) );
			exit;
		}

		if ( true !== $dws_parent_node->can_create_child() ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleAdminNotice(
					$this->get_admin_notice_handle( 'missing-permissions' ),
					\sprintf(
						/* translators: %d: parent order ID. */
						\__( 'You are not authorized to create linked children for order %d.', 'linked-orders-for-woocommerce' ),
						$parent_order_id
					),
					AdminNoticeTypesEnum::ERROR
				),
				'user-meta'
			);
		} else {
			$args = Arrays::maybe_cast_input( INPUT_GET, 'args', array() );
			\array_walk( $args, 'sanitize_text_field' );

			$linked_order_id = dws_lowc_create_linked_child( $parent_order_id, $args );
			if ( \is_wp_error( $linked_order_id ) ) {
				$this->get_admin_notices_service()->add_notice(
					new DismissibleAdminNotice(
						$this->get_admin_notice_handle( 'creation-error' ),
						\sprintf(
							/* translators: %d: parent order ID; %s: error message contents. */
							\__( 'Failed to create a new linked child for order %1$d. Error message: %2$s', 'linked-orders-for-woocommerce' ),
							$parent_order_id,
							$linked_order_id->get_error_message()
						),
						AdminNoticeTypesEnum::ERROR
					),
					'user-meta'
				);
			} elseif ( \is_numeric( $linked_order_id ) ) {
				\wp_safe_redirect( \get_edit_post_link( $linked_order_id, 'redirect' ) );
				exit;
			}
		}

		\wp_safe_redirect( \wp_get_referer() ?: \admin_url( 'edit.php?post_type=' . \get_post_type( $parent_order_id ) ) );
		exit;
	}

	// endregion
}
