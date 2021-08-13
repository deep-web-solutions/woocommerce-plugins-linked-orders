<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\LinkingManager;
use DeepWebSolutions\WC_Plugins\LinkedOrders\OrdersArchive;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Plugin\PluginInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\Handlers\WordPress_Handler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Handlers\DefaultHooksHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\Handlers\ContainerValidationHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationService;
use DWS_LO_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\WC_Handler;
use DWS_LO_Deps\DI\ContainerBuilder;
use function DWS_LO_Deps\DI\autowire;
use function DWS_LO_Deps\DI\factory;
use function DWS_LO_Deps\DI\get;

defined( 'ABSPATH' ) || exit;

return array_merge(
	// Foundations
	array(
		PluginInterface::class => get( Plugin::class ),
	),
	// Utilities
	array(
		HooksService::class => factory(
			function( Plugin $plugin, LoggingService $logging_service, DefaultHooksHandler $handler ) {
				$hooks_service = new HooksService( $plugin, $logging_service, $handler );
				$plugin->register_runnable_on_setup( $hooks_service );
				return $hooks_service;
			}
		),
	),
	// Settings
	array(
		SettingsService::class   => factory(
			function( Plugin $plugin, LoggingService $logging_service, HooksService $hooks_service ) {
				return new SettingsService( $plugin, $logging_service, $hooks_service, array( new WordPress_Handler(), new WC_Handler() ) );
			}
		),
		ValidationService::class => factory(
			function( Plugin $plugin, LoggingService $logging_service ) {
				$container         = ( new ContainerBuilder() )->addDefinitions( __DIR__ . '/src/configs/settings.php' )->build();
				$container_handler = new ContainerValidationHandler( 'default', $container );
				return new ValidationService( $plugin, $logging_service, array( $container_handler ) );
			}
		),
	),
	// Plugin
	array(
		Plugin::class                         => autowire( Plugin::class )
			->constructorParameter( 'plugin_file_path', dws_wc_lo_path() ),

		LinkingManager::class                 => autowire( LinkingManager::class )
			->constructorParameter( 'component_id', 'linking-manager' )
			->constructorParameter( 'component_name', 'Linking Manager' ),

		Permissions::class                    => autowire( Permissions::class )
			->constructorParameter( 'component_id', 'permissions' )
			->constructorParameter( 'component_name', 'Permissions' ),
		Permissions\LinkingPermissions::class => autowire( Permissions\LinkingPermissions::class )
			->constructorParameter( 'component_id', 'linking-permissions' )
			->constructorParameter( 'component_name', 'Linking Permissions' ),
		Permissions\ScreensPermissions::class => autowire( Permissions\ScreensPermissions::class )
			->constructorParameter( 'component_id', 'screens-permissions' )
			->constructorParameter( 'component_name', 'Screens Permissions' ),

		Settings::class                       => autowire( Settings::class )
			->constructorParameter( 'component_id', 'settings' )
			->constructorParameter( 'component_name', 'Settings' ),
		Settings\GeneralSettings::class       => autowire( Settings\GeneralSettings::class )
			->constructorParameter( 'component_id', 'general-settings' )
			->constructorParameter( 'component_name', 'General Settings' ),
		Settings\PluginSettings::class        => autowire( Settings\PluginSettings::class )
			->constructorParameter( 'component_id', 'plugin-settings' )
			->constructorParameter( 'component_name', 'Plugin Settings' ),

		OrdersArchive::class                  => autowire( OrdersArchive::class )
			->constructorParameter( 'component_id', 'orders-archive' )
			->constructorParameter( 'component_name', 'Orders Archive' ),
	),
	// Plugin aliases
	array(
		'linking-manager'     => get( LinkingManager::class ),

		'permissions'         => get( Permissions::class ),
		'linking-permissions' => get( Permissions\LinkingPermissions::class ),
		'screens-permissions' => get( Permissions\ScreensPermissions::class ),

		'settings'            => get( Settings::class ),
		'general-settings'    => get( Settings\GeneralSettings::class ),
		'plugin-settings'     => get( Settings\PluginSettings::class ),

		'orders-archive'      => get( OrdersArchive::class ),
	)
);
