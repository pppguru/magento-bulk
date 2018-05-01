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

class ParadoxLabs_TokenBase_Model_Cron_Clean extends Mage_Core_Model_Abstract
{
	/**
	 * Perform generic maintenance actions...
	 */
	public function cleanData()
	{
		if( Mage::getStoreConfig('payment_services/tokenbase/clean_old_cards') != 1 ) {
			return;
		}
		
		/**
		 * Prune inactive cards older than 120 days (beyond auth and refund periods)
		 */
		$cards = Mage::getModel('tokenbase/card')->getCollection()
						->addFieldToFilter( 'active', '0' )
						->addFieldToFilter( 'updated_at', array( 'lt' => date( 'c', strtotime( '-120 days' ) ), 'date' => true ) )
						->addFieldToFilter(
							array(
								'last_use',
								'last_use',
							),
							array(
								array( 'lt' => date( 'c', strtotime( '-120 days' ) ), 'date' => true ),
								array( 'null' => true ),
							)
						);
		
		$affectedCount	= 0;
		
		foreach( $cards as $card ) {
			$card = $card->getTypeInstance();
			
			$cardId			= $card->getId();
			$cardMethod		= $card->getMethod();
			$cardPaymentId	= $card->getPaymentId();
			
			try {
				/**
				 * Delete the card.
				 */
				$card->delete();
				
				$affectedCount++;
				
				/**
				 * Suspend any profiles using the card.
				 */
				$profiles	= Mage::getModel('sales/recurring_profile')->getCollection()
									->addFieldToFilter( 'method_code', $cardMethod )
									->addFieldToFilter( 'additional_info', array( 'like' => '%"' . $cardPaymentId . '"%' ) )
									->addFieldToFilter( 'state', array( 'in' => array( 'active', 'pending' ) ) );
				
				$count = 0;
				if( count( $profiles ) > 0 ) {
					foreach( $profiles as $profile ) {
						$profile	= Mage::getModel('sales/recurring_profile')->loadByInternalReferenceId( $profile->getInternalReferenceId() );
						$adtl		= $profile->getAdditionalInfo();
						
						if( $adtl['payment_id'] == $cardPaymentId ) {
							$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED )
									->save();
							
							$count++;
						}
					}
				}
				
				if( $count > 0 ) {
					Mage::helper('tokenbase')->log( $cardMethod, sprintf( Mage::helper('tokenbase')->__( "Deleted card %s; automatically suspended %s recurring profiles." ), $cardId, $cardMethod, $count ) );
				}
			}
			catch( Exception $e ) {
				Mage::helper('tokenbase')->log( $cardMethod, Mage::helper('tokenbase')->__( 'Error deleting card: %s', (string)$e ) );
			}
		}
		
		if( $affectedCount > 0 ) {
			Mage::helper('tokenbase')->log( 'tokenbase', sprintf( Mage::helper('tokenbase')->__( 'Deleted %s queued cards.' ), $affectedCount ) );
		}
		
		return $this;
	}
}
