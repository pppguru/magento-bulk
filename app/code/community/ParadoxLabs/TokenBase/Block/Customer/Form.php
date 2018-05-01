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

class ParadoxLabs_TokenBase_Block_Customer_Form extends Mage_Core_Block_Template
{
	protected $_card;
	
	/**
	 * Get the address block for dynamic state/country selection on forms.
	 */
	public function getAddressBlock()
	{
		return Mage::helper('tokenbase')->getAddressBlock();
	}
	
	/**
	 * Get customer address dropdown
	 */
	public function getAddressesHtmlSelect()
	{
		return Mage::helper('tokenbase')->getAddressesHtmlSelect('billing');
	}
	
	/**
	 * Return active card (if any).
	 */
	public function getCard()
	{
		if( is_null( $this->_card ) ) {
			try {
				$this->_card = Mage::helper('tokenbase')->getActiveCard();
			}
			catch( Exception $e ) {
				$this->_card = Mage::getModel('tokenbase/card');
			}
		}
		
		return $this->_card;
	}
	
	/**
	 * Return the form submit action.
	 */
	public function getAction()
	{
		return Mage::getUrl( '*/*/save', array( '_secure' => true ) );
	}
	
	/**
	 * Return whether or not this is a card edit.
	 */
	public function isEdit()
	{
		return ( $this->getCard()->getId() > 0 ) ? true : false;
	}
}
