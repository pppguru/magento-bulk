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

class ParadoxLabs_TokenBase_Model_Resource_Card extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('tokenbase/card', 'id');
	}
	
	/**
	 * Load card by hash
	 */
	public function loadByHash(ParadoxLabs_TokenBase_Model_Card $card, $hash)
	{
		$adapter = $this->_getReadAdapter();
		$select  = $adapter->select()
							->from( $this->getMainTable(), array( $this->getIdFieldName() ) )
							->where('hash = :hash');
		
		$cardId  = $adapter->fetchOne( $select, array( 'hash' => $hash ) );
		
		if( $cardId ) {
			$this->load( $card, $cardId );
		}
		else {
			$card->setData( array() );
		}
		
		return $this;
	}
}
