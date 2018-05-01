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

class ParadoxLabs_AuthorizeNetCim_Model_Card extends ParadoxLabs_TokenBase_Model_Card
{
	/**
	 * Try to create a card record from legacy data.
	 */
	public function importLegacyData( Varien_Object $payment )
	{
		// Customer ID -- pull from customer or payment if possible, otherwise go to Authorize.Net.
		if( intval( $this->getCustomer()->getAuthnetcimProfileId() ) > 0 ) {
			$this->setProfileId( $this->getCustomer()->getAuthnetcimProfileId() );
		}
		elseif( intval( $payment->getAdditionalInformation('profile_id') ) > 0 ) {
			$this->setProfileId( intval( $payment->getAdditionalInformation('profile_id') ) );
		}
		else {
			$this->_createCustomerProfile();
		}
		
		// Payment ID -- pull from order if possible.
		$this->setPaymentId( $payment->getOrder()->getExtCustomerId() );
		
		if( $this->getProfileId() == '' || $this->getPaymentId() == '' ) {
			Mage::helper('tokenbase')->log( $this->getMethod(), 'Authorize.Net CIM: Unable to covert legacy data for processing. Please seek support.' );
			Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM: Unable to covert legacy data for processing. Please seek support.' ) );
		}
		
		if( $payment->getCcType() != '' ) {
			$this->setAdditional( 'cc_type', $payment->getCcType() );
		}
		
		if( $payment->getCcLast4() != '' ) {
			$this->setAdditional( 'cc_last4', $payment->getCcLast4() );
		}
		
		if( $payment->getCcExpYear() > date('Y') || ( $payment->getCcExpYear() == date('Y') && $payment->getCcExpMonth() >= date('n') ) ) {
			$this->setAdditional( 'cc_exp_year', $payment->getCcExpYear() )
				 ->setAdditional( 'cc_exp_month', $payment->getCcExpMonth() )
				 ->setExpires( sprintf( "%s-%s-%s 23:59:59", $payment->getCcExpYear(), $payment->getCcExpMonth(), date( 't', strtotime( $payment->getCcExpYear() . '-' . $payment->getCcExpMonth() ) ) ) );
		}
		
		return $this;
	}
	
	/**
	 * Finalize before saving.
	 */
	protected function _beforeSave()
	{
		// Sync only if we have an info instance for payment data, and haven't already.
		if( $this->hasInfoInstance() && $this->getNoSync() != true ) {
			$this->_createCustomerPaymentProfile();
		}
		
		return parent::_beforeSave();
	}
	
	/**
	 * Finalize before deleting.
	 */
	protected function _beforeDelete()
	{
		/**
		 * Delete from Authorize.Net if we have a valid record.
		 */
		if( $this->getProfileId() != '' && $this->getPaymentId() != '' ) {
			$gateway = $this->getMethodInstance()->gateway();
			
			$gateway->setCard( $this );
			
			$gateway->setParameter( 'customerProfileId', $this->getProfileId() );
			$gateway->setParameter( 'customerPaymentProfileId', $this->getPaymentId() );
			
			// Suppress any gateway errors that might occur, we don't care here.
			try {
				$gateway->deleteCustomerPaymentProfile();
			}
			catch( Exception $e ) {
				Mage::helper('tokenbase')->log( $this->getMethod(), $e->getMessage() );
			}
		}
		
		return parent::_beforeDelete();
	}
	
