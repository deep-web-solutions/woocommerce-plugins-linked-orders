<?php

namespace DeepWebSolutions\WC_Plugins\LinkedOrders\Tests\Acceptance;

use AcceptanceTester;
use Codeception\Scenario;
use Exception;

/**
 * Test that activation is successful and that all the proper admin notices are outputted.
 *
 * @since   1.2.0
 * @version 1.2.0
 * @author  Cristina Hegyes <c.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC_Plugins\LinkedOrders\Tests\Acceptance
 */

class CreateLOCest {

	// region HOOKS

	/**
	 * Start every test on the plugins list page.
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 *
	 * @param AcceptanceTester $I Instance of the Codeception actor.
	 *
	 * @throws Exception
	 */
	public function _before( AcceptanceTester $I ) {
		$I->setTestCookie();
		$I->loginAsAdmin();

		$I->amOnPluginsPage();
		$I->seePluginDeactivated( 'woocommerce' );
		$I->seePluginDeactivated( 'linked-orders-for-woocommerce' );
		$I->activate_wc_and_lowc();
	}

	// endregion

	// region TESTS

	/**
	 * Test that creating a linked order is working properly from the edit order screen.
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 *
	 * @param   AcceptanceTester    $I      Instance of the Codeception actor.
	 */

	public function test_create_LO_editorder( AcceptanceTester $I ) {
		// create order
		$I->amOnAdminPage( 'edit.php?post_type=shop_order' );
		$I->click( array( 'class' => 'page-title-action' ) );
		$I->click( 'Add item(s)' );
		$I->wait( 5 );
		$I->click( 'Add product(s)' );
		$I->click( '#wc-backbone-modal-dialog span.selection' );
		$I->fillField( 'span.select2-search.select2-search--dropdown input[type=text]', 'Album' );
		$I->wait( 5 );
		$I->pressKey( 'span.select2-search.select2-search--dropdown input[type=text]', \Facebook\WebDriver\WebDriverKeys::ENTER );
		$I->click( '#btn-ok' );
		$I->scrollTo( '#wpbody-content h1' );
		$I->click( '#woocommerce-order-actions button[type="submit"]' );

		// create LO
		$I->see( 'Linked Orders' );
		$I->see( 'There are no attached children' );
		$I->see( 'Add new child Order' );
		$I->click( 'Add new child Order' );
		$I->click( 'Add item(s)' );
		$I->click( 'Add product(s)' );
		$I->click( '#wc-backbone-modal-dialog span.selection' );
		$I->fillField( 'span.select2-search.select2-search--dropdown input[type=text]', 'T-shirt' );
		$I->wait( 5 );
		$I->pressKey( 'span.select2-search.select2-search--dropdown input[type=text]', \Facebook\WebDriver\WebDriverKeys::ENTER );
		$I->click( '#btn-ok' );
		$I->scrollTo( '#wpbody-content h1' );
		$I->click( '#woocommerce-order-actions button[type="submit"]' );

		// visualization edit order
		$I->see( 'Linked Orders' );
		$I->see( 'Parent:' );
		$I->seeElement( '#dws-linked-orders div.dws-linked-orders__parent a' );
		$I->click( '#dws-linked-orders div.dws-linked-orders__parent a' );
		$I->switchToNextTab();
		$I->see( 'Linked Orders' );
		$I->see( 'Attached children:' );
		$I->seeElement( '#dws-linked-orders a' );
		$I->switchToPreviousTab();
		$I->wait( 5 );
	}

	/**
	 * Test that creating a linked order is working properly from the orders list table.
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 *
	 * @param   AcceptanceTester    $I      Instance of the Codeception actor.
	 */

	public function test_create_LO_orderslist( AcceptanceTester $I ) {
		// create order
		$I->amOnAdminPage( 'edit.php?post_type=shop_order' );
		$I->click( array( 'class' => 'page-title-action' ) );
		$I->click( 'Add item(s)' );
		$I->wait( 5 );
		$I->click( 'Add product(s)' );
		$I->click( '#wc-backbone-modal-dialog span.selection' );
		$I->fillField( 'span.select2-search.select2-search--dropdown input[type=text]', 'Album' );
		$I->wait( 5 );
		$I->pressKey( 'span.select2-search.select2-search--dropdown input[type=text]', \Facebook\WebDriver\WebDriverKeys::ENTER );
		$I->click( '#btn-ok' );
		$I->scrollTo( '#wpbody-content h1' );
		$I->click( '#woocommerce-order-actions button[type="submit"]' );

		$I->moveMouseOver( '#toplevel_page_woocommerce' );
		$I->click( 'Orders' );
		$I->see( 'Root Order' );

		// create LO
		$I->see( 'Screen Options' );
		$I->click( 'Screen Options' );
		$I->wait( 5 );
		$I->see( 'Actions' );
		$I->seeElement( 'input[type="checkbox"]#wc_actions-hide' );
		$I->click( 'input[type="checkbox"]#wc_actions-hide' );
		$I->wait( 5 );
		$I->see( 'Root Order' );
		$I->click( 'input[type="submit"]#screen-options-apply' );
		$I->click( 'a.button.wc-action-button.wc-action-button-create-empty-linked-child.create-empty-linked-child' );
		$I->click( 'Update' );

		// visualization orders list
		$I->moveMouseOver( '#toplevel_page_woocommerce' );
		$I->click( 'Orders' );
		$I->see( 'Root Order' );
		$I->see( 'Child Order (level 2)' );
	}

