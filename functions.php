<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Exceptions\NotSupportedException;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
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
		$container = ( new ContainerBuilder() )
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

// region SETTINGS

/**
 * Returns the raw database value of a plugin's option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $field_id   ID of the option field to retrieve.
 *
 * @return  mixed|null
 */
function dws_wc_lo_get_raw_setting( string $field_id ) {
	try {
		$settings = dws_wc_lo_di_container()->get( 'settings' );
		return $settings->get_setting( $field_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Returns the validated database value of a plugin's option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $field_id   ID of the option field to retrieve.
 *
 * @return  mixed|null
 */
function dws_wc_lo_get_validated_setting( string $field_id ) {
	try {
		$settings = dws_wc_lo_di_container()->get( 'settings' );
		return $settings->get_validated_setting( $field_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

// endregion

// region MISC

/**
 * Converts a WC Order reference to a DWS_Linked_Order object and reads its metadata from the database.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order  Order to retrieve.
 *
 * @return  DWS_Order_Node|null
 */
function dws_wc_lo_get_order_node( $order ): ?DWS_Order_Node {
	try {
		$dws_order = new DWS_Order_Node( $order );
		$dws_order->read();

		return $dws_order;
	} catch ( NotSupportedException $exception ) {
		return null;
	}
}

/**
 * Determines whether a given order is a root order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order  Order to retrieve.
 *
 * @return  bool|null
 */
function dws_wc_lo_is_root_order( $order ): ?bool {
	$order = dws_wc_lo_get_order_node( $order );
	if ( is_null( $order ) ) {
		return null;
	}

	return 0 === $order->get_depth();
}

/**
 * Determines whether a given user can create linked orders for a given order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Order|int    $order      Order to retrieve.
 * @param   int|null        $user_id    The ID of the user to check for.
 *
 * @return bool|null
 */
function dws_wc_lo_can_create_linked_order( $order, ?int $user_id = null ): ?bool {
	$order = dws_wc_lo_get_order_node( $order );
	if ( is_null( $order ) ) {
		return null;
	}

	return $order->can_create_linked_order( $user_id );
}

// endregion
