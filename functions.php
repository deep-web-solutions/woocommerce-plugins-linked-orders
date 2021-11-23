<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DWS_LOWC_Deps\DI\Container;
use DWS_LOWC_Deps\DI\ContainerBuilder;

defined( 'ABSPATH' ) || exit;

// region DEPENDENCY INJECTION

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
function dws_lowc_di_container( string $environment = 'prod' ): Container {
	static $container = null;

	if ( is_null( $container ) ) {
		/* @noinspection PhpUnhandledExceptionInspection */
		$container = ( new ContainerBuilder() )
			->addDefinitions( __DIR__ . "/config_$environment.php" )
			->build();
	}

	return $container;
}

/**
 * Returns the plugin's main class instance.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Plugin
 */
function dws_lowc_instance(): Plugin {
	/* @noinspection PhpUnhandledExceptionInspection */
	return dws_lowc_di_container()->get( Plugin::class );
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
function dws_lowc_component( string $component_id ): ?AbstractPluginFunctionality {
	try {
		return dws_lowc_di_container()->get( $component_id );
	} catch ( Exception $e ) {
		return null;
	}
}

// endregion

// region LIFECYCLE

/**
 * Initialization function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  InitializationFailureException|null
 */
function dws_lowc_instance_initialize(): ?InitializationFailureException {
	$result = dws_lowc_instance()->initialize();

	if ( is_null( $result ) ) {
		do_action( 'dws_lowc_initialized' );
	} else {
		do_action( 'dws_lowc_initialization_failure', $result );
	}

	return $result;
}

/**
 * Activate function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_lowc_plugin_activate() {
	if ( is_null( dws_lowc_instance_initialize() ) ) {
		dws_lowc_instance()->activate();
	}
}

/**
 * Uninstall function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_lowc_plugin_uninstall() {
	if ( is_null( dws_lowc_instance_initialize() ) ) {
		dws_lowc_instance()->uninstall();
	}
}
add_action( 'fs_after_uninstall_linked-orders-for-woocommerce', 'dws_lowc_plugin_uninstall' );

// endregion

// region HOOKS

/**
 * Shorthand for generating a plugin-level hook tag.
 *
 * @since   1.0.0
 * @version 1.1.1
 *
 * @param   string              $name       The actual descriptor of the hook's purpose.
 * @param   string|string[]     $extra      Further descriptor of the hook's purpose.
 *
 * @return  string|null
 */
function dws_lowc_get_hook_tag( string $name, $extra = array() ): ?string {
	return dws_lowc_instance()->get_hook_tag( $name, $extra );
}

/**
 * Shorthand for generating a component-level hook tag.
 *
 * @since   1.0.0
 * @version 1.1.1
 *
 * @param   string              $component_id   The ID of the component as defined in the DI container.
 * @param   string              $name           The actual descriptor of the hook's purpose.
 * @param   string|string[]     $extra          Further descriptor of the hook's purpose.
 *
 * @return  string|null
 */
function dws_lowc_get_component_hook_tag( string $component_id, string $name, $extra = array() ): ?string {
	$component = dws_lowc_component( $component_id );
	if ( is_null( $component ) ) {
		return null;
	}

	if ( ! did_action( 'dws_lowc_initialized' ) ) {
		$component->set_plugin( dws_lowc_instance() );
	}

	return $component->get_hook_tag( $name, $extra );
}

// endregion

// region OTHERS

require plugin_dir_path( __FILE__ ) . 'src/functions/settings.php';
require plugin_dir_path( __FILE__ ) . 'src/functions/post-type.php';
require plugin_dir_path( __FILE__ ) . 'src/functions/linking.php';

// endregion