	/**
	 * Attempt to create a CIM customer profile
	 */
	protected function _createCustomerProfile()
	{
		if( $this->getCustomerId() > 0 || $this->getCustomerEmail() != '' ) {
			$gateway = $this->getMethodInstance()->gateway();
			
			$gateway->setParameter( 'merchantCustomerId', $this->getCustomerId() );
			$gateway->setParameter( 'email', $this->getCustomerEmail() );
			
			$profileId = $gateway->createCustomerProfile();
			
			if( !empty( $profileId ) )  {
				$this->setProfileId( $profileId );
				$this->getCustomer()->setAuthnetcimProfileId( $profileId )
									->setAuthnetcimProfileVersion( 200 );
				
				if( $this->getCustomer()->getId() > 0 ) {
					$this->getCustomer()->save();
				}
			}
			else {
				Mage::helper('tokenbase')->log( $this->getMethod(), 'Authorize.Net CIM Gateway: Unable to create customer profile.' );
				Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Unable to create customer profile.' ) );
			}
		}
		else {
			Mage::helper('tokenbase')->log( $this->getMethod(), 'Authorize.Net CIM Gateway: Unable to create customer profile; email or user ID is required.' );
			Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Unable to create customer profile; email or user ID is required.' ) );
		}
		
		return $this;
	}
	
	/**
	 * Attempt to create a CIM payment profile
	 */
	protected function _createCustomerPaymentProfile( $retry=true )
	{
		Mage::helper('tokenbase')->log( $this->getMethod(), sprintf( '_createCustomerPaymentProfile(%s) (profile_id %s, payment_id %s)', var_export( $retry, 1 ), var_export( $this->getProfileId(), 1 ), var_export( $this->getPaymentId(), 1 ) ) );
		
		$this->getMethodInstance()->setCard( $this );
		
		$gateway = $this->getMethodInstance()->gateway();
		
		/**
		 * Make sure we have a customer profile, first off.
		 */
		if( $this->getProfileId() == '' ) {
			// Does the customer have a profile ID? Try to import it.
			if( $this->getCustomer()->getId() > 0 && $this->getCustomer()->getAuthnetcimProfileId() != '' ) {
				$this->setProfileId( $this->getCustomer()->getAuthnetcimProfileId() );
			}
			// No profile ID, so create one.
			else {
				$this->_createCustomerProfile();
			}
		}
		
		/**
		 * If the card does not exist, create it in CIM.
		 */
		if( $this->getPaymentId() == '' ) {
			$address = $this->getAddressObject();
			
			$gateway->setParameter( 'customerProfileId', $this->getProfileId() );
			
			$gateway->setParameter( 'billToFirstName', $address->getFirstname() );
			$gateway->setParameter( 'billToLastName', $address->getLastname() );
			$gateway->setParameter( 'billToCompany', $address->getCompany() );
			$gateway->setParameter( 'billToAddress', $address->getStreetFull() );
			$gateway->setParameter( 'billToCity', $address->getCity() );
			$gateway->setParameter( 'billToState', $address->getRegion() );
			$gateway->setParameter( 'billToZip', $address->getPostcode() );
			$gateway->setParameter( 'billToCountry', $address->getCountry() );
			$gateway->setParameter( 'billToPhoneNumber', $address->getTelephone() );
			$gateway->setParameter( 'billToFaxNumber', $address->getFax() );
			
			$gateway->setParameter( 'validationMode', $this->getMethodInstance()->getConfigData('validation_mode') );
			
			$this->_setPaymentInfoOnCreate( $gateway );
			
			$paymentId = $gateway->createCustomerPaymentProfile();
		}
		/**
		 * If it does exist, update CIM.
		 */
		else {
			$address = $this->getAddressObject();
			
			$gateway->setParameter( 'customerProfileId', $this->getProfileId() );
			$gateway->setParameter( 'customerPaymentProfileId', $this->getPaymentId() );
			
			$gateway->setParameter( 'billToFirstName', $address->getFirstname() );
			$gateway->setParameter( 'billToLastName', $address->getLastname() );
			$gateway->setParameter( 'billToCompany', $address->getCompany() );
			$gateway->setParameter( 'billToAddress', $address->getStreetFull() );
			$gateway->setParameter( 'billToCity', $address->getCity() );
			$gateway->setParameter( 'billToState', $address->getRegion() );
			$gateway->setParameter( 'billToZip', $address->getPostcode() );
			$gateway->setParameter( 'billToCountry', $address->getCountry() );
			$gateway->setParameter( 'billToPhoneNumber', $address->getTelephone() );
			$gateway->setParameter( 'billToFaxNumber', $address->getFax() );
			
			if( Mage::helper('tokenbase')->getIsCheckout() !== true ) {
				$gateway->setParameter( 'validationMode', $this->getMethodInstance()->getConfigData('validation_mode') );
			}
			
			$this->_setPaymentInfoOnUpdate( $gateway );
			
			$gateway->updateCustomerPaymentProfile();
			
			$paymentId = $this->getPaymentId();
		}
		
		/**
		 * Check for 'Record cannot be found' errors (changed Authorize.Net accounts).
		 * If we find it, clear our data and try again (once, and only once!).
		 */
		$response = $gateway->getLastResponse();
		if( $retry === true && isset( $response['messages']['message']['code'] ) && $response['messages']['message']['code'] == 'E00040' ) {
			$this->setProfileId( '' );
			$this->setPaymentId( '' );
			
			if( $this->getCustomer()->getId() > 0 && $this->getCustomer()->getAuthnetcimProfileId() != '' ) {
				$this->getCustomer()->setAuthnetcimProfileId( '' );
			}
			
			return $this->_createCustomerPaymentProfile( false );
		}
		elseif( $response['messages']['resultCode'] != 'Ok' && ( $response['messages']['message']['code'] != 'E00039' || empty( $paymentId ) ) ) {
			$errorCode	= $response['messages']['message']['code'];
			$errorText	= $response['messages']['message']['text'];
			
			Mage::helper('tokenbase')->log( $this->getMethod(), sprintf( "API error: %s: %s", $errorCode, $errorText ) );
			$gateway->logLogs();
			
			Mage::throwException( Mage::helper('tokenbase')->__( sprintf( 'Authorize.Net CIM Gateway: %s', $errorText ) ) );
		}
		
		if( !empty( $paymentId ) ) {
			/**
			 * Prevent data from being updated multiple times in one request.
			 */
			$this->setPaymentId( $paymentId );
			$this->setNoSync( true );
		}
		else {
			$gateway->logLogs();
			
			Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Unable to create payment record.' ) );
		}
		
		return $this;
	}
	
