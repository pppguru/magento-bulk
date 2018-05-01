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

abstract class ParadoxLabs_TokenBase_Block_Form extends Mage_Payment_Block_Form_Cc
{
	protected $_cards		= null;
	
	/**
	 * Instantiate with default payment form (stored CC).
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('paradoxlabs/tokenbase/form.phtml');
	}
	
	/**
	 * Get/load stored cards for the current customer and method.
	 */
	public function getStoredCards()
	{
		if( is_null( $this->_cards ) ) {
			/**
			 * If logged in, fetch the method cards for the current customer.
			 * If not, short circuit / return empty array.
			 */
			$customer = Mage::helper('tokenbase')->getCurrentCustomer();
			
			if( Mage::app()->getStore()->isAdmin() || $customer && $customer->getId() > 0 ) {
				$this->_cards = Mage::helper('tokenbase')->getActiveCustomerCardsByMethod( $this->getMethodCode() );
			}
			else {
				$this->_cards = array();
			}
		}
		
		return $this->_cards;
	}
	
	/**
	 * Check whether we have any cards stored.
	 */
	public function haveStoredCards()
	{
		$cards = $this->getStoredCards();
		
		return ( count( $cards ) > 0 ? true : false );
	}
	
	/**
	 * Check whether we are logged in or registering, or just a guest.
	 */
	public function isGuestCheckout()
	{
		if( Mage::app()->getStore()->isAdmin() ) {
			// Admin has no guest checkout feature (out of box).
			return false;
		}
		else {
			if( Mage::getSingleton('customer/session')->isLoggedIn() == false && Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod() != 'register' ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check whether we are buying a nominal item.
	 */
	public function isNominalCheckout()
	{
		if( Mage::app()->getStore()->isAdmin() ) {
			if( Mage::getSingleton('adminhtml/session_quote')->hasQuoteId() ) {
				$quote	= Mage::getSingleton('adminhtml/session_quote')->getQuote();
			}
			else {
				$quote	= false;
			}
		}
		else {
			$quote	= Mage::getSingleton('checkout/session')->getQuote();
		}
		
		if( $quote && $quote->getId() ) {
			$items	= $quote->getAllItems();
			
			if( $items && isset($items[0]) && $items[0]->isNominal() ) {
				return true;
			}
		}
		
		return false;
	}
}
