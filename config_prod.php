<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\Actions;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Integrations;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Output;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Autocompletion;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Settings;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Foundations\PluginInterface;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Helpers\Request;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\Handlers\ContainerValidationHandler;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationService;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\WooCommerce\Logging\WC_LoggingHandler;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\WC_SettingsHandler;
use DWS_LOWC_Deps\DI\ContainerBuilder;
use function DWS_LOWC_Deps\DI\autowire;
use function DWS_LOWC_Deps\DI\factory;
use function DWS_LOWC_Deps\DI\get;

defined( 'ABSPATH' ) || exit;

return array_merge(
	// Foundations
	array(
		PluginInterface::class => get( Plugin::class ),
		LoggingService::class  => factory(
			function( PluginInterface $plugin ) {
				$logging_handlers = array();
				$is_debug_active  = Request::has_debug( 'DWS_LOWC_DEBUG' );

				if ( class_exists( 'WC_Log_Levels' ) ) { // in case the WC plugin is not active
					$min_log_level  = $is_debug_active ? WC_Log_Levels::DEBUG : WC_Log_Levels::ERROR;

					$logging_handlers = array(
						new WC_LoggingHandler( 'framework', null, $min_log_level ),
						new WC_LoggingHandler( 'plugin', null, $min_log_level ),
					);
				}

				return new LoggingService( $plugin, $logging_handlers, $is_debug_active );
			}
		),
	),
	// Settings
	array(
		'settings-validation-handler' => factory(
			function() {
				$config    = require_once __DIR__ . '/src/configs/settings.php';
				$container = ( new ContainerBuilder() )->addDefinitions( $config )->build();

				return new ContainerValidationHandler( 'settings', $container );
			}
		),

		SettingsService::class        => autowire( SettingsService::class )
			->method( 'register_handler', new WC_SettingsHandler() ),
		ValidationService::class      => autowire( ValidationService::class )
			->method( 'register_handler', get( 'settings-validation-handler' ) ),
	),
	// Plugin
	array(
		Plugin::class                                   => autowire( Plugin::class )
			->constructorParameter( 'plugin_slug', 'linked-orders-for-woocommerce' )
			->constructorParameter( 'plugin_file_path', dws_lowc_path() ),
		Actions::class                                  => autowire( Actions::class )
			->constructorParameter( 'component_id', 'actions' )
			->constructorParameter( 'component_name', 'Actions' ),

		Output::class                                   => autowire( Output::class )
			->constructorParameter( 'component_id', 'output' )
			->constructorParameter( 'component_name', 'Output' ),
		Output\MetaBox::class                           => autowire( Output\MetaBox::class )
			->constructorParameter( 'component_id', 'metabox-output' )
			->constructorParameter( 'component_name', 'MetaBox Output' ),
		Output\ListTable::class                         => autowire( Output\ListTable::class )
			->constructorParameter( 'component_id', 'list-table-output' )
			->constructorParameter( 'component_name', 'List Table Output' ),

		Permissions::class                              => autowire( Permissions::class )
			->constructorParameter( 'component_id', 'permissions' )
			->constructorParameter( 'component_name', 'Permissions' ),
		Permissions\OutputPermissions::class            => autowire( Permissions\OutputPermissions::class )
			->constructorParameter( 'component_id', 'output-permissions' )
			->constructorParameter( 'component_name', 'Output Permissions' ),

		Integrations::class                             => autowire( Integrations::class )
			->constructorParameter( 'component_id', 'integrations' )
			->constructorParameter( 'component_name', 'Integrations' ),
		Integrations\WCSequentialOrderNumbersPro::class => autowire( Integrations\WCSequentialOrderNumbersPro::class )
			->constructorParameter( 'component_id', 'wc-sequential-order-numbers-pro-integration' )
			->constructorParameter( 'component_name', 'WC Sequential Order Numbers Pro Integration' ),

		Settings::class                                 => autowire( Settings::class )
			->constructorParameter( 'component_id', 'settings' )
			->constructorParameter( 'component_name', 'Settings' ),
		Settings\GeneralSettings::class                 => autowire( Settings\GeneralSettings::class )
			->constructorParameter( 'component_id', 'general-settings' )
			->constructorParameter( 'component_name', 'General Settings' ),
		Settings\PluginSettings::class                  => autowire( Settings\PluginSettings::class )
			->constructorParameter( 'component_id', 'plugin-settings' )
			->constructorParameter( 'component_name', 'Plugin Settings' ),

		Autocompletion::class                           => autowire( Autocompletion::class )
			->constructorParameter( 'component_id', 'autocompletion' )
			->constructorParameter( 'component_name', 'Autocompletion' ),
	),
	// Plugin aliases
	array(
		'actions'                                     => get( Actions::class ),
		'autocompletion'                              => get( Autocompletion::class ),

		'output'                                      => get( Output::class ),
		'metabox-output'                              => get( Output\MetaBox::class ),
		'list-table-output'                           => get( Output\ListTable::class ),

		'permissions'                                 => get( Permissions::class ),
		'output-permissions'                          => get( Permissions\OutputPermissions::class ),

		'integrations'                                => get( Integrations::class ),
		'wc-sequential-order-numbers-pro-integration' => get( Integrations\WCSequentialOrderNumbersPro::class ),

		'settings'                                    => get( Settings::class ),
		'general-settings'                            => get( Settings\GeneralSettings::class ),
		'plugin-settings'                             => get( Settings\PluginSettings::class ),
	)
);