	/**
	 * On card save, set payment data to the gateway. (Broken out for extensibility)
	 */
	protected function _setPaymentInfoOnCreate( $gateway )
	{
		$gateway->setParameter( 'cardNumber', $this->getInfoInstance()->getCcNumber() );
		$gateway->setParameter( 'cardCode', $this->getInfoInstance()->getCcCid() );
		$gateway->setParameter( 'expirationDate', sprintf( "%04d-%02d", $this->getInfoInstance()->getCcExpYear(), $this->getInfoInstance()->getCcExpMonth() ) );
		
		return $this;
	}
	
	/**
	 * On card update, set payment data to the gateway. (Broken out for extensibility)
	 */
	protected function _setPaymentInfoOnUpdate( $gateway )
	{
		if( strlen( $this->getInfoInstance()->getCcNumber() ) >= 12 ) {
			$gateway->setParameter( 'cardNumber', $this->getInfoInstance()->getCcNumber() );
		}
		else {
			// If we were not given a full CC number, grab the masked value from Authorize.Net.
			$profile = $gateway->getCustomerPaymentProfile();
			
			if( isset( $profile['paymentProfile'] ) && isset( $profile['paymentProfile']['payment']['creditCard'] ) ) {
				$gateway->setParameter( 'cardNumber', $profile['paymentProfile']['payment']['creditCard']['cardNumber'] );
			}
			else {
				Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Could not load payment record.' ) );
			}
		}
		
		if( $this->getInfoInstance()->getCcExpYear() != '' && $this->getInfoInstance()->getCcExpMonth() != '' ) {
			$gateway->setParameter( 'expirationDate', sprintf( "%04d-%02d", $this->getInfoInstance()->getCcExpYear(), $this->getInfoInstance()->getCcExpMonth() ) );
		}
		else {
			$gateway->setParameter( 'expirationDate', 'XXXX' );
		}
		
		$gateway->setParameter( 'cardCode', $this->getInfoInstance()->getCcCid() );
		
		return $this;
	}
}
