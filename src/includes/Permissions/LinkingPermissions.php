<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\PluginComponents\AbstractPermissions;

\defined( 'ABSPATH' ) || exit;

/**
 * Collection of permissions used by the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\Permissions
 */
class LinkingPermissions extends AbstractPermissions {
	// region PERMISSION CONSTANTS

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

	/**
	 * Permission required to be able to edit linked orders.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  public
	 * @var     string
	 */
	public const EDIT_LINKED_ORDERS = 'edit_dws_linked_orders';

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

	// endregion
}
