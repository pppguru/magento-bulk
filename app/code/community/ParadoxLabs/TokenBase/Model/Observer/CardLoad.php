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

class ParadoxLabs_TokenBase_Model_Observer_CardLoad extends Mage_Catalog_Model_Observer
{
	/**
	 * Check for any cards queued for deletion before we load the card list.
	 * This will happen if there is a failure during order submit. We can't
	 * actually save it there, so we register and do it here instead. Magic.
	 */
	public function checkQueuedForDeletion( $observer )
	{
		$card = Mage::registry('queue_card_deletion');
		
		if( $card && $card->getActive() == 1 && $card->getId() > 0 ) {
			$card->queueDeletion()
				 ->setNoSync( true )
				 ->save();
		}
		
		return $this;
	}
}
