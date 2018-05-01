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

class ParadoxLabs_TokenBase_Block_Adminhtml_Customer_Cards extends Mage_Adminhtml_Block_Template
{
	protected $_code	= 'tokenbase';
	
	/**
	 * Get stored cards for the currently-active method.
	 */
	public function getCards()
	{
		return Mage::helper('tokenbase')->getActiveCustomerCardsByMethod( $this->getCode() );
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
}
