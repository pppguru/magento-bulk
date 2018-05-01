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

class ParadoxLabs_TokenBase_Model_Observer_Refund extends Mage_Catalog_Model_Observer
{
	/**
	 * If we're doing a partial refund, don't mark it as fully refunded
	 * unless the full amount is done.
	 */
	public function processRefund( $observer )
	{
		$memo		= $observer->getEvent()->getCreditmemo();
		$methods	= Mage::helper('tokenbase')->getAllMethods();
		
		if( in_array( $memo->getOrder()->getPayment()->getMethod(), $methods ) ) {
			if( $memo->getInvoice() && $memo->getInvoice()->getBaseTotalRefunded() < $memo->getInvoice()->getBaseGrandTotal() ) {
				$memo->getInvoice()->setIsUsedForRefund(false);
			}
		}
	}
}
