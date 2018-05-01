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

class ParadoxLabs_TokenBase_Block_Customer_Link extends Mage_Core_Block_Template
{
	public function addProfileLink()
	{
		$navigation	= $this->getParentBlock();
		
		if( $navigation && count( Mage::helper('tokenbase')->getActiveMethods() ) > 0 ) {
			$navigation->addLink( 'tokenbase', 'customer/paymentinfo', $this->__("My Payment Data"), array( '_secure' => true ) );
		}
	}
}
