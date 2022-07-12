<?php
/**
 * The Linked Orders for WooCommerce bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\WC-Plugins\LinkedOrders
 * @author              Deep Web Solutions
 * @copyright           2021 Deep Web Solutions
 * @license             GPL-3.0-or-later
 *
 * @noinspection        ALL
 * @fs_ignore           /dependencies, /vendor, /src
 *
 * @wordpress-plugin
 * Plugin Name:             Linked Orders for WooCommerce
 * Plugin URI:              https://www.deep-web-solutions.com/plugins/linked-orders-for-woocommerce/
 * Description:             A WooCommerce extension for creating orders that are logically connected to existing ones.
 * Version:                 1.2.2
 * Requires at least:       5.5
 * Requires PHP:            7.4
 * Author:                  Deep Web Solutions
 * Author URI:              https://www.deep-web-solutions.com
 * License:                 GPL-3.0+
 * License URI:             http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:             linked-orders-for-woocommerce
 * Domain Path:             /src/languages
 * WC requires at least:    4.5.2
 * WC tested up to:         6.7
 */

defined( 'ABSPATH' ) || exit;

// Start by autoloading dependencies and defining a few functions for running the bootstrapper.
is_file( __DIR__ . '/vendor/autoload.php' ) && require_once __DIR__ . '/vendor/autoload.php';

// Load plugin-specific bootstrapping functions.
require_once __DIR__ . '/bootstrap-functions.php';

// Check that the DWS WP Framework is loaded.
if ( ! function_exists( '\DWS_LOWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_get_bootstrapper_init_status' ) ) {
	add_action(
		'admin_notices',
		function() {
			$message      = wp_sprintf( /* translators: %s: Plugin name. */ __( 'It seems like <strong>%s</strong> is corrupted. Please reinstall!', 'linked-orders-for-woocommerce' ), dws_lowc_name() );
			$html_message = wp_sprintf( '<div class="error notice dws-plugin-corrupted-error">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}
	);
	return;
}

// Define plugin constants.
define( 'DWS_LOWC_NAME', DWS_LOWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_name() . ': Linked Orders for WooCommerce' );
define( 'DWS_LOWC_VERSION', '1.2.2' );
define( 'DWS_LOWC_PATH', __FILE__ );

// Define minimum environment requirements.
define( 'DWS_LOWC_MIN_PHP', '7.4' );
define( 'DWS_LOWC_MIN_WP', '5.5' );

// Start plugin initialization if system requirements check out.
if ( DWS_LOWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_check_php_wp_requirements_met( dws_lowc_min_php(), dws_lowc_min_wp() ) ) {
	if ( ! function_exists( 'dws_lowc_fs' ) ) {
		include __DIR__ . '/freemius.php';
		dws_lowc_fs_init();
	}

	include __DIR__ . '/functions.php';
	add_action( 'plugins_loaded', 'dws_lowc_instance_initialize' );
	register_activation_hook( __FILE__, 'dws_lowc_plugin_activate' );
} else {
	DWS_LOWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_output_requirements_error( dws_lowc_name(), dws_lowc_version(), dws_lowc_min_php(), dws_lowc_min_wp() );
}
