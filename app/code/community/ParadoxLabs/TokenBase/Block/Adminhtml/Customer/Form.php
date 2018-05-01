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

class ParadoxLabs_TokenBase_Block_Adminhtml_Customer_Form extends Mage_Adminhtml_Block_Template
{
	protected $_code	= 'tokenbase';
	
	/**
	 * Get the address block for dynamic state/country selection on forms.
	 */
	public function getAddressBlock()
	{
		return Mage::helper('tokenbase')->getAddressBlock();
	}
	
	/**
	 * Return active card (if any).
	 */
	public function getCard()
	{
		return Mage::helper('tokenbase')->getActiveCard( $this->getCode() );
	}
	
	/**
	 * Get the current method code.
	 */
	public function getCode()
	{
		if( parent::hasCode() ) {
			return parent::getCode();
		}
		
		return $this->_code;
	}
	
	/**
	 * Return whether or not this is a card edit.
	 */
	public function isEdit()
	{
		return ( $this->getCard()->getId() > 0 ) ? true : false;
	}
}
