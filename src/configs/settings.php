<?php

use function DWS_LOWC_Deps\DI\factory;

defined( 'ABSPATH' ) || exit;

return array(
	'defaults' => array(
		'general' => array(
			'max-depth'                => 1,
			'autocomplete-descendants' => 'no',
		),
		'plugin'  => array(
			'remove-data-uninstall' => 'no',
		),
	),
	'options'  => array(
		'boolean' => array(
			'yes' => factory( fn() => _x( 'Yes', 'settings', 'linked-orders-for-woocommerce' ) ),
			'no'  => factory( fn() => _x( 'No', 'settings', 'linked-orders-for-woocommerce' ) ),
		),
	),
);
