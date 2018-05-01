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

class ParadoxLabs_AuthorizeNetCim_Block_Adminhtml_Customer_Link extends Mage_Adminhtml_Block_Template
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function getTabLabel()
	{
		return $this->__('Authorize.Net CIM');
	}
	
	public function getTabTitle()
	{
		return $this->__('Authorize.Net CIM');
	}
	
	public function canShowTab()
	{
		return Mage::helper('payment')->getMethodInstance('authnetcim')->isAvailable() && Mage::registry('current_customer')->getId() > 0;
	}
	
	public function isHidden()
	{
		return false;
	}
	
	public function getAfter()
	{
		return 'tags';
	}
}
