<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Tests\Functional;

use FunctionalTester;

/**
 * Test plugin activation sequence.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WC_Plugins\LinkedOrders\Tests\Functional
 */
class ActivationCest {
	// region HOOKS

	/**
	 * Start every test on the plugins list page.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   FunctionalTester    $I      Instance of the Codeception actor.
	 */
	public function _before( FunctionalTester $I ) {
		$I->loginAsAdmin();

		$I->amOnPluginsPage();
        $I->seePluginDeactivated( 'woocommerce' );
		$I->seePluginDeactivated( 'linked-orders-for-woocommerce' );
	}

	// endregion

	// region TESTS

	/**
	 * Test that activating the plugin will set the proper version options in the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   FunctionalTester    $I      Instance of the Codeception actor.
	 */
	public function test_activation_timestamp( FunctionalTester $I ) {
		$I->dontSeeOptionInDatabase( 'linked_orders_for_woocommerce_version' );
		$I->dontSeeOptionInDatabase( 'linked_orders_for_woocommerce_original_version' );

        $I->activatePlugin( 'woocommerce' );
		$I->activatePlugin( 'linked-orders-for-woocommerce' );
		$I->seePluginActivated( 'linked-orders-for-woocommerce' );

		$I->seeOptionInDatabase( 'linked_orders_for_woocommerce_version' );
		$I->seeOptionInDatabase( 'linked_orders_for_woocommerce_original_version' );

		$original_version = $I->grabOptionFromDatabase( 'linked_orders_for_woocommerce_original_version' );
		$I->assertArrayHasKey( 'timestamp', $original_version );
		$I->assertLessThan( 10, time() - $original_version['timestamp'] );
	}

	// endregion
}
