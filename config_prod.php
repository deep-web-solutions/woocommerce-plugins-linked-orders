<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\LinkingManager;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Output;
use DeepWebSolutions\WC_Plugins\LinkedOrders\ShopOrder;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Plugin\PluginInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Request;
use DWS_LO_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\Handlers\ContainerValidationHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationService;
use DWS_LO_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\WC_Handler;
use DWS_LO_Deps\DeepWebSolutions\Framework\WooCommerce\Utilities\WC_LoggingHandler;
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
		HooksService::class   => factory(
			function( Plugin $plugin, LoggingService $logging_service ) {
				$hooks_service = new HooksService( $plugin, $logging_service );
				$plugin->register_runnable_on_setup( $hooks_service );
				return $hooks_service;
			}
		),
		LoggingService::class => factory(
			function( PluginInterface $plugin ) {
				$logging_handlers = array();

				if ( class_exists( 'WC_Log_Levels' ) ) { // in case the WC plugin is not active
					$min_log_level  = Request::has_debug() ? WC_Log_Levels::DEBUG : WC_Log_Levels::ERROR;
					$wc_log_handler = new WC_Log_Handler_File();

					$logging_handlers = array(
						new WC_LoggingHandler( 'framework', array( $wc_log_handler ), $min_log_level ),
						new WC_LoggingHandler( 'plugin', array( $wc_log_handler ), $min_log_level ),
					);
				}

				return new LoggingService( $plugin, $logging_handlers, Request::has_debug() );
			}
		),
	),
	// Settings
	array(
		SettingsService::class   => factory(
			function( Plugin $plugin, LoggingService $logging_service, HooksService $hooks_service ) {
				return new SettingsService( $plugin, $logging_service, $hooks_service, array( new WC_Handler() ) );
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
		Plugin::class                        => autowire( Plugin::class )
			->constructorParameter( 'plugin_file_path', dws_lowc_path() ),

		Output::class                        => autowire( Output::class )
			->constructorParameter( 'component_id', 'output' )
			->constructorParameter( 'component_name', 'Output' ),
		Output\MetaBox::class                => autowire( Output\MetaBox::class )
			->constructorParameter( 'component_id', 'metabox-output' )
			->constructorParameter( 'component_name', 'MetaBox Output' ),
		Output\ListTable::class              => autowire( Output\ListTable::class )
			->constructorParameter( 'component_id', 'list-table-output' )
			->constructorParameter( 'component_name', 'List Table Output' ),

		Permissions::class                   => autowire( Permissions::class )
			->constructorParameter( 'component_id', 'permissions' )
			->constructorParameter( 'component_name', 'Permissions' ),
		Permissions\OutputPermissions::class => autowire( Permissions\OutputPermissions::class )
			->constructorParameter( 'component_id', 'output-permissions' )
			->constructorParameter( 'component_name', 'Output Permissions' ),

		Settings::class                      => autowire( Settings::class )
			->constructorParameter( 'component_id', 'settings' )
			->constructorParameter( 'component_name', 'Settings' ),
		Settings\GeneralSettings::class      => autowire( Settings\GeneralSettings::class )
			->constructorParameter( 'component_id', 'general-settings' )
			->constructorParameter( 'component_name', 'General Settings' ),
		Settings\PluginSettings::class       => autowire( Settings\PluginSettings::class )
			->constructorParameter( 'component_id', 'plugin-settings' )
			->constructorParameter( 'component_name', 'Plugin Settings' ),




		ShopOrder::class                     => autowire( ShopOrder::class )
			->constructorParameter( 'component_id', 'shop-order' )
			->constructorParameter( 'component_name', 'Shop Order' ),
		LinkingManager::class                => autowire( LinkingManager::class )
			->constructorParameter( 'component_id', 'linking-manager' )
			->constructorParameter( 'component_name', 'Linking Manager' ),
	),
	// Plugin aliases
	array(
		'output'             => get( Output::class ),
		'metabox-output'     => get( Output\MetaBox::class ),
		'list-table-output'  => get( Output\ListTable::class ),

		'permissions'        => get( Permissions::class ),
		'output-permissions' => get( Permissions\OutputPermissions::class ),

		'settings'           => get( Settings::class ),
		'general-settings'   => get( Settings\GeneralSettings::class ),
		'plugin-settings'    => get( Settings\PluginSettings::class ),




		'shop-order'         => get( ShopOrder::class ),
		'linking-manager'    => get( LinkingManager::class ),
	)
);
