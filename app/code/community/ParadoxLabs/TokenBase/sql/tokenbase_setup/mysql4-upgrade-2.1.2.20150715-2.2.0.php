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

try {
	/**
	 * Use filesystem locking to avoid duplication.
	 */
	$processLock = Mage::getModel('index/process');
	$processLock->setId('tokenbase_setup');
	
	if( !$processLock->isLocked() ) {	
		$processLock->lockAndBlock();
		
		$this->startSetup();
		
		/**
		 * Add index to sales_flat_order.ext_customer_id to speed up legacy data conversion (1507075)
		 */
		$table		= $this->getTable('sales/order');
		
		$needIndex	= true;
		$indexes	= $this->getConnection()->getIndexList( $table );
		foreach( $indexes as $index ) {
			if( isset( $index['COLUMNS_LIST'] ) && in_array( 'ext_customer_id', $index['COLUMNS_LIST'] ) ) {
				$needIndex = false;
				break;
			}
		}
		
		if( $needIndex === true ) {
			$this->getConnection()->addKey(
				$table,
				$this->getIdxName( 'sales/order', array( 'ext_customer_id' ), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX ),
				array( 'ext_customer_id' ),
				Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
			);
		}
		
		$this->endSetup();
		
		$processLock->unlock();
	}
}
catch( Exception $e ) {
	Mage::log( 'SETUP FAILURE in tokenbase_setup:mysql4-upgrade-2.1.2-2.2.0: ' . (string)$e, null, 'tokenbase.log', true );
	
	if( isset( $processLock ) && $processLock instanceof Mage_Index_Model_Process ) {
		$processLock->unlock();
	}
	
	throw $e;
}
