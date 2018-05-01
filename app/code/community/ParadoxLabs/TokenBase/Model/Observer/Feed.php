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

class ParadoxLabs_TokenBase_Model_Observer_Feed extends Mage_AdminNotification_Model_Feed
{
	public function getFeedUrl()
	{
		$methods		= Mage::helper('tokenbase')->getAllMethods();
		$methods[]		= 'tokenbase';
		
		$protocol		= Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
		
		$this->_feedUrl	= $protocol . 'store.paradoxlabs.com/updates.php?key=' . implode( ',', $methods ) . '&version=' . (string)Mage::getConfig()->getNode()->modules->ParadoxLabs_TokenBase->version;
		
		return $this->_feedUrl;
	}
	
	/**
	 * Check for notifications via the update RSS feed.
	 */
	public function observe()
	{
		$this->checkUpdate();
	}
}
