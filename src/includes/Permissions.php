<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\PluginComponents\AbstractPermissionsFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Collection of permissions used by the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class Permissions extends AbstractPermissionsFunctionality {
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
	 * Permission required to be able to see the linked orders metabox.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  public
	 * @var     string
	 */
	public const SEE_METABOX = 'see_dws_linked_orders_metabox';

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_granting_rules(): array {
		return array(
			'administrator' => 'all',
			'shop_manager'  => 'all',
		);
	}

	// endregion
}
