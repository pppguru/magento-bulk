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

class ParadoxLabs_TokenBase_Model_Card extends Mage_Core_Model_Abstract
{
	protected $_eventPrefix	= 'tokenbase_card';
	
	protected $_additional	= null;
	protected $_address		= null;
	protected $_instance	= null;
	protected $_method		= null;
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('tokenbase/card');
	}
	
	/**
	 * Check whether customer has permission to use/modify this card. Guests, never.
	 */
	public function hasOwner( $customerId )
	{
		$customerId = intval( $customerId );
		
		if( $customerId < 1 ) {
			return false;
		}
		
		return ( $this->getCustomerId() == $customerId ? true : false );
	}
	
	/**
	 * Get billing address or some part thereof.
	 */
	public function getAddress( $key='' )
	{
		if( is_null( $this->_address ) ) {
			$this->_address = unserialize( parent::getAddress() );
		}
		
		if( $key !== '' ) {
			return ( isset( $this->_address[ $key ] ) ? $this->_address[ $key ] : null );
		}
		
		return $this->_address;
	}
	
	/**
	 * Get billing address rebuilt as an object.
	 */
	public function getAddressObject()
	{
		$address = Mage::getModel('customer/address_abstract');
		$address->setData( $this->getAddress() );
		
		return $address;
	}
	
	/**
	 * Set the billing address for the card.
	 */
	public function setAddress( Mage_Customer_Model_Address_Abstract $address )
	{
		$addressData = $address->getData();
		
		Mage::helper('tokenbase')->cleanupArray( $addressData );
		
		$this->_address = null;
		
		return parent::setAddress( serialize( $addressData ) );
	}
	
	/**
	 * Set the customer account (if any) for the card.
	 */
	public function setCustomer( Mage_Customer_Model_Customer $customer, Mage_Payment_Model_Info $payment=null )
	{
		if( $customer->getEmail() != '' ) {
			$this->setCustomerEmail( $customer->getEmail() );
			
			/**
			 * Make an ID if we don't have one (and hope this doesn't break anything)
			 */
			if( $customer->getId() < 1 ) {
				$customer->save();
			}
			
			$this->setCustomerId( $customer->getId() );
			
			parent::setCustomer( $customer );
		}
		elseif( !is_null( $payment ) ) {
			$model = null;
			
			/**
			 * If we have no email, try to find it from current scope data.
			 */
			if( $payment->getQuote() != null && $payment->getQuote()->getBillingAddress() != null && $payment->getQuote()->getBillingAddress()->getCustomerEmail() != '' ) {
				$model = $payment->getQuote();
			}
			elseif( $payment->getOrder() != null && ( $payment->getOrder()->getCustomerEmail() != '' || ( $payment->getOrder()->getBillingAddress() != null && $payment->getOrder()->getBillingAddress()->getCustomerEmail() != '' ) ) ) {
				$model = $payment->getOrder();
			}
			else {
				/**
				 * This will fall back to checkout/session if onepage has no quote loaded.
				 * Should work for all checkouts that use normal Magento processes.
				 */
				$model = Mage::getSingleton('checkout/type_onepage')->getQuote();
			}
			
			if( !is_null( $model ) ) {
				if( $model->getCustomerEmail() == '' && $model->getBillingAddress() instanceof Varien_Object && $model->getBillingAddress()->getEmail() != '' ) {
					$model->setCustomerEmail( $model->getBillingAddress()->getEmail() );
				}
				
				if( $model->hasEmail() ) {
					$this->setCustomerEmail( $model->getEmail() );
				}
				elseif( $model->hasCustomerEmail() ) {
					$this->setCustomerEmail( $model->getCustomerEmail() );
				}
				
				$this->setCustomerId( intval( $model->getCustomerId() ) );
			}
		}
		
		return $this;
	}
	
	/**
	 * Get the customer object (if any) for the card.
	 */
	public function getCustomer()
	{
		if( $this->hasCustomer() ) {
			return parent::getCustomer();
		}
		
		$customer = Mage::getModel('customer/customer');
		
		if( $this->getCustomerId() > 0 ) {
			$customer->load( $this->getCustomerId() );
		}
		else {
			$customer->setEmail( $this->getCustomerEmail() );
		}
		
		parent::setCustomer( $customer );
		
		return $customer;
	}
	
	/**
	 * Set card payment data from a quote or order payment instance.
	 */
	public function importPaymentInfo( Mage_Payment_Model_Info $payment )
	{
		if( $payment instanceof Mage_Payment_Model_Info ) {
			if( $payment->getAdditionalInformation('save') === 0 ) {
				$this->setActive(0);
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
			
			$this->setInfoInstance( $payment );
			
			if( $this->getMethodInstance()->hasInfoInstance() !== true ) {
				$this->getMethodInstance()->setInfoInstance( $payment );
			}
		}
		
		return $this;
	}
	
	/**
	 * Get the arbitrary type instance for this card.
	 * Response will extend ParadoxLabs_TokenBase_Model_Card.
	 */
	public function getTypeInstance()
	{
		if( is_null( $this->_instance ) ) {
			if( $this->hasMethod() ) {
				$this->_instance = Mage::getModel( $this->getMethod() . '/card' );
				$this->_instance->setData( $this->getData() );
			}
			else {
				return $this;
			}
		}
		
		return $this->_instance;
	}
	
	/**
	 * Set the method instance for this card. This is often necessary to route card data properly.
	 */
	public function setMethodInstance( ParadoxLabs_TokenBase_Model_Method $method )
	{
		$this->_method = $method;
		
		return $this;
	}
	
	/**
	 * Get the arbitrary method instance.
	 * Response will extend ParadoxLabs_TokenBase_Model_Method.
	 */
	public function getMethodInstance()
	{
		if( is_null( $this->_method ) ) {
			if( $this->hasMethod() ) {
				$this->_method = Mage::helper('payment')->getMethodInstance( $this->getMethod() );
			}
			else {
				Mage::throwException( Mage::helper('tokenbase')->__( 'Payment method is unknown for the current card.' ) );
			}
		}
		
		return $this->_method;
	}
	
	/**
	 * Get card label (formatted number).
	 */
	public function getLabel()
	{
		if( $this->getAdditional('cc_last4') ) {
			return Mage::helper('tokenbase')->__( 'XXXX-%s', $this->getAdditional('cc_last4') );
		}
		
		return '';
	}
	
	/**
	 * Get additional card data.
	 * If $key is set, will return that value or null;
	 * otherwise, will return an array of all additional date.
	 */
	public function getAdditional( $key='' )
	{
		if( is_null( $this->_additional ) ) {
			$this->_additional = unserialize( parent::getAdditional() );
		}
		
		if( $key !== '' ) {
			return ( isset( $this->_additional[ $key ] ) ? $this->_additional[ $key ] : null );
		}
		
		return $this->_additional;
	}
	
	/**
	 * Set additional card data.
	 * Can pass in a key-value pair to set one value,
	 * or a single parameter (associative array) to overwrite all data.
	 */
	public function setAdditional( $key, $value=null )
	{
		if( !is_null( $value ) ) {
			if( is_null( $this->_additional ) ) {
				$this->_additional = array();
			}
			
			$this->_additional[ $key ] = $value;
		}
		elseif( is_array( $key ) ) {
			$this->_additional = $key;
		}
		
		return parent::setAdditional( serialize( $this->_additional ) );
	}
	
	/**
	 * Check if card is connected to any pending orders.
	 */
	public function isInUse()
	{
		$orders	= Mage::getModel('sales/order')->getCollection()
						->addAttributeToSelect( '*' )
						->addAttributeToFilter( 'customer_id', $this->getCustomerId() )
						->addAttributeToFilter( 'status', array( 'like' => 'pending%' ) );
		
		if( count($orders) > 0 ) {
			foreach( $orders as $order ) {
				$payment = $order->getPayment();
				
				if( $payment->getMethod() == $this->getMethod() && $payment->getTokenbaseId() == $this->getId() ) {
					// If we found an order with this card that is not complete, closed, or canceled,
					// it is still active and the payment ID is important. No editey.
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Change last_use date to the current time.
	 */
	public function updateLastUse()
	{
		$this->setLastUse( ParadoxLabs_TokenBase_Helper_Data::now() );
		
		return $this;
	}
	
	/**
	 * Delete this card, or hide and queue for deletion after the refund period.
	 */
	public function queueDeletion()
	{
		$this->setActive(0);
		
		return $this;
	}
	
	/**
	 * Load card by security hash.
	 */
	public function loadByHash( $hash )
	{
		$this->_getResource()->loadByHash( $this, $hash );
		
		return $this;
	}
	
	/**
	 * Finalize before saving. Instances should sync with the gateway here.
	 * 
	 * Set $this->_dataSaveAllowed to false or throw exception to abort.
	 */
	protected function _beforeSave()
	{
		parent::_beforeSave();
		
		/**
		 * If the payment ID has changed, look for any duplicate payment records that might be stored.
		 */
		if( $this->getOrigData('payment_id') != $this->getPaymentId() ) {
			$dupe = Mage::getModel('tokenbase/card')->getCollection()
							->addFieldToFilter( 'method', $this->getMethod() )
							->addFieldToFilter( 'profile_id', $this->getProfileId() )
							->addFieldToFilter( 'payment_id', $this->getPaymentId() )
							->addFieldToFilter( 'id', array( 'neq' => $this->getId() ) )
							->getFirstItem();
			
			/**
			 * If we find a duplicate, switch to that one, but retain the current info otherwise.
			 */
			if( $dupe && $dupe->getId() > 0 && $dupe->getId() != $this->getId() ) {
				$this->_mergeCardOnto( $dupe );
			}
		}
		
		/**
		 * If we are not admin, record current IP.
		 */
		if( Mage::app()->getStore()->isAdmin() == false ) {
			$this->setCustomerIp( Mage::helper('core/http')->getRemoteAddr() );
		}
		
		/**
		 * Create unique hash for security purposes.
		 */
		if( $this->getHash() == '' ) {
			$this->setHash( sha1( 'tokenbase' . time() . $this->getCustomerId() . $this->getCustomerEmail() . $this->getMethod() . $this->getProfileId() . $this->getPaymentId() ) );
		}
		
		/**
		 * Update dates.
		 */
		if( $this->isObjectNew() ) {
			$this->setCreatedAt( ParadoxLabs_TokenBase_Helper_Data::now() );
		}
		
		$this->setUpdatedAt( ParadoxLabs_TokenBase_Helper_Data::now() );
		
		return $this;
	}
	
	/**
	 * Finalize before deleting. Instances should sync with the gateway here.
	 * 
	 * Throw exception to abort.
	 */
	protected function _beforeDelete()
	{
		return parent::_beforeDelete();
	}
	
	/**
	 * Merge the current card info over the given one. Retain the given card's ID.
	 * 
	 * It is assumed that the current card and the one given have the same gateway reference.
	 *
	 * @param  ParadoxLabs_TokenBase_Model_Card $card Card to merge current data onto.
	 * @return $this
	 */
	protected function _mergeCardOnto( ParadoxLabs_TokenBase_Model_Card $card )
	{
		Mage::helper('tokenbase')->log( $this->getMethod(), sprintf( 'Merging duplicate payment data into card %s', $card->getId() ) );
		
		$this->setId( $card->getId() );
		$this->isObjectNew( false );
		
		return $this;
	}
}
