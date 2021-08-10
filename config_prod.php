<?php

use DeepWebSolutions\WC_Plugins\LinkedOrders\Plugin;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\Plugin\PluginInterface;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Handlers\DefaultHooksHandler;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
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
	// Plugin
	array(
		Plugin::class => autowire( Plugin::class )
			->constructorParameter( 'plugin_file_path', dws_wc_lo_path() ),
	),
	// Plugin aliases
	array()
);
