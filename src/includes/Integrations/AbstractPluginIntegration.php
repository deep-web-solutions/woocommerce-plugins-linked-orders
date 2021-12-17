<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Integrations;

use DWS_LOWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_LOWC_Deps\DeepWebSolutions\Framework\Utilities\Dependencies\Actions\InitializePluginDependenciesContextHandlersTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating some most-often needed functionalities of a plugin integration.
 *
 * @since   1.2.0
 * @version 1.2.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
abstract class AbstractPluginIntegration extends AbstractPluginFunctionality {
	// region TRAITS

	use InitializePluginDependenciesContextHandlersTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the plugin dependency.
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 *
	 * @return  array
	 */
	protected function get_plugin_dependencies_disabled(): array {
		return $this->get_dependent_plugin();
	}

	/**
	 * Returns the plugin dependency.
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 *
	 * @return  array
	 */
	abstract public function get_dependent_plugin(): array;

	// endregion
}
