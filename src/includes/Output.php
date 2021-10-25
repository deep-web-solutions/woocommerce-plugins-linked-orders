<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\OutputPermissions;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DWS_LO_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\WordPress\Users;

\defined( 'ABSPATH' ) || exit;

/**
 * Logical node for managing output nodes.
 *
 * @since   1.0.0
 * @version 1.1.1
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Output extends AbstractPluginFunctionality {
	// region TRAITS

	use ActiveLocalTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_active_local(): bool {
		return Users::has_capabilities( array( OutputPermissions::SEE_ORDER_LINKS ) ) ?? false;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		return array( Output\MetaBox::class, Output\ListTable::class );
	}

	// endregion
}
