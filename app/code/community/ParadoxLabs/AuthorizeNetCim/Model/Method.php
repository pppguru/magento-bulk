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

class ParadoxLabs_AuthorizeNetCim_Model_Method extends ParadoxLabs_TokenBase_Model_Method
{
	protected $_formBlockType				= 'authnetcim/form';
	protected $_infoBlockType				= 'authnetcim/info';
	protected $_code						= 'authnetcim';
	
	protected $_canFetchTransactionInfo		= true;
	
	/**
	 * Try to convert legacy data inline.
	 */
	protected function _loadOrCreateCard( Varien_Object $payment )
	{
		if( !is_null( $this->_card ) ) {
			$this->_log( sprintf( '_loadOrCreateCard(%s %s)', get_class( $payment ), $payment->getId() ) );
			
			$this->setCard( $this->getCard() );
			
			return $this->getCard();
		}
		elseif( $payment->hasTokenbaseId() !== true && $payment->getOrder() && $payment->getOrder()->getExtCustomerId() != '' ) {
			$this->_log( sprintf( '_loadOrCreateCard(%s %s)', get_class( $payment ), $payment->getId() ) );
			
			$card = Mage::getModel( $this->_code . '/card' );
			$card->setMethod( $this->_code )
				 ->setMethodInstance( $this )
				 ->setCustomer( $this->getCustomer(), $payment )
				 ->setAddress( $payment->getOrder()->getBillingAddress() )
				 ->importLegacyData( $payment )
				 ->save();
			
			$this->setCard( $card );
			
			return $card;
		}
		
		return parent::_loadOrCreateCard( $payment );
	}
	
	/**
	 * Create shipping address record before running the transaction.
	 */
	protected function _handleShippingAddress( Varien_Object $payment )
	{
		if( $this->getAdvancedConfigData('send_shipping_address') && $payment->getOrder()->getIsVirtual() == false ) {
			$address = $payment->getOrder()->getShippingAddress();
			
			$this->gateway()->setParameter( 'shipToFirstName', $address->getFirstname() );
			$this->gateway()->setParameter( 'shipToLastName', $address->getLastname() );
			$this->gateway()->setParameter( 'shipToCompany', $address->getCompany() );
			$this->gateway()->setParameter( 'shipToAddress', $address->getStreetFull() );
			$this->gateway()->setParameter( 'shipToCity', $address->getCity() );
			$this->gateway()->setParameter( 'shipToState', $address->getRegion() );
			$this->gateway()->setParameter( 'shipToZip', $address->getPostcode() );
			$this->gateway()->setParameter( 'shipToCountry', $address->getCountry() );
			$this->gateway()->setParameter( 'shipToPhoneNumber', $address->getTelephone() );
			$this->gateway()->setParameter( 'shipToFaxNumber', $address->getFax() );
		}
		
		return $this;
	}
	
	/**
	 * Catch execution before authorizing to include shipping address.
	 */
	protected function _beforeAuthorize( Varien_Object $payment, $amount )
	{
		$this->_handleShippingAddress( $payment );
		
		return parent::_beforeAuthorize( $payment, $amount );
	}
	
	/**
	 * Catch execution after authorizing to look for card type.
	 */
	protected function _afterAuthorize( Varien_Object $payment, $amount, Varien_Object $response )
	{
		$payment = $this->_fixLegacyCcType( $payment, $response );
		$payment = $this->_storeTransactionStatuses( $payment, $response );
		
		return parent::_afterAuthorize( $payment, $amount, $response );
	}
	
	/**
	 * Catch execution before capturing to include shipping address.
	 */
	protected function _beforeCapture( Varien_Object $payment, $amount )
	{
		$this->_handleShippingAddress( $payment );
		
		return parent::_beforeCapture( $payment, $amount );
	}
	
