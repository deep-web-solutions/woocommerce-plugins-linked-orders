<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionalityRoot;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeAdminNoticesServiceTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\InitializeDependenciesHandlersTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\SetupDependenciesAdminNoticesTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Checkers\WPPluginsChecker;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Handlers\SingleCheckerHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Helpers\DependenciesContextsEnum;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Helpers\DependenciesHelpersTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.0.0
 * @version 1.1.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
final class Plugin extends AbstractPluginFunctionalityRoot {
	// region TRAITS

	use DependenciesHelpersTrait;
	use InitializeAdminNoticesServiceTrait;
	use InitializeDependenciesHandlersTrait;
	use SetupDependenciesAdminNoticesTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Initializes the dependencies handler.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SingleCheckerHandler[]
	 */
	protected function get_dependencies_handlers(): array {
		static $handler = null;

		if ( \is_null( $handler ) ) {
			$handler_id = $this->get_dependencies_handler_id( DependenciesContextsEnum::ACTIVE_STATE );

			$checker = new WPPluginsChecker( $handler_id );
			$checker->register_dependency(
				array(
					'plugin'          => 'woocommerce/woocommerce.php',
					'name'            => 'WooCommerce',
					'min_version'     => '4.5.2',
					'version_checker' => function() {
						return \defined( 'WC_VERSION' ) ? WC_VERSION : '0.0.0';
					},
				)
			);

			$handler = new SingleCheckerHandler( $handler_id, $checker );
		}

		return array( $handler );
	}

	/**
	 * Register plugin components.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Functionality::register_children_functionalities()
	 *
	 * @return  array
	 */
	protected function get_di_container_children(): array {
		return \array_merge(
			parent::get_di_container_children(),
			array(
				ShopOrder::class,
				LinkingManager::class,
				Permissions::class,
				Screens::class,
				Settings::class,
			)
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Registers plugin actions on blog pages.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   string[]    $actions        An array of plugin action links.
	 * @param   string      $plugin_file    Path to the plugin file relative to the plugins directory.
	 * @param   array       $plugin_data    An array of plugin data. See `get_plugin_data()`.
	 * @param   string      $context        The plugin context. By default this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 *
	 * @return  string[]
	 */
	public function register_plugin_actions( array $actions, string $plugin_file, array $plugin_data, string $context ): array {
		$action_links = array();

		if ( $this->is_active() ) {
			$action_links['settings'] = '<a href="' . dws_wc_lo_fs_settings_url() . '" aria-label="' . \esc_attr__( 'View settings', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Settings', 'linked-orders-for-woocommerce' ) . '</a>';
		}

		return \array_merge( $action_links, $actions );
	}

	/**
	 * Register plugin meta information and/or links.
	 *
	 * @since   1.0.0
	 * @version 1.1.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   array   $plugin_meta    An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param   string  $plugin_file    Path to the plugin file relative to the plugins directory.
	 * @param   array   $plugin_data    An array of plugin data. See `get_plugin_data()`.
	 * @param   string  $status         Status filter currently applied to the plugin list. Possible values are: 'all', 'active', 'inactive', 'recently_activated',
	 *                                  'upgrade', 'mustuse', 'dropins', 'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
	 *
	 * @return  array
	 */
	public function register_plugin_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
		if ( $this->get_plugin_basename() !== $plugin_file ) {
			return $plugin_meta;
		}

		$row_meta = array(
			'support' => '<a href="' . \esc_url( dws_wc_lo_fs()->get_support_forum_url() ) . '" aria-label="' . \esc_attr__( 'Visit community forums', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Community support', 'linked-orders-for-woocommerce' ) . '</a>',
			'contact' => '<a href="' . \esc_url( dws_wc_lo_fs()->contact_url() ) . '" aria-label="' . \esc_attr__( 'Send us an inquiry', 'linked-orders-for-woocommerce' ) . '">' . \esc_html__( 'Contact us', 'linked-orders-for-woocommerce' ) . '</a>',
		);

		return \array_merge( $plugin_meta, $row_meta );
	}

	// endregion
}
