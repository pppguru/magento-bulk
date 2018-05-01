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

class ParadoxLabs_TokenBase_Customer_PaymentinfoController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Ensure customers log in/register before getting to this controller.
	 */
	public function preDispatch()
	{
		parent::preDispatch();
		
		if( !Mage::getSingleton('customer/session')->authenticate($this) ) {
			$this->getResponse()->setRedirect( Mage::helper('customer')->getLoginUrl() );
			$this->setFlag( '', self::FLAG_NO_DISPATCH, true );
		}
		elseif( count( Mage::helper('tokenbase')->getActiveMethods() ) == 0 ) {
			$this->getResponse()->setRedirect( Mage::helper('customer')->getAccountUrl() );
			$this->setFlag( '', self::FLAG_NO_DISPATCH, true );
		}
		
		return $this;
	}
	
	/**
	 * Inject a layout handle to pull in the current active method.
	 */
	public function addActionLayoutHandles()
	{
		parent::addActionLayoutHandles();
		
		Mage::getSingleton('core/layout')->getUpdate()->addHandle( 'customer_paymentinfo_' . strtolower( $this->getRequest()->getRequestedActionName() ) . '_' . Mage::registry('tokenbase_method') );
		
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
				Mage::getSingleton('core/session')->addError( $this->__('No payment methods are currently available.') );
				return $this->_redirect( '*' );
			}
		}
		
		$method = Mage::registry('tokenbase_method');
		
		/**
		 * Check for card input and validate if present.
		 */
		$id	= intval( $this->getRequest()->getPost('id') );
		
		if( $id <= 0 || $this->_formKeyIsValid() !== true ) {
			$id = 0;
			
			if( Mage::getSingleton('customer/session')->hasTokenbaseFormData() ) {
				$data = Mage::getSingleton('customer/session')->getTokenbaseFormData();
				
				if( isset( $data['id'] ) && intval( $data['id'] ) > 0 ) {
					$id = intval( $data['id'] );
				}
			}
		}
		
		if( $id > 0 ) {
			$card = Mage::getModel( $method . '/card' )->load( $id );
			
			if( $card && $card->getId() == $id && $card->hasOwner( Mage::helper('tokenbase')->getCurrentCustomer()->getId() ) ) {
				Mage::register( 'active_card', $card );
			}
		}
		
		/**
		 * Output management interface
		 */
		$this->loadLayout();
		
		// Add title and breadcrumbs.
		$methodTitle = Mage::getSingleton( Mage::registry('tokenbase_method') . '/method' )->getConfigData('title');
		
		$this->_title( Mage::helper('tokenbase')->__('My Payment Data' ) )
			 ->_title( $methodTitle );
		
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		if( $breadcrumbs && $breadcrumbs instanceof Mage_Core_Block_Template ) {
			$breadcrumbs->addCrumb( 'tokenbase', array( 'label' => Mage::helper('tokenbase')->__('My Payment Data'), 'title' => Mage::helper('tokenbase')->__('My Payment Data'), 'link' => Mage::getUrl('*/*') ) );
			$breadcrumbs->addCrumb( 'tokenbase_method', array( 'label' => $methodTitle, 'title' => $methodTitle, 'link' => Mage::getUrl( '*/*/*', array( 'method' => Mage::registry('tokenbase_method') ) ) ) );
		}
		
		$this->renderLayout();
	}
	
	/**
	 * Load a card for edit
	 */
	public function ajaxLoadAction()
	{
		$id			= intval( $this->getRequest()->getPost('id') );
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
					Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
					return 0;
				}
			}
		}
		else {
			Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
			return 0;
		}
		
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Create or update a card on save
	 */
	public function saveAction()
	{
		$id			= intval( $this->getRequest()->getPost('id') );
		$method		= $this->getRequest()->getParam('method');
		
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
					$newAddrId	= intval( Mage::app()->getRequest()->getParam('shipping_address_id') );
					
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
						
						$data = Mage::app()->getRequest()->getPost( 'billing', array() );
						
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
					$cardData = Mage::app()->getRequest()->getParam('payment');
					$cardData['method']		= $method;
					$cardData['card_id']	= $card->getId();
					
					if( isset( $cardData['cc_number'] ) ) {
						$cardData['cc_last4']	= substr( $cardData['cc_number'], -4 );
					}
					
					$newPayment = Mage::getModel('sales/quote_payment');
					$newPayment->setQuote( Mage::getSingleton('checkout/session')->getQuote() );
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
					
					Mage::getSingleton('customer/session')->unsTokenbaseFormData();
				}
				else {
					Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
					return $this->_redirectReferer();
				}
			}
			catch( Exception $e ) {
				Mage::getSingleton('customer/session')->setTokenbaseFormData( Mage::app()->getRequest()->getPost() );
				
				Mage::helper('tokenbase')->log( $method, (string)$e );
				Mage::getSingleton('core/session')->addError( $e->getMessage() );
				
				return $this->_redirectReferer();
			}
		}
		else {
			Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
			return $this->_redirectReferer();
		}
		
		Mage::getSingleton('core/session')->addSuccess( $this->__('Payment data saved successfully.') );
		
		$this->_redirect( '*/*', array( 'method' => $method ) );
	}
	
	/**
	 * Delete a card
	 */
	public function deleteAction()
	{
		$id			= intval( $this->getRequest()->getPost('id') );
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
					
					Mage::getSingleton('core/session')->addSuccess( $this->__('Payment record deleted.') );
				}
				else {
					Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
					return $this->_redirectReferer();
				}
			}
			catch( Exception $e ) {
				Mage::helper('tokenbase')->log( $method, (string)$e );
				
				Mage::getSingleton('core/session')->addError( $e->getMessage() );
			}
		}
		else {
			Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
			return $this->_redirectReferer();
		}
		
		$this->_redirect( '*/*', array( 'method' => $method ) );
	}
	
	/**
	 * Check whether input form key is valid
	 */
	protected function _formKeyIsValid()
	{
		$formKey	= $this->getRequest()->getParam('form_key');
		
		if( $formKey == Mage::getSingleton('core/session')->getFormKey() ) {
			return true;
		}
		
		return false;
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
}