	/**
	 * Catch execution after capturing to reauthorize (if incomplete partial capture).
	 */
	protected function _afterCapture( Varien_Object $payment, $amount, Varien_Object $response )
	{
		$outstanding = round( $payment->getOrder()->getBaseTotalDue() - $amount, 4 );
		
		/**
		 * If this is a pre-auth capture for less than the total value of the order,
		 * try to reauthorize any remaining balance. So we have it.
		 */
		if( $outstanding > 0 ) {
			$wasTransId		= $payment->getTransactionId();
			$wasParentId	= $payment->getParentTransactionId();
			$authResponse	= null;
			$message		= false;
			
			if( $this->getConfigData('reauthorize_partial_invoice') == 1 ) {
				try {
					$this->_log( sprintf( '_afterCapture(): Reauthorizing for %s', $outstanding ) );
					
					$this->gateway()->clearParameters();
					$this->gateway()->setCard( $this->gateway()->getCard() );
					$this->gateway()->setIsReauthorize( true );
					
					$authResponse	= $this->gateway()->authorize( $payment, $outstanding );
					
					$payment->getOrder()->setExtOrderId( sprintf( '%s:%s', $authResponse->getTransactionId(), $authResponse->getAuthCode() ) );
				}
				catch( Exception $e ) {
					$payment->getOrder()->setExtOrderId( sprintf( '%s:', $response->getTransactionId() ) );
				}
			}
			
			/**
			 * Even if the auth didn't go through, we need to create a new 'transaction'
			 * so we can still do an online capture for the remainder.
			 */
			if( !is_null( $authResponse ) ) {
				$payment->setTransactionId( $this->_getValidTransactionId( $payment, $authResponse->getTransactionId() ) );
				$payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $authResponse->getData() );
				
				$message = Mage::helper('tokenbase')->__(
					'Reauthorized outstanding amount of %s.',
					$payment->getOrder()->getBaseCurrency()->formatTxt( $outstanding, array( 'currency' => $this->getConfigData('currency') ) )
				);
			}
			else {
				$payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() . '-auth' ) );
			}
			
			$payment->setParentTransactionId(null);
			$payment->setIsTransactionClosed(0);
			
			$payment->addTransaction(
				Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
				$payment->getOrder(),
				false,
				$message
			);
			
			$payment->setTransactionId( $wasTransId );
			$payment->setParentTransactionId( $wasParentId );
		}
		else {
			$payment->getOrder()->setExtOrderId( sprintf( '%s:', $response->getTransactionId() ) );
		}
		
		$payment = $this->_fixLegacyCcType( $payment, $response );
		$payment = $this->_storeTransactionStatuses( $payment, $response );
		
		return parent::_afterCapture( $payment, $amount, $response );
	}
	
	/**
	 * Save type for legacy cards if we don't have it. Run after auth/capture transactions.
	 */
	protected function _fixLegacyCcType( $payment, $response )
	{
		if( $this->getCard()->getAdditional('cc_type') == null && $response->getCardType() != '' ) {
			$ccType = Mage::helper( $this->_code )->mapCcTypeToMagento( $response->getCardType() );
			
			if( !is_null( $ccType ) ) {
				$this->getCard()->setAdditional( 'cc_type', $ccType )
								->setNoSync( true )
								->save();
				
				$payment->getOrder()->getPayment()->setCcType( $ccType );
			}
		}
		
		return $payment;
	}
	
	/**
	 * Store response statuses persistently.
	 */
	protected function _storeTransactionStatuses( $payment, $response )
	{
		if( $payment->getData('cc_avs_status') == '' && $response->getData('avs_result_code') != '' ) {
			$payment->setData( 'cc_avs_status', $response->getData('avs_result_code') );
		}
		
		if( $payment->getData('cc_cid_status') == '' && $response->getData('card_code_response_code') != '' ) {
			$payment->setData( 'cc_cid_status', $response->getData('card_code_response_code') );
		}
		
		if( $payment->getData('cc_status') == '' && $response->getData('cavv_response_code') != '' ) {
			$payment->setData( 'cc_status', $response->getData('cavv_response_code') );
		}
		
		return $payment;
	}
}