	/**
	 * Test permissions.
	 *
	 * @param AcceptanceTester $I Instance of the Codeception actor.
	 *
	 * @throws Exception
	 *
	 * @since   1.2.0
	 * @version 1.2.0
	 */
	public function test_permission( AcceptanceTester $I ) {
		$editor_id = $I->haveUserInDatabase( 'test_editor', 'editor' );
		$I->haveUserCapabilitiesInDatabase(
			$editor_id,
			array(
				'editor'                     => true,
				'manage_woocommerce'         => true,
				'woocommerce_order_itemmeta' => true,
				'edit_shop_orders'           => true,
			)
		);
		// create order
		$I->logOut();
		$I->loginAs( 'test_editor', 'test_editor' );
		$I->amOnAdminPage( 'edit.php?post_type=shop_order' );
		$I->click( array( 'class' => 'page-title-action' ) );
		$I->click( 'Add item(s)' );
		$I->wait( 5 );
		$I->click( 'Add product(s)' );
		$I->click( '#wc-backbone-modal-dialog span.selection' );
		$I->fillField( 'span.select2-search.select2-search--dropdown input[type=text]', 'Album' );
		$I->wait( 5 );
		$I->pressKey( 'span.select2-search.select2-search--dropdown input[type=text]', \Facebook\WebDriver\WebDriverKeys::ENTER );
		$I->click( '#btn-ok' );
		$I->scrollTo( '#wpbody-content h1' );
		$I->click( '#woocommerce-order-actions button[type="submit"]' );

		// LO
		$I->dontSee( 'Linked Orders' );
		$I->dontSee( 'There are no attached children' );
		$I->dontSee( 'Add new child Order' );
		$I->moveMouseOver( '#toplevel_page_woocommerce' );
		$I->click( 'Orders' );
		$I->dontSee( 'Root Order' );

		// enable actions
		$I->see( 'Screen Options' );
		$I->click( 'Screen Options' );
		$I->wait( 5 );
		$I->see( 'Actions' );
		$I->seeElement( 'input[type="checkbox"]#wc_actions-hide' );
		$I->click( 'input[type="checkbox"]#wc_actions-hide' );
		$I->wait( 5 );
		$I->click( 'input[type="submit"]#screen-options-apply' );
		$I->dontSeeElement( 'a.button.wc-action-button.wc-action-button-create-empty-linked-child.create-empty-linked-child' );
		$I->dontSeeElement( 'a.button.wc-action-button.wc-action-button-view-all-linked-orders.view-all-linked-orders' );

		$I->haveUserCapabilitiesInDatabase(
			$editor_id,
			array(
				'editor'                        => true,
				'manage_woocommerce'            => true,
				'woocommerce_order_itemmeta'    => true,
				'edit_shop_orders'              => true,
				'edit_others_shop_orders'       => true,
				'publish_shop_orders'           => true,
				'create_dws_linked_children'    => true,
				'see_dws_order_links'           => true,
				'see_dws_linked_orders_metabox' => true,
				'see_dws_linked_orders_column'  => true,
			)
		);
		$I->reloadPage();

		// create LO
		$I->see( 'Root Order' );
		$I->seeElement( 'a.button.wc-action-button.wc-action-button-create-empty-linked-child.create-empty-linked-child' );
		$I->click( 'a.button.wc-action-button.wc-action-button-create-empty-linked-child.create-empty-linked-child' );
		$I->see( 'Linked Orders' );
		$I->see( 'Parent:' );
		$I->seeElement( '#dws-linked-orders div.dws-linked-orders__parent a' );
		$I->click( '#dws-linked-orders div.dws-linked-orders__parent a' );
		$I->switchToNextTab();
		$I->see( 'Linked Orders' );
		$I->see( 'Attached children:' );
		$I->seeElement( '#dws-linked-orders a' );
		$I->switchToPreviousTab();
		$I->moveBack();
		$I->reloadPage();
		$I->seeElement( 'a.button.wc-action-button.wc-action-button-view-all-linked-orders.view-all-linked-orders' );
		$I->wait( 5 );
	}

	// endregion

}
