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

/**
 * Generic payment gateway implementation... everything except the API.
 */
class ParadoxLabs_TokenBase_Model_Method extends Mage_Payment_Model_Method_Cc
{
	protected $_formBlockType				= 'tokenbase/form';
	protected $_infoBlockType				= 'tokenbase/info';
	protected $_code						= 'tokenbase';
	
	// Capabilities
	protected $_isGateway					= false;
	protected $_canAuthorize				= true;
	protected $_canCapture					= true;
	protected $_canCapturePartial			= true;
	protected $_canRefund					= true;
	protected $_canRefundInvoicePartial		= true;
	protected $_canVoid						= true;
	protected $_canUseInternal				= true;
	protected $_canUseCheckout				= true;
	protected $_canUseForMultishipping		= true;
	protected $_canSaveCc					= false;
	protected $_canReviewPayment			= false;
	protected $_canCancelInvoice			= true;
	protected $_canManageRecurringProfiles	= false;
	protected $_canFetchTransactionInfo		= false;
	
	// Persistent values
	protected $_gateway						= null;
	protected $_customer					= null;
	protected $_card						= null;
	protected $_storeId						= 0;
	
	/**
	 * Initialize scope
	 */
	public function __construct()
	{
		$this->setStore( Mage::helper('tokenbase')->getCurrentStoreId() );
		
		return $this;
	}
	
	/**
	 * Set the payment config scope and reinitialize the API
	 */
	public function setStore( $id )
	{
		// Whelp.
		if( $id instanceof Mage_Core_Model_Store ) {
			$id = $id->getId();
		}
		
		$this->_storeId	= intval( $id );
		$this->_gateway	= null;
		
		return $this;
	}
	
	/**
	 * Fetch a setting for the current store scope.
	 */
	public function getConfigData( $field, $storeId=null )
	{
		if( is_null( $storeId ) ) {
			$storeId = $this->_storeId;
		}
		
		return Mage::getStoreConfig( 'payment/' . $this->_code . '/' . $field, $storeId );
	}
	
	/**
	 * Fetch an advanced setting for the current store scope.
	 * @deprecated  since 2.0.3 for compatibility with CE 1.6 and below (do not support settings sub-groups).
	 */
	public function getAdvancedConfigData( $field, $storeId=null )
	{
		return $this->getConfigData( $field, $storeId );
	}
	
	/**
	 * Set the customer to use for payment/card operations.
	 */
	public function setCustomer( $customer )
	{
		$this->_customer = $customer;
		
		return $this;
	}
	
	/**
	 * Get the current customer; fetch from session if necessary.
	 */
	public function getCustomer()
	{
		if( is_null( $this->_customer ) || $this->_customer->getId() < 1 ) {
			$this->setCustomer( Mage::helper('tokenbase')->getCurrentCustomer() );
		}
		
		return $this->_customer;
	}
	
	/**
	 * Initialize/return the API gateway class.
	 */
	public function gateway()
	{
		if( is_null( $this->_gateway ) ) {
			$this->_gateway = Mage::getModel( $this->_code . '/gateway' );
			$this->_gateway->init(array(
				'login'			=> $this->getConfigData('login'),
				'password'		=> $this->getConfigData('trans_key'),
				'secret_key'	=> $this->getConfigData('secrey_key'),
				'test_mode'		=> $this->getConfigData('test'),
				'verify_ssl'	=> $this->getConfigData('verify_ssl'),
			));
		}
		
		return $this->_gateway;
	}
	
	/**
	 * Load the given card by ID, authenticate, and store with the object.
	 */
	public function loadAndSetCard( $cardId, $byHash=false )
	{
		$this->_log( sprintf( 'loadAndSetCard(%s, %s)', $cardId, var_export( $byHash, 1 ) ) );
		
		$card = Mage::getModel( $this->_code . '/card' );
		
		if( $byHash === true ) {
			$card->loadByHash( $cardId );
		}
		else {
			$card->load( $cardId );
		}
		
		if( $card && $card->getId() > 0 ) {
			$this->setCard( $card );
			
			return $card;
		}
		
		/**
		 * This error will be thrown if the card does not exist OR if we don't have permission to use it.
		 */
		$this->_log( Mage::helper('tokenbase')->__('Unable to load payment data. Please check the form and try again.') );
		
		throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Unable to load payment data. Please check the form and try again.') );
	}
	
