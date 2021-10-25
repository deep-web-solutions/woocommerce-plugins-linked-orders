<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions;

use DWS_LO_Deps\DeepWebSolutions\Framework\Core\PluginComponents\AbstractPermissionsChildFunctionality;

/**
 * Collection of permissions used by the output portion of the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class OutputPermissions extends AbstractPermissionsChildFunctionality {
	// region PERMISSION CONSTANTS

	/**
	 * Capability needed to be able to see the linked orders output at all.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	public const SEE_ORDER_LINKS = 'see_dws_order_links';

	/**
	 * Capability needed to be able to see the linked orders metabox.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	public const SEE_METABOX = 'see_dws_linked_orders_metabox';

	/**
	 * Capability needed to be able to see the linked orders table column.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	public const SEE_TABLE_COLUMN = 'see_dws_linked_orders_column';

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
