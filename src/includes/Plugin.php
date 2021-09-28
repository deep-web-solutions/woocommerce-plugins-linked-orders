<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionalityRoot;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeAdminNoticesServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\InitializePluginDependenciesContextHandlersTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\SetupDependenciesAdminNoticesTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
final class Plugin extends AbstractPluginFunctionalityRoot {
	// region TRAITS

	use InitializeAdminNoticesServiceTrait;
	use InitializePluginDependenciesContextHandlersTrait;
	use SetupDependenciesAdminNoticesTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the WC plugin-level dependency.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_plugin_dependencies_active(): array {
		return array(
			'plugin'          => 'woocommerce/woocommerce.php',
			'name'            => 'WooCommerce',
			'min_version'     => '4.5.2',
			'version_checker' => function() {
				return \defined( 'WC_VERSION' ) ? WC_VERSION : '0.0.0';
			},
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		return \array_merge(
			parent::get_di_container_children(),
			array(
				/*
				ShopOrder::class,
				LinkingManager::class,
				Screens::class,*/
				Permissions::class,
				Settings::class,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function uninstall(): ?UninstallFailureException {
		if ( true === dws_lowc_get_validated_setting( 'remove-data-uninstall', 'plugin' ) ) {
			return parent::uninstall();
		}

		return null;
	}

	// endregion

	// region HOOKS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_plugin_actions( array $actions, string $plugin_file, array $plugin_data, string $context ): array {
		$action_links = array();

		if ( $this->is_active() ) {
			$action_links['settings'] = '<a href="' . dws_lowc_fs_settings_url() . '" aria-label="' . \esc_attr__( 'View settings', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Settings', 'linked-orders-for-woocommerce' ) . '</a>';
		}

		return \array_merge( $action_links, $actions );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_plugin_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
		if ( $this->get_plugin_basename() !== $plugin_file ) {
			return $plugin_meta;
		}

		$row_meta = array(
			'support' => '<a href="' . \esc_url( dws_lowc_fs()->get_support_forum_url() ) . '" aria-label="' . \esc_attr__( 'Visit community forums', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Community support', 'linked-orders-for-woocommerce' ) . '</a>',
			'contact' => '<a href="' . \esc_url( dws_lowc_fs()->contact_url() ) . '" aria-label="' . \esc_attr__( 'Send us an inquiry', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Contact us', 'linked-orders-for-woocommerce' ) . '</a>',
		);

		return \array_merge( $plugin_meta, $row_meta );
	}

	// endregion
}
