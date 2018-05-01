<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		AuthorizeNetCim
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_AuthorizeNetCim_Model_Ach_Card extends ParadoxLabs_AuthorizeNetCim_Model_Card
{
	/**
	 * Set card payment data from a quote or order payment instance.
	 */
	public function importPaymentInfo( Mage_Payment_Model_Info $payment )
	{
		parent::importPaymentInfo( $payment );
		
		if( $payment instanceof Mage_Payment_Model_Info ) {
			if( $payment->getEcheckAccountName() != '' ) {
				$this->setAdditional( 'echeck_account_name', $payment->getEcheckAccountName() );
			}
			
			if( $payment->getEcheckBankName() != '' ) {
				$this->setAdditional( 'echeck_bank_name', $payment->getEcheckBankName() );
			}
			
			if( $payment->getEcheckAccountType() != '' ) {
				$this->setAdditional( 'echeck_account_type', $payment->getEcheckAccountType() );
			}
			
			if( $payment->getEcheckRoutingNo() != '' ) {
				$this->setAdditional( 'echeck_routing_number_last4', substr( $payment->getEcheckRoutingNo(), -4 ) );
			}
				
			if( $payment->getEcheckAccountNo() != '' ) {
				$this->setAdditional( 'echeck_account_number_last4', substr( $payment->getEcheckAccountNo(), -4 ) );
			}
		}
		
		return $this;
	}
	
	/**
	 * Get card label (formatted number).
	 */
	public function getLabel()
	{
		return Mage::helper('tokenbase')->__( "%s: x-%s", $this->getAdditional('echeck_bank_name'), $this->getAdditional('echeck_account_number_last4') );
	}
	
	/**
	 * On card save, set payment data to the gateway. (Broken out for extensibility)
	 */
	protected function _setPaymentInfoOnCreate( $gateway )
	{
		if( $this->getInfoInstance()->getEcheckAccountType() != 'businessChecking' ) {
			$gateway->setParameter( 'echeckType', 'WEB' );
		}
		else {
			$gateway->setParameter( 'echeckType', 'CCD' );
		}
		
		$gateway->setParameter( 'nameOnAccount', $this->getInfoInstance()->getEcheckAccountName() );
		$gateway->setParameter( 'bankName', $this->getInfoInstance()->getEcheckBankName() );
		$gateway->setParameter( 'accountType', $this->getInfoInstance()->getEcheckAccountType() );
		$gateway->setParameter( 'routingNumber', $this->getInfoInstance()->getEcheckRoutingNo() );
		$gateway->setParameter( 'accountNumber', $this->getInfoInstance()->getEcheckAccountNo() );
		
		return $this;
	}
	
	/**
	 * On card update, set payment data to the gateway. (Broken out for extensibility)
	 */
	protected function _setPaymentInfoOnUpdate( $gateway )
	{
		if( $this->getInfoInstance()->getEcheckAccountType() != 'businessChecking' ) {
			$gateway->setParameter( 'echeckType', 'WEB' );
		}
		else {
			$gateway->setParameter( 'echeckType', 'CCD' );
		}
		
		$gateway->setParameter( 'nameOnAccount', $this->getInfoInstance()->getEcheckAccountName() );
		$gateway->setParameter( 'bankName', $this->getInfoInstance()->getEcheckBankName() );
		$gateway->setParameter( 'accountType', $this->getInfoInstance()->getEcheckAccountType() );
		
		// Potentially masked routing number
		if( strlen( $this->getInfoInstance()->getEcheckRoutingNo() ) > 8 ) {
			$gateway->setParameter( 'routingNumber', $this->getInfoInstance()->getEcheckRoutingNo() );
		}
		else {
			$gateway->setParameter( 'routingNumber', 'XXXX' . $this->getAdditional('echeck_routing_number_last4') );
		}
		
		// Potentially masked account number
		if( strlen( $this->getInfoInstance()->getEcheckAccountNo() ) > 8 ) {
			$gateway->setParameter( 'accountNumber', $this->getInfoInstance()->getEcheckAccountNo() );
		}
		else {
			$gateway->setParameter( 'accountNumber', 'XXXX' . $this->getAdditional('echeck_account_number_last4') );
		}
		
		return $this;
	}
}
