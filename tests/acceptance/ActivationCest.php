<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Tests\Acceptance;

use AcceptanceTester;
use Codeception\Scenario;
use Exception;

/**
 * Test that activation is successful and that all the proper admin notices are outputted.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC_Plugins\LinkedOrders\Tests\Acceptance
 */
class ActivationCest {
	// region HOOKS

	/**
	 * Start every test on the plugins list page.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AcceptanceTester    $I      Instance of the Codeception actor.
	 */
	public function _before( AcceptanceTester $I ) {
		$I->setTestCookie();
		$I->loginAsAdmin();

		$I->amOnPluginsPage();
		$I->seePluginDeactivated( 'woocommerce' );
		$I->seePluginDeactivated( 'linked-orders-for-woocommerce' );
	}

	// endregion

	// region TESTS

	/**
	 * Test that activating the free versions together doesn't break the site.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AcceptanceTester    $I      Instance of the Codeception actor.
	 *
	 * @throws  Exception   Thrown if plugin activation fails.
	 */
	public function test_activating_free( AcceptanceTester $I ) {
		$I->activate_lowc_free();
		$I->see( 'Linked Orders for WooCommerce was successfully installed.', '.dws-framework-notice' );
	}

	// endregion
}
