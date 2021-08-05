<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DWS_LO_Deps\DI\Container;
use DWS_LO_Deps\DI\ContainerBuilder;

defined( 'ABSPATH' ) || exit;

// region PLUGIN

/**
 * Returns a container singleton that enables one to setup unit testing by passing an environment file for class mapping in PHP-DI.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $environment    The environment rules that the container should be initialized on.
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Container
 */
function dws_wc_lo_di_container( string $environment = 'prod' ): Container {
	static $container = null;

	if ( is_null( $container ) ) {
		/* @noinspection PhpUnhandledExceptionInspection */
		$container = (new ContainerBuilder())
			->addDefinitions( __DIR__ . "/config_$environment.php" )
			->build();
	}

	return $container;
}

/**
 * Returns the plugin's main class instance.
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Plugin
 */
function dws_wc_lo_instance(): Plugin {
	/* @noinspection PhpUnhandledExceptionInspection */
	return dws_wc_lo_di_container()->get( Plugin::class );
}

/**
 * Returns a plugin component by its container ID.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $component_id   The ID of the component as defined in the DI container.
 *
 * @return  AbstractPluginFunctionality|null
 */
function dws_wc_lo_component( string $component_id ): ?AbstractPluginFunctionality {
	try {
		return dws_wc_lo_di_container()->get( $component_id );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * Initialization function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  InitializationFailureException|null
 */
function dws_wc_lo_instance_initialize(): ?InitializationFailureException {
	return dws_wc_lo_instance()->initialize();
}

/**
 * Activate function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wc_lo_plugin_activate() {
	if ( is_null( dws_wc_lo_instance_initialize() ) ) {
		dws_wc_lo_instance()->activate();
	}
}

/**
 * Uninstall function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wc_lo_plugin_uninstall() {
	if ( is_null( dws_wc_lo_instance_initialize() ) ) {
		dws_wc_lo_instance()->uninstall();
	}
}
add_action( 'fs_after_uninstall_linked-orders-for-woocommerce', 'dws_wc_lo_plugin_uninstall' );

// endregion
