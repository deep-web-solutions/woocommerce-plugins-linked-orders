<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders;

use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\LinkingPermissions;
use DeepWebSolutions\WC_Plugins\LinkedOrders\Permissions\ScreensPermissions;
use DWS_LO_Deps\DeepWebSolutions\Framework\Core\PluginComponents\AbstractPermissions;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DWS_LO_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

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
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the list of permissions collections of the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_di_container_children(): array {
		return array(
			LinkingPermissions::class,
			ScreensPermissions::class,
		);
	}

	/**
	 * Registers actions and filters with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'map_meta_cap', $this, 'map_meta_cap', 10, 4 );
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

	// region HOOKS

	/**
	 * Maps meta capabilities to primitive capabilities defined by this class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string[]    $caps       Primitive capabilities required of the user.
	 * @param   string      $cap        Capability being checked.
	 * @param   int         $user_id    The user ID.
	 * @param   array       $args       Adds context to the capability check, typically starting with an object ID.
	 *
	 * @return  array
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		switch ( $cap ) {
			case 'create_dws_linked_order':
				$order = \wc_get_order( $args[0] );
				if ( empty( $order ) ) {
					$caps[] = 'do_not_allow';
					break;
				}

				$caps = \apply_filters( $this->get_hook_tag( 'create_dws_linked_order' ), array( LinkingPermissions::CREATE_LINKED_ORDERS ), $order, $user_id, $args );
				break;
		}

		return $caps;
	}

	// endregion
}
