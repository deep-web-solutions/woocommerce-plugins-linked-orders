<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Output;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\OutputPermissions;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Outputtable\OutputFailureException;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Outputtable\OutputLocalTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\OutputtableInterface;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\Users;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Settings\Actions\SetupSettingsTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Settings\SettingsService;

\defined( 'ABSPATH' ) || exit;

/**
 * Outputs the linked orders metabox on the edit order screen.
 *
 * @since   1.0.0
 * @version 1.1.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
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
	 * @version 1.1.0
	 */
	public function is_active_local(): bool {
		return Users::has_capabilities( OutputPermissions::SEE_METABOX ) ?? false;
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
				$post_object = \get_post_type_object( \get_post_type() );
				return \sprintf(
					/* translators: %s: post type label */
					\_x( 'Linked %s', 'metabox heading', 'linked-orders-for-woocommerce' ),
					$post_object->label
				);
			},
			array(),
			dws_lowc_get_supported_order_types(),
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
	 * @version 1.1.0
	 */
	protected function output_local(): ?OutputFailureException {
		$order_id = \get_the_ID();
		$dws_node = dws_lowc_get_order_node( $order_id );

		// Output parent order information.
		if ( $dws_node->get_depth() > 0 ) {
			$dws_node_parent = $dws_node->get_parent();
			?>

			<div class="dws-linked-orders__parent">
				<?php echo \esc_html_x( 'Parent', 'metabox', 'linked-orders-for-woocommerce' ); ?>:
				<a href="<?php echo \esc_url( \get_edit_post_link( $dws_node_parent->get_id() ) ); ?>" target="_blank">
					<?php echo \wp_kses_post( $dws_node_parent->get_formatted_name() ); ?>
				</a>
			</div>

			<hr/>

			<?php
		}

		// Output children orders information.
		if ( true !== $dws_node->has_children() ) {
			?>

			<div class="dws-linked-orders__no-children">
				<?php \esc_html_e( 'There are no attached children.', 'linked-orders-for-woocommerce' ); ?>
				<?php if ( true !== $dws_node->can_create_child() ) : ?>
					<?php
						echo \esc_html(
							\sprintf(
								/* translators: node post type singular name */
								\_x( 'New children cannot be added to this %s.', 'metabox', 'linked-orders-for-woocommerce' ),
								$dws_node->get_post_type()->labels->singular_name
							)
						);
					?>
				<?php endif; ?>
			</div>

			<?php
		} else {
			?>

			<div class="dws-linked-orders__children">
				<?php \esc_html_e( 'Attached children', 'linked-orders-for-woocommerce' ); ?>:
				<ul class="dws-linked-orders__children-list">
					<?php foreach ( $dws_node->get_children() as $dws_child ) : ?>
						<li id="linked-order-<?php echo \esc_attr( $dws_child->get_id() ); ?>" class="dws-linked-order">
							<a href="<?php echo \esc_url( \get_edit_post_link( $dws_child->get_id() ) ); ?>" target="_blank">
								<?php echo \wp_kses_post( $dws_child->get_formatted_name() ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php
		}

		// Maybe output button for creating a new child order.
		if ( true === $dws_node->can_create_child() ) {
			$link = \wp_nonce_url( \admin_url( 'admin-post.php?action=dws_lowc_create_linked_child&parent_id=' . $order_id ), 'dws_create_linked_child' );
			?>

			<a class="button button-alt" href="<?php echo \esc_url( $link ); ?>">
				<?php
					echo \esc_html(
						\sprintf(
							/* translators: post type singular name */
							\_x( 'Add new child %s', 'metabox', 'linked-orders-for-woocommerce' ),
							$dws_node->get_post_type()->labels->singular_name
						)
					);
				?>
			</a>

			<?php
		}

		return null;
	}

	// endregion
}