	/**
	 * Set the current payment card
	 */
	public function setCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		$this->_log( sprintf( 'setCard(%s)', $card->getId() ) );
		
		$this->_card = $card;
		
		$this->gateway()->setCard( $card );
		
		$this->getInfoInstance()->setTokenbaseId( $card->getId() )
								->setCcType( $card->getAdditional('cc_type') )
								->setCcLast4( $card->getAdditional('cc_last4') )
								->setCcExpMonth( $card->getAdditional('cc_exp_month') )
								->setCcExpYear( $card->getAdditional('cc_exp_year') );
		
		return $this;
	}
	
	/**
	 * Get the current card
	 */
	public function getCard()
	{
		return $this->_card;
	}
	
	/**
	 * Allow zero-subtotal checkout with card storage by forcing the test bit to zero.
	 */
	public function isApplicableToQuote( $quote, $checksBitMask )
	{
		// Skip zero total check (we want to allow)
		$checksBitMask &= ~self::CHECK_ZERO_TOTAL;
		
		return parent::isApplicableToQuote( $quote, $checksBitMask );
	}
	
	/**
	 * Update the CC info during the checkout process.
	 */
	public function assignData( $data )
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		
		parent::assignData( $data );
		
		if( $data->hasCardId() && $data->getCardId() != '' ) {
			/**
			 * Load and validate the chosen card.
			 * 
			 * If we are in checkout, force load by hash rather than numeric ID. Bit harder to guess.
			 */
			if( Mage::helper('tokenbase')->getIsCheckout() || !is_numeric( $data->getCardId() ) ) {
				$card = $this->loadAndSetCard( $data->getCardId(), true );
			}
			else {
				$card = $this->loadAndSetCard( $data->getCardId() );
			}
			
			/**
			 * Overwrite data if necessary
			 */
			if( $data->hasCcType() && $data->getCcType() != '' ) {
				$this->getInfoInstance()->setCcType( $data->getCcType() );
			}
			
			if( $data->hasCcLast4() && $data->getCcLast4() != '' ) {
				$this->getInfoInstance()->setCcLast4( $data->getCcLast4() );
			}
			
			if( $data->getCcExpYear() != ''  && $data->getCcExpMonth() != '' ) {
				$this->getInfoInstance()->setCcExpYear( $data->getCcExpYear() )
										->setCcExpMonth( $data->getCcExpMonth() );
			}
			
			if( $data->hasSavedCcCid() && $data->getSavedCcCid() != '' ) {
				$this->getInfoInstance()->setCcCid( preg_replace( '/[^0-9]/', '', $data->getSavedCcCid() ) );
			}
		}
		else {
			$this->getInfoInstance()->unsetData('tokenbase_id');
		}
		
		if( $data->hasSave() ) {
			$this->getInfoInstance()->setAdditionalInformation( 'save', intval( $data->getSave() ) );
		}
		
		return $this;
	}
	
	/**
	 * Check whether void is available for the given order.
	 */
	public function canVoid(Varien_Object $payment)
	{
		if( parent::canVoid( $payment ) ) {
			if( ( $payment->getOrder() instanceof Mage_Sales_Model_Order ) && $payment->getOrder()->canCancel() ) {
				/**
				 * Bad convention: Auth code is stored as the second part of ext_order_id.
				 * If there is no auth code, it has already been voided or is not relevant.
				 */
				$transactionId = explode( ':', $payment->getOrder()->getExtOrderId(), 2 );
				
				if( !isset( $transactionId[1] ) || empty( $transactionId[1] ) ) {
					return false;
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validate the transaction inputs.
	 */
	public function validate()
	{
		$this->_log( sprintf( 'validate(%s)', $this->getInfoInstance()->getCardId() ) );
		
		/**
		 * If no tokenbase ID, we must have a new card. Make sure all the details look valid.
		 */
		if( $this->getInfoInstance()->hasTokenbaseId() === false ) {
			return parent::validate();
		}
		/**
		 * If there is an ID, this might be an edit. Validate there too, as much as we can.
		 */
		else {
			if( $this->getInfoInstance()->getCcNumber() != '' && substr( $this->getInfoInstance()->getCcNumber(), 0, 4 ) != 'XXXX' ) {
				// remove credit card number delimiters such as "-" and space
				$this->getInfoInstance()->setData( 'cc_number', preg_replace( '/[\-\s]+/', '', $this->getInfoInstance()->getCcNumber() ) );
				
				if( strlen( $this->getInfoInstance()->getCcNumber() ) < 13
					|| !is_numeric( $this->getInfoInstance()->getCcNumber() )
					|| !$this->validateCcNum( $this->getInfoInstance()->getData('cc_number') ) ) {
					throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('payment')->__('Invalid Credit Card Number') );
				}
			}
			
			if( $this->getInfoInstance()->getCcExpYear() != '' && $this->getInfoInstance()->getCcExpMonth() != '' ) {
				if( !$this->_validateExpDate( $this->getInfoInstance()->getCcExpYear(), $this->getInfoInstance()->getCcExpMonth() ) ) {
					throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('payment')->__('Incorrect credit card expiration date.') );
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Authorize a transaction
	 */
	public function authorize( Varien_Object $payment, $amount )
	{
		$this->_log( sprintf( 'authorize(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );
		
		$this->_loadOrCreateCard( $payment );
		
		if( $amount <= 0 ) {
			return $this;
		}
		
		/**
		 * Check for existing authorization, and void it if so.
		 */
		$transactionId = explode( ':', $payment->getOrder()->getExtOrderId() );
		if( !empty( $transactionId[1] ) ) {
			$parentTransactionId = $payment->getParentTransactionId();
			$payment->setParentTransactionId( $transactionId[0] );
			
			$this->void( $payment );
			
			$payment->setParentTransactionId( $parentTransactionId );
		}
		
		/**
		 * Process transaction and results
		 */
		$this->_resyncStoredCard( $payment );
		
		if( $this->getAdvancedConfigData('send_line_items') ) {
			$this->gateway()->setLineItems( $payment->getOrder()->getAllVisibleItems() );
		}
		
		$this->_beforeAuthorize( $payment, $amount );
		$response = $this->gateway()->authorize( $payment, $amount );
		$this->_afterAuthorize( $payment, $amount, $response );
		
		$payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );
		
		if( $response->getIsFraud() === true ) {
			$payment->setIsTransactionPending(true)
					->setIsFraudDetected(true)
					->setTransactionAdditionalInfo( 'is_transaction_fraud', true );
		}
		elseif( $payment->getOrder()->getStatus() != $this->getConfigData('order_status') ) {
			$payment->getOrder()->setStatus( $this->getConfigData('order_status') );
		}
		
		$payment->getOrder()->setExtOrderId( sprintf( '%s:%s', $response->getTransactionId(), $response->getAuthCode() ) );
		
		$payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() ) )
				->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) )
				->setIsTransactionClosed(0);
		
		$this->getCard()->updateLastUse()->save();
		
		$this->_log( json_encode( $response->getData() ) );
		
		return $this;
	}
	
	/**
	 * Capture a transaction [authorize if necessary]
	 */
	public function capture( Varien_Object $payment, $amount )
	{
		$this->_log( sprintf( 'capture(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );
		
		$this->_loadOrCreateCard( $payment );
		
		if( $amount <= 0 ) {
			return $this;
		}
		
		/**
		 * Check for existing auth code.
		 */
		$transactionId = explode( ':', $payment->getOrder()->getExtOrderId() );
		if( !empty( $transactionId[1] ) ) {
			$this->gateway()->setHaveAuthorized( true );
			$this->gateway()->setAuthCode( $transactionId[1] );
			
			if( $payment->getParentTransactionId() != '' ) {
				$this->gateway()->setTransactionId( $payment->getParentTransactionId() );
			}
			else {
				$this->gateway()->setTransactionId( $transactionId[0] );
			}
		}
		else {
			$this->gateway()->setHaveAuthorized( false );
		}
		
		/**
		 * Grab transaction ID from the invoice in case partial invoicing.
		 */
		if( $payment->hasInvoice() && $payment->getInvoice() instanceof Mage_Sales_Model_Order_Invoice ) {
			$invoice	= $payment->getInvoice();
		}
		else {
			$invoice	= Mage::registry('current_invoice');
		}
		
		if( !is_null( $invoice ) ) {
			if( $invoice->getTransactionId() != '' ) {
				$this->gateway()->setTransactionId( $invoice->getTransactionId() );
			}
			
			if( $this->getAdvancedConfigData('send_line_items') ) {
				$this->gateway()->setLineItems( $invoice->getAllItems() );
			}
		}
		elseif( $this->getAdvancedConfigData('send_line_items') ) {
			$this->gateway()->setLineItems( $payment->getOrder()->getAllVisibleItems() );
		}
		
		/**
		 * Process transaction and results
		 */
		$this->_resyncStoredCard( $payment );
		
		$this->_beforeCapture( $payment, $amount );
		$response = $this->gateway()->capture( $payment, $amount );
		$this->_afterCapture( $payment, $amount, $response );
		
		$payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );
		
		if( $response->getIsFraud() === true ) {
			$payment->setIsTransactionPending(true)
					->setIsFraudDetected(true)
					->setTransactionAdditionalInfo( 'is_transaction_fraud', true );
		}
		elseif( $this->gateway()->getHaveAuthorized() === false ) {
			if( $payment->getOrder()->getStatus() != $this->getConfigData('order_status') ) {
				$payment->getOrder()->setStatus( $this->getConfigData('order_status') );
			}
			
			$payment->getOrder()->setExtOrderId( sprintf( '%s:%s', $response->getTransactionId(), $response->getAuthCode() ) );
		}
		
		$payment->setIsTransactionClosed(0);
		
		// Set transaction id iff different from the last txn id -- use Magento's generated ID otherwise.
		if( $payment->getParentTransactionId() != $response->getTransactionId() ) {
			$payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() ) );
		}
		
		$payment->setShouldCloseParentTransaction(1);
		$payment->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) );
		
		$this->getCard()->updateLastUse()->save();
		
		$this->_log( json_encode( $response->getData() ) );
		
		return $this;
	}
	
	/**
	 * Refund a transaction
	 */
	public function refund( Varien_Object $payment, $amount )
	{
		$this->_log( sprintf( 'refund(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );
		
		$this->_loadOrCreateCard( $payment );
		
		if( $amount <= 0 ) {
			return $this;
		}
		
		$creditmemo		= $payment->getCreditmemo();
		
		/**
		 * Grab transaction ID from the order
		 */
		if( $payment->getParentTransactionId() != '' ) {
			$transactionId = substr( $payment->getParentTransactionId(), 0, strcspn( $payment->getParentTransactionId(), '-' ) );
		}
		else {
			if( $creditmemo && $creditmemo->getInvoice()->getTransactionId() != '' ) {
				$transactionId = $creditmemo->getInvoice()->getTransactionId();
			}
			else {
				$transactionId = explode( ':', $payment->getOrder()->getExtOrderId() );
				$transactionId = $transactionId[0];
			}
		}
		
		$this->gateway()->setTransactionId( $transactionId );
		
		/**
		 * Add line items.
		 */
		if( $this->getAdvancedConfigData('send_line_items') ) {
			if( $creditmemo ) {
				$this->gateway()->setLineItems( $creditmemo->getAllItems() );
			}
			else {
				$this->gateway()->setLineItems( $payment->getOrder()->getAllVisibleItems() );
			}
		}
		
		/**
		 * Process transaction and results
		 */
		$this->_beforeRefund( $payment, $amount );
		$response = $this->gateway()->refund( $payment, $amount );
		$this->_afterRefund( $payment, $amount, $response );
		
		$payment->setIsTransactionClosed(1)
				->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) )
				->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );
		
		if( $response->getTransactionId() != '' && $response->getTransactionId() != $transactionId ) {
			$payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() ) );
		}
		else {
			$payment->setTransactionId( $this->_getValidTransactionId( $payment, $transactionId . '-refund' ) );
		}
		
		if( $creditmemo && $creditmemo->getInvoice() && $creditmemo->getInvoice()->getBaseTotalRefunded() < $creditmemo->getInvoice()->getBaseGrandTotal() ) {
			$payment->setShouldCloseParentTransaction(0);
		}
		else {
			$payment->setShouldCloseParentTransaction(1);
		}
		
		$this->getCard()->updateLastUse()->save();
		
		$this->_log( json_encode( $response->getData() ) );
		
		return $this;
	}
	
	/**
	 * Void a payment
	 */
	public function void( Varien_Object $payment )
	{
		$this->_log( sprintf( 'void(%s %s)', get_class( $payment ), $payment->getId() ) );
		
		$this->_loadOrCreateCard( $payment );
		
		/**
		 * Grab transaction ID from the order
		 */
		$this->gateway()->setTransactionId( $payment->getParentTransactionId() );
		
		/**
		 * Process transaction and results
		 */
		$this->_beforeVoid( $payment );
		$response = $this->gateway()->void( $payment );
		$this->_afterVoid( $payment, $response );
			
		$transactionId = $response->getTransactionId() != '' && $response->getTransactionId() != '0' ? $response->getTransactionId() : $payment->getTransactionId();
		
		$payment->getOrder()->setExtOrderId( $transactionId );
		
		$payment->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) )
				->setShouldCloseParentTransaction(1)
				->setIsTransactionClosed(1);
		
		$payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );
		
		$this->getCard()->updateLastUse()->save();
		
		$this->_log( json_encode( $response->getData() ) );
		
		return $this;
	}
	
	/**
	 * Cancel a payment
	 */
	public function cancel( Varien_Object $payment )
	{
		$this->_log( sprintf( 'cancel(%s %s)', get_class( $payment ), $payment->getId() ) );
		
		return $this->void( $payment );
	}
	
	/**
	 * Fetch transaction info -- fraud detection
	 */
	public function fetchTransactionInfo( Mage_Payment_Model_Info $payment, $transactionId )
	{
		$this->_log( 'fetchTransactionInfo('.$transactionId.')' );
		
		$this->_loadOrCreateCard( $payment );
		
		/**
		 * Process transaction and results
		 */
		$this->_beforeFraudUpdate( $payment, $transactionId );
		$response = $this->gateway()->fraudUpdate( $payment, $transactionId );
		$this->_afterFraudUpdate( $payment, $transactionId, $response );
		
		if( $response->getIsApproved() ) {
			$transaction = $payment->getTransaction($transactionId);
			$transaction->setAdditionalInformation( 'is_transaction_fraud', false );
			
			$payment->setIsTransactionApproved( true );
		}
		elseif( $response->getIsDenied() ) {
			$payment->setIsTransactionDenied( true );
		}
		
		$this->_log( json_encode( $response->getData() ) );
		
		$txn = $payment->getTransaction( $transactionId );
		$existingData = $txn instanceof Mage_Sales_Model_Order_Payment_Transaction ? $txn->getAdditionalInformation( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS ) : null;
		
		return Mage::helper('tokenbase')->replaceArray( !is_null( $existingData ) ? $existingData : array(), $response->getData() );
	}
	
	/**
	 * We can't have two transactions with the same ID. Make sure that doesn't happen.
	 */
	protected function _getValidTransactionId( Varien_Object $payment, $transactionId )
	{
		$transactions = Mage::getModel('sales/order_payment_transaction')->getCollection()
								->setOrderFilter( $payment->getOrder() )
								->addPaymentIdFilter( $payment->getId() )
								->setOrder( 'created_at', Varien_Data_Collection::SORT_ORDER_DESC )
								->setOrder( 'transaction_id', Varien_Data_Collection::SORT_ORDER_DESC );
		
		$baseId		= $transactionId;
		$increment	= 1;
		
		/**
		 * Iterate over the txn collection, adding to an increment until we get one that does not exist.
		 * will try txnId, txnId-1, txnId-2, etc.
		 */
		do {
			$found = false;
			
			foreach( $transactions as $txn ) {
				if( $txn->getTxnId() == $transactionId ) {
					$found = true;
					$transactionId = $baseId . '-' . $increment++;
					break;
				}
			}
		}
		while( $found == true );
		
		return $transactionId;
	}
	
	/**
	 * Given the current object/payment, load the paying card, or create
	 * one if none exists.
	 */
	protected function _loadOrCreateCard( Varien_Object $payment )
	{
		$this->_log( sprintf( '_loadOrCreateCard(%s %s)', get_class( $payment ), $payment->getId() ) );
		
		if( !is_null( $this->getCard() ) ) {
			$this->setCard( $this->getCard() );
			
			return $this->getCard();
		}
		elseif( $payment->hasTokenbaseId() && $payment->getTokenbaseId() ) {
			return $this->loadAndSetCard( $payment->getTokenbaseId() );
		}
		elseif( $this->_paymentContainsCard( $payment ) === true ) {
			$card = Mage::getModel( $this->_code . '/card' );
			$card->setMethod( $this->_code )
				 ->setMethodInstance( $this )
				 ->setCustomer( $this->getCustomer(), $payment )
				 ->importPaymentInfo( $payment );
			
			if( $payment->getOrder() ) {
				$card->setAddress( $payment->getOrder()->getBillingAddress() );
			}
			elseif( $payment->getBillingAddress() ) {
				$card->setAddress( $payment->getBillingAddress() );
			}
			else {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Could not find billing address.') );
			}
			
			$card->save();
			
			$this->setCard( $card );
			
			return $card;
		}
		
		/**
		 * This error will be thrown if we were unable to load a card and had no data to create one.
		 */
		$this->_log( Mage::helper('tokenbase')->__('Invalid payment data provided. Please check the form and try again.') );
		
		throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Invalid payment data provided. Please check the form and try again.') );
	}
	
	/**
	 * Return boolean whether given payment object includes new card info.
	 */
	protected function _paymentContainsCard( Varien_Object $payment )
	{
		if( $payment->hasCcNumber() && $payment->hasCcExpYear() && $payment->hasCcExpMonth() ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Resync billing address et al. before auth/capture.
	 */
	protected function _resyncStoredCard( $payment )
	{
		$this->_log( sprintf( '_resyncStoredCard(%s %s)', get_class( $payment ), $payment->getId() ) );
		
		if( $this->getCard() instanceof ParadoxLabs_TokenBase_Model_Card && $this->getCard()->getId() > 0 ) {
			$haveChanges = false;
			
			/**
			 * Any changes that we can see? Check the payment info and main address fields.
			 */
			if( $this->getCard()->getOrigData('additional') != null && $this->getCard()->getOrigData('additional') != $this->getCard()->getData('additional') ) {
				$haveChanges = true;
			}
			
			if( $payment->getOrder() ) {
				$address = $payment->getOrder()->getBillingAddress();
			}
			elseif( $payment->getBillingAddress() ) {
				$address = $payment->getBillingAddress();
			}
			
			if( isset( $address ) && $address instanceof Mage_Customer_Model_Address_Abstract ) {
				foreach( array( 'firstname', 'lastname', 'company', 'street', 'city', 'country_id', 'region', 'region_id', 'postcode' ) as $field ) {
					if( $this->getCard()->getAddress( $field ) != $address->getData( $field ) ) {
						$this->getCard()->setAddress( $address );
						
						$haveChanges = true;
						break;
					}
				}
			}
			
			if( $haveChanges === true ) {
				if( $this->hasInfoInstance() !== true ) {
					$this->setInfoInstance( $payment );
				}
				
				$this->getCard()->setMethodInstance( $this );
				$this->getCard()->setInfoInstance( $payment );
				
				$this->getCard()->save();
			}
		}
		
		return $this;
	}
	
	/**
	 * Write a message to the logs, nice and abstractly.
	 */
	protected function _log( $message )
	{
		Mage::helper('tokenbase')->log( $this->_code, $message );
		
		return $this;
	}
	
	/**
	 * Stubs, implement in methods as convenient.
	 */
	protected function _beforeAuthorize( Varien_Object $payment, $amount ) {}
	protected function _beforeCapture( Varien_Object $payment, $amount ) {}
	protected function _beforeFraudUpdate( Varien_Object $payment, $transactionId ) {}
	protected function _beforeRefund( Varien_Object $payment, $amount ) {}
	protected function _beforeVoid( Varien_Object $payment ) {}
	protected function _afterAuthorize( Varien_Object $payment, $amount, Varien_Object $response ) {}
	protected function _afterCapture( Varien_Object $payment, $amount, Varien_Object $response ) {}
	protected function _afterFraudUpdate( Varien_Object $payment, $transactionId, Varien_Object $response ) {}
	protected function _afterRefund( Varien_Object $payment, $amount, Varien_Object $response ) {}
	protected function _afterVoid( Varien_Object $payment, Varien_Object $response ) {}
}
