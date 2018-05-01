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
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_TokenBase_Model_Api_Api extends Mage_Api_Model_Resource_Abstract
{
	protected $_cardMap	= array( 'id', 'customer_id', 'customer_email', 'customer_ip', 'profile_id', 'payment_id', 'method', 'created_at', 'updated_at', 'last_use', 'expires', 'additional', 'hash' );
	protected $_addrMap	= array( 'firstname', 'lastname', 'street', 'city', 'region', 'postcode', 'country_id', 'telephone', 'fax', 'region_id' );
	
	/**
	 * Fetch a card by ID.
	 */
	public function getCard( $tokenbaseId )
	{
		$card		= Mage::getModel('tokenbase/card');
		$card->load( $tokenbaseId );
		
		if( !$card || $card->getId() != $tokenbaseId || $card->getId() < 1 ) {
			$this->_fault('tokenbase_id_invalid');
		}
		
		return $this->_prepareCard( $card );
	}
	
	/**
	 * Fetch all stored payment data belonging to the given customer.
	 */
	public function getCardsByCustomer( $customerId )
	{
		$cards		= Mage::getModel('tokenbase/card')->getCollection()
						->addFieldToFilter( 'customer_id', (int)$customerId )
						->addFieldToFilter( 'active', 1 );
		
		$results	= array();
		foreach( $cards as $card ) {
			$results[] = $this->_prepareCard( $card );
		}
		
		return $results;
	}
	
	/**
	 * Delete the given payment data. $customerId is checked for protection reasons.
	 */
	public function deleteCard( $customerId, $cardId )
	{
		$card	= Mage::getModel('tokenbase/card')->load( (int)$cardId );
		
		if( ( $customerId == 0 || $card->hasOwner( (int)$customerId ) ) ) {
			if( $card->getIsActive() == 1 ) {
				$card->queueDeletion()->save();
			}
			
			return true;
		}
		else {
			$this->_fault('customer_invalid');
		}
		
		return false;
	}
	
	/**
	 * Create or update the given card
	 */
	public function updateCard( $method, $customerData, $addressData, $paymentData, $cardId=null )
	{
		$customerData	= (array)$customerData;
		$addressData	= (array)$addressData;
		$paymentData	= (array)$paymentData;
		
		if( isset( $addressData['street'] ) && !is_array( $addressData['street'] ) ) {
			$addressData['street'] = array( $addressData['street'] );
		}
		
		/**
		 * Convert inputs into an address and payment object for storage.
		 */
		try {
			if( $this->_methodIsValid( $method ) === false ) {
				$this->_fault('method_invalid');
			}
			
			$card		= Mage::getModel( $method . '/card' );
			
			/**
			 * Load the card (if any) and process customer data
			 */
			if( !is_null( $cardId ) && (int)$cardId > 0 ) {
				$card->load( (int)$cardId );
			}
			
			if( isset( $customerData['customer_id'] ) && intval( $customerData['customer_id'] ) > 0 ) {
				$card->setCustomerId( $customerData['customer_id'] );
			}
			
			if( isset( $customerData['customer_email'] ) ) {
				$card->setCustomerEmail( $customerData['customer_email'] );
			}
			
			if( isset( $customerData['customer_ip'] ) ) {
				$card->setCustomerIp( $customerData['customer_ip'] );
			}
			
			/**
			 * Process address data
			 */
			$newAddr = Mage::getModel('customer/address');
			$newAddr->setCustomerId( $card->getCustomerId() );
			
			$addressForm    = Mage::getModel('customer/form');
			$addressForm->setFormCode('customer_address_edit');
			$addressForm->setEntity( $newAddr );
			
			$addressExtract = $addressForm->extractData( $addressForm->prepareRequest( $addressData ) );
			$addressErrors  = $addressForm->validateData( $addressExtract );
			
			if( $addressErrors !== true ) {
				$this->_fault( 'address_invalid', implode( ' ', $addressErrors ) );
			}
			
			$addressForm->compactData( $addressExtract );
			$addressErrors  = $newAddr->validate();
			
			$newAddr->setSaveInAddressBook( false );
			$newAddr->implodeStreetAddress();
			
			/**
			 * Process payment data
			 */
			$paymentData['method']	= $method;
			$paymentData['card_id']	= $card->getId();
			
			if( isset( $paymentData['cc_number'] ) ) {
				$paymentData['cc_last4']	= substr( $paymentData['cc_number'], -4 );
			}
			
			$quote = Mage::getModel('sales/quote');
			$quote->setStoreId( Mage::helper('tokenbase')->getCurrentStoreId() );
			$quote->setCustomerId( $card->getCustomerId() );
			$quote->getBillingAddress()->setCountryId( $newAddr->getCountryId() );
			
			$newPayment = Mage::getModel('sales/quote_payment');
			$newPayment->setQuote( $quote );
			$newPayment->importData( $paymentData );
			
			/**
			 * Save payment data
			 */
			$card->setMethod( $method );
			$card->setAddress( $newAddr );
			$card->importPaymentInfo( $newPayment );
			$card->save();
			
			return $card->getId();
		}
		catch( Exception $e ) {
			Mage::helper('tokenbase')->log( $method, (string)$e );
			
			if( $e instanceof Mage_Api_Exception ) {
				throw new Mage_Api_Exception( $e->getMessage(), $e->getCustomMessage() );
			}
			else {
				$this->_fault( 'unable_to_save', $e->getMessage() );
			}
		}
	}
	
	/**
	 * Turn a stored card into an output-ready array.
	 */
	protected function _prepareCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		$card		= $card->getTypeInstance();
		$address	= $card->getAddress();
		
		/**
		 * Basic payment record data
		 */
		$result		= array();
		foreach( $this->_cardMap as $key ) {
			$result[ $key ]		= $card->getData( $key );
		}
		
		/**
		 * Address data
		 */
		$result['address']		= array();
		foreach( $this->_addrMap as $key ) {
			$result['address'][ $key ] = $address[ $key ];
		}
		
		/**
		 * Additional (common) information
		 */
		$result['label']		= $card->getLabel();
		$result['cc_type']		= $card->getAdditional('cc_type');
		$result['cc_last4']		= $card->getAdditional('cc_last4');
		
		return $result;
	}
	
	/**
	 * Check whether input method is valid, register if so.
	 */
	protected function _methodIsValid( $method )
	{
		if( in_array( $method, Mage::helper('tokenbase')->getActiveMethods() ) !== false ) {
			return true;
		}
		
		return false;
	}
}
