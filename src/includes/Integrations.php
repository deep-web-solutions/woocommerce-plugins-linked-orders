<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Logical node for all integration functionalities.
 *
 * @since   1.2.0
 * @version 1.2.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
final class Integrations extends AbstractPluginFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 */
	protected function get_di_container_children(): array {
		return array( Integrations\WCSequentialOrderNumbersPro::class );
	}

	// endregion
}
