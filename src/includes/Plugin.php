<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionalityRoot;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\InitializePluginDependenciesContextHandlersTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\SetupActiveStateDependenciesAdminNoticesTrait;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\WooCommerce\WC_Helpers;

\defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.0.0
 * @version 1.2.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
final class Plugin extends AbstractPluginFunctionalityRoot {
	// region TRAITS

	use InitializePluginDependenciesContextHandlersTrait;
	use SetupActiveStateDependenciesAdminNoticesTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the WC plugin-level dependency.
	 *
	 * @since   1.0.0
	 * @version 1.1.0
	 *
	 * @return  array
	 */
	protected function get_plugin_dependencies_active(): array {
		return array(
			'plugin'         => 'woocommerce/woocommerce.php',
			'fallback_name'  => 'WooCommerce',
			'min_version'    => '4.5.2',
			'version_getter' => array( WC_Helpers::class, 'get_version' ),
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 */
	protected function get_di_container_children(): array {
		return \array_merge(
			parent::get_di_container_children(),
			array(
				Actions::class,
				Output::class,
				Permissions::class,
				Settings::class,
				Autocompletion::class,
				Integrations::class,
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
		global $wpdb;

		if ( true === dws_lowc_get_validated_setting( 'remove-data-uninstall', 'plugin' ) ) {
			$result = $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_dws_lo_%'" ); // phpcs:ignore WordPress.DB
			if ( false === $result ) {
				return new UninstallFailureException( \__( 'Failed to delete the orders links from the database', 'linked-orders-for-woocommerce' ) );
			}

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
	 * @version 1.2.0
	 */
	public function register_plugin_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
		if ( $this->get_plugin_basename() !== $plugin_file ) {
			return $plugin_meta;
		}

		$row_meta = array(
			'support' => '<a href="' . \esc_url( dws_lowc_fs()->get_support_forum_url() ) . '" aria-label="' . \esc_attr__( 'Visit community forums', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Community support', 'linked-orders-for-woocommerce' ) . '</a>',
		);

		return \array_merge( $plugin_meta, $row_meta );
	}

	// endregion
}
