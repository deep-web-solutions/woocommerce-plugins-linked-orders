<?php

use function DWS_LO_Deps\DI\factory;

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
			'yes' => factory(
				function() {
					return _x( 'Yes', 'settings', 'linked-orders-for-woocommerce' );
				}
			),
			'no'  => factory(
				function() {
					return _x( 'No', 'settings', 'linked-orders-for-woocommerce' );
				}
			),
		),
	),
);
