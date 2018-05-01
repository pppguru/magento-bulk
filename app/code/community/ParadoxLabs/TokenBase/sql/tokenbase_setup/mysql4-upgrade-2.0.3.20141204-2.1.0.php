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
		 * Add card hash bit
		 */
		$table = $this->getTable('tokenbase/card');
		
		$this->run("ALTER TABLE {$table} 
			CHANGE `method` `method` VARCHAR(32),
			ADD `hash` VARCHAR(40) NULL COMMENT 'Unique Hash',
			ADD UNIQUE (`hash`);");
		$this->run("UPDATE {$table} SET `hash`=SHA1( CONCAT('tokenbase', customer_id, customer_email, method, profile_id, payment_id) ) WHERE `hash` IS NULL;");
		
		
		$this->endSetup();
		
		$processLock->unlock();
	}
}
catch( Exception $e ) {
	Mage::log( 'SETUP FAILURE in tokenbase_setup:mysql4-upgrade-1.0.0-2.1.0: ' . (string)$e, null, 'tokenbase.log', true );
	
	if( isset( $processLock ) && $processLock instanceof Mage_Index_Model_Process ) {
		$processLock->unlock();
	}
	
	throw $e;
}
