<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Screens\MetaBox;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Screens\EditOrders;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Logical node for managing screen nodes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders
 */
class Screens extends AbstractPluginFunctionality {
	// region INHERITED METHODS

	/**
	 * Returns the list of screens that require adjustments for internal comments.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_di_container_children(): array {
		return array( EditOrders::class, MetaBox::class );
	}

	// endregion
}
