<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\PluginComponents\AbstractPermissions;

\defined( 'ABSPATH' ) || exit;

/**
 * Collection of permissions used by the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class Permissions extends AbstractPermissions {
	// region PERMISSION CONSTANTS

	/**
	 * Permission required to be able to see the linked orders metabox.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  public
	 * @var     string
	 */
	public const SEE_METABOX = 'see_dws_linked_orders_metabox';

	/**
	 * Permission required to be able to create new linked orders.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  public
	 * @var     string
	 */
	public const CREATE_LINKED_ORDERS = 'create_dws_linked_orders';

	// endregion

	// region INHERITED METHODS

	/**
	 * Grant default permissions to all admins and shop managers by default.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	public function get_granting_rules(): array {
		return array(
			'administrator' => 'all',
			'shop_manager'  => 'all',
		);
	}

	/**
	 * Leave the decision of whether to remove all data on uninstallation or not to the admin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function should_remove_data_on_uninstall(): bool {
		return dws_wc_lo_get_validated_setting( 'plugin_remove-data-uninstall' );
	}

	// endregion
}