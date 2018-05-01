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

class ParadoxLabs_TokenBase_Block_Adminhtml_Customer_Method extends Mage_Adminhtml_Block_Template
{
	protected $_code	= 'tokenbase';
	
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
	 * Return whether or not this is an AJAX request.
	 */
	public function isAjax()
	{
		return ( $this->getRequest()->getParam('isAjax') == 1 ) ? true : false;
	}
	
	/**
	 * Return the current customer record.
	 */
	public function getCustomer()
	{
		return Mage::helper('tokenbase')->getCurrentCustomer();
	}
}
