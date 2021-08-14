<?php

defined( 'ABSPATH' ) || exit;

/**
 * Returns the Freemius instance of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Freemius
 */
function dws_wc_lo_fs(): Freemius {
	global $dws_wc_lo_fs;

	if ( ! isset( $dws_wc_lo_fs ) ) {
		// Activate multisite network integration.
		if ( ! defined( 'WP_FS__PRODUCT_8072_MULTISITE' ) ) {
			define( 'WP_FS__PRODUCT_8072_MULTISITE', true );
		}

		// Include Freemius SDK.
		require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';

		/* @noinspection PhpUnhandledExceptionInspection */
		$dws_wc_lo_fs = fs_dynamic_init(
			array(
				'id'             => '8072',
				'slug'           => 'linked-orders-for-woocommerce',
				'type'           => 'plugin',
				'public_key'     => 'pk_ed8a99b52a5ab9fceb21e54bb5f53',
				'is_premium'     => false,
				'has_addons'     => false,
				'has_paid_plans' => false,
				'menu'           => array(
					'first-path' => 'plugins.php',
				),
			)
		);
	}

	return $dws_wc_lo_fs;
}

/**
 * Initializes the Freemius global instance and sets a few defaults.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  Freemius
 */
function dws_wc_lo_fs_init(): Freemius {
	$freemius = dws_wc_lo_fs();

	do_action( 'dws_wc_lo_fs_loaded' );

	$freemius->add_filter( 'after_skip_url', 'dws_wc_lo_fs_settings_url' );
	$freemius->add_filter( 'after_connect_url', 'dws_wc_lo_fs_settings_url' );
	$freemius->add_filter( 'after_pending_connect_url', 'dws_wc_lo_fs_settings_url' );

	return $freemius;
}

/**
 * Returns the URL to the settings page.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wc_lo_fs_settings_url(): string {
	return dws_wc_lo_instance()->is_active()
		? admin_url( 'admin.php?page=wc-settings&tab=advanced&section=dws-linked-orders' )
		: admin_url( 'plugins.php' );
}
