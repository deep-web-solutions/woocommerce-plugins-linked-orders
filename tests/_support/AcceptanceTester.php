<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

	/**
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setTestCookie() {
		$this->amOnPage( '/' );
		$this->setCookie( 'TEST_REQUEST', 'true' );
	}

	/**
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $grant_access
	 * @param   bool    $is_wc_active
	 *
	 * @throws  Exception
	 */
	public function activate_lowc_free( bool $grant_access = true, bool $is_wc_active = false ) {
		$this->click( array( 'id' => 'activate-linked-orders-for-woocommerce' ) ); // doing it like this will trigger the Freemius redirect

		$this->see( 'Never miss an important update', '.fs-content' );
		$this->click( $grant_access ? 'Allow & Continue' : 'Skip' );

		if ( false === $is_wc_active ) {
			$this->waitForElement( array( 'class' => 'wp-heading-inline' ), 15 );
			$this->seeInCurrentUrl( 'plugins.php' );
			$this->seePluginActivated( 'linked-orders-for-woocommerce' );
		} else {
			$this->waitForElement( array( 'class' => 'woocommerce-layout__header' ), 15 );
			$this->seeInCurrentUrl( 'admin.php?page=wc-settings&tab=advanced&section=dws-linked-orders' );
		}
	}

    /**
     * @throws Exception
     * @version 1.2.0
     *
     * @since   1.2.0
     */
    public function activate_wc_and_lowc() {
        $this->activatePlugin( 'woocommerce' );
        $this->seePluginActivated( 'woocommerce' );
        $this->activate_lowc_free( true, true );
    }
}
