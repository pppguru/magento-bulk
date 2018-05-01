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

class ParadoxLabs_TokenBase_Block_Customer_Wrapper extends Mage_Core_Block_Template
{
	/**
	 * Each method must have a separate page to support hosted forms and such.
	 * This handles the outer-page-bits (type selector, mostly).
	 */
	
	public function getActiveMethods()
	{
		return Mage::helper('tokenbase')->getActiveMethods();
	}
}
