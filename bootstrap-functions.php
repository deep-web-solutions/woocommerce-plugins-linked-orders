<?php
/**
 * Defines plugin-specific getters and functions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 *
 * @noinspection PhpMissingReturnTypeInspection
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the whitelabel name of the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wc_lo_name() {
	return defined( 'DWS_WC_LO_NAME' )
		? DWS_WC_LO_NAME : 'Linked Orders for WooCommerce';
}

/**
 * Returns the version of the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_wc_lo_version() {
	return defined( 'DWS_WC_LO_VERSION' )
		? DWS_WC_LO_VERSION : null;
}

/**
 * Returns the path to the plugin's main file.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_wc_lo_path() {
	return defined( 'DWS_WC_LO_PATH' )
		? DWS_WC_LO_PATH : null;
}

/**
 * Returns the minimum PHP version required to run the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_wc_lo_min_php() {
	return defined( 'DWS_WC_LO_MIN_PHP' )
		? DWS_WC_LO_MIN_PHP : null;
}

/**
 * Returns the minimum WP version required to run the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_wc_lo_min_wp() {
	return defined( 'DWS_WC_LO_MIN_WP' )
		? DWS_WC_LO_MIN_WP : null;
}
