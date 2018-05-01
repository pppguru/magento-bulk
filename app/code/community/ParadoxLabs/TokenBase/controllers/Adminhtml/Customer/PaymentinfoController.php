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

class ParadoxLabs_TokenBase_Adminhtml_Customer_PaymentinfoController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Load the customer record.
	 */
	public function preDispatch()
	{
		parent::preDispatch();
		
		$customer = Mage::getModel('customer/customer');
		$customer->load( $this->getRequest()->getParam('id') );
		if( !$customer || $customer->getId() < 1 || $customer->getId() != $this->getRequest()->getParam('id') ) {
			$this->getResponse()->setBody( json_encode( array( 'success' => false, 'message' => $this->__('Could not load customer.') ) ) );
			exit;
		}
		
		Mage::register( 'current_customer', $customer, true );
		
		return $this;
	}
	
	/**
	 * Inject a layout handle to pull in the current active method.
	 */
	public function addActionLayoutHandles()
	{
		parent::addActionLayoutHandles();
		
		Mage::getSingleton('core/layout')->getUpdate()->addHandle( 'adminhtml_customer_edit_paymentinfo_' . strtolower( $this->getRequest()->getRequestedActionName() ) . '_' . Mage::registry('tokenbase_method') );
		
		return $this;
	}
	
	/**
	 * Show management interface
	 */
	public function indexAction()
	{
		/**
		 * Check for active method, or pick one if none given.
		 */
		if( $this->_methodIsValid() !== true ) {
			$methods = Mage::helper('tokenbase')->getActiveMethods();
			
			if( count( $methods ) > 0 ) {
				sort( $methods );
				
				Mage::register( 'tokenbase_method', $methods[0] );
			}
			else {
				return $this->__('No payment methods are currently available.');
			}
		}
		
		$method = Mage::registry('tokenbase_method');
		
		/**
		 * Check for card input and validate if present.
		 */
		$id	= intval( $this->getRequest()->getParam('card_id') );
		if( $id > 0  && $this->_formKeyIsValid() === true ) {
			$card = Mage::getModel( $method . '/card' )->load( $id );
			
			if( $card && $card->getId() == $id && $card->hasOwner( Mage::helper('tokenbase')->getCurrentCustomer()->getId() ) ) {
				Mage::register( 'active_card', $card );
			}
		}
		
		/**
		 * Output management interface
		 */
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Load a card for edit
	 */
	public function loadAction()
	{
		$id			= intval( $this->getRequest()->getParam('card_id') );
		$method		= $this->getRequest()->getParam('method');
		
		if( $this->_formKeyIsValid() === true && $this->_methodIsValid() === true ) {
			/**
			 * Load the card and verify we are actually the cardholder before doing anything.
			 */
			if( $id > 0 ) {
				$card = Mage::getModel( $method . '/card' )->load( $id );
				
				if( $card && $card->getId() == $id && $card->hasOwner( Mage::helper('tokenbase')->getCurrentCustomer()->getId() ) ) {
					Mage::register( 'active_card', $card );
				}
				else {
					$this->getResponse()->setBody( json_encode( array( 'success' => false, 'message' => $this->__('Invalid Request.') ) ) );
					return;
				}
			}
		}
		else {
			$this->getResponse()->setBody( json_encode( array( 'success' => false, 'message' => $this->__('Invalid Request.') ) ) );
			return;
		}
		
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Create or update a card on save
	 */
	public function saveAction()
	{
		$method		= $this->getRequest()->getParam('method');
		$input 		= $this->getRequest()->getParam( $method );
		$id			= intval( $input['card_id'] );
		
		if( $this->_formKeyIsValid() === true && $this->_methodIsValid() === true ) {
			/**
			 * Convert inputs into an address and payment object for storage.
			 */
			try {
				/**
				 * Load the card and verify we are actually the cardholder before doing anything.
				 */
				$card		= Mage::getModel( $method . '/card' )->load( $id );
				$customer	= Mage::helper('tokenbase')->getCurrentCustomer();
				
				if( $card && ( $id == 0 || ( $card->getId() == $id && $card->hasOwner( $customer->getId() ) ) ) ) {
					/**
					 * Process address data
					 */
					$newAddrId	= isset( $input['shipping_address_id'] ) ? intval( $input['shipping_address_id'] ) : 0;
					
					// Existing address
					if( $newAddrId > 0 ) {
						$newAddr = Mage::getModel('customer/address')->load( $newAddrId );
						
						if( $newAddr->getCustomerId() != $customer->getId() ) {
							Mage::throwException( $this->__('An error occurred. Please try again.') );
						}
					}
					// New address
					else {
						$newAddr = Mage::getModel('customer/address');
						$newAddr->setCustomerId( $customer->getId() );
						
						$data = isset( $input['billing'] ) ? $input['billing'] : array();
						
						$addressForm    = Mage::getModel('customer/form');
						$addressForm->setFormCode('customer_address_edit');
						$addressForm->setEntity( $newAddr );
						
						$addressData    = $addressForm->extractData( $addressForm->prepareRequest( $data ) );
						$addressErrors  = $addressForm->validateData( $addressData );
						
						if( $addressErrors !== true ) {
							Mage::throwException( implode( ' ', $addressErrors ) );
						}
						
						$addressForm->compactData( $addressData );
						$addressErrors  = $newAddr->validate();
						
						$newAddr->setSaveInAddressBook( false );
						$newAddr->implodeStreetAddress();
					}
					
					/**
					 * Process payment data
					 */
					$cardData = isset( $input['payment'] ) ? $input['payment'] : array();
					$cardData['method']		= $method;
					$cardData['card_id']	= $card->getId();
					
					if( isset( $cardData['cc_number'] ) ) {
						$cardData['cc_last4']	= substr( $cardData['cc_number'], -4 );
					}
					
					$quote = Mage::getModel('sales/quote');
					$quote->setStoreId( Mage::helper('tokenbase')->getCurrentStoreId() );
					$quote->setCustomerId( $card->getCustomerId() );
					
					$newPayment = Mage::getModel('sales/quote_payment');
					$newPayment->setQuote( $quote );
					$newPayment->getQuote()->getBillingAddress()->setCountryId( $newAddr->getCountryId() );
					$newPayment->importData( $cardData );
					
					/**
					 * Save payment data
					 */
					$card->setMethod( $method );
					$card->setActive( 1 );
					$card->setCustomer( $customer );
					$card->setAddress( $newAddr );
					$card->importPaymentInfo( $newPayment );
					$card->save();
					
					Mage::getSingleton('adminhtml/session')->unsTokenbaseFormData();
				}
				else {
					$this->getResponse()->setBody( json_encode( array( 'success' => false, 'message' => $this->__('Invalid Request.') ) ) );
					return;
				}
			}
			catch( Exception $e ) {
				Mage::getSingleton('adminhtml/session')->setTokenbaseFormData( $input );
				
				Mage::helper('tokenbase')->log( $method, (string)$e );
				$this->getResponse()->setBody( json_encode( array( 'success' => false, 'message' => $this->__( 'ERROR: %s', $e->getMessage() ) ) ) );
				return;
			}
		}
		else {
			$this->getResponse()->setBody( json_encode( array( 'success' => false, 'message' => $this->__('Invalid Request.') ) ) );
			return;
		}
		
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Delete a card
	 */
	public function deleteAction()
	{
		$id			= intval( $this->getRequest()->getParam('card_id') );
		$method		= $this->getRequest()->getParam('method');
		
		if( $this->_formKeyIsValid() === true && $this->_methodIsValid() === true && $id > 0 ) {
			try {
				/**
				 * Load the card and verify we are actually the cardholder before doing anything.
				 */
				$card = Mage::getModel( $method . '/card' )->load( $id );
				
				if( $card && $card->getId() == $id && $card->hasOwner( Mage::helper('tokenbase')->getCurrentCustomer()->getId() ) ) {
					$card->queueDeletion()
						 ->save();
				}
				else {
					$this->getResponse()->setBody( json_encode( array( 'success' => false, 'error' => $this->__('Invalid Request.') ) ) );
					return;
				}
			}
			catch( Exception $e ) {
				Mage::helper('tokenbase')->log( $method, (string)$e );
				
				$this->getResponse()->setBody( json_encode( array( 'success' => false, 'error' => $this->__( 'ERROR: %s', $e->getMessage() ) ) ) );
				return;
			}
		}
		else {
			$this->getResponse()->setBody( json_encode( array( 'success' => false, 'error' => $this->__('Invalid Request.') ) ) );
			return;
		}
		
		$this->getResponse()->setBody( json_encode( array( 'success' => true, 'error' => $this->__('Payment record deleted.') ) ) );
		return;
	}
	
	/**
	 * Check whether input form key is valid
	 */
	protected function _formKeyIsValid()
	{
		return true;
	}
	
	/**
	 * Check whether input method is valid, register if so.
	 */
	protected function _methodIsValid()
	{
		$method	= $this->getRequest()->getParam('method');
		
		if( in_array( $method, Mage::helper('tokenbase')->getActiveMethods() ) !== false ) {
			Mage::register( 'tokenbase_method', $method );
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check ACP perms
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
	}
}
