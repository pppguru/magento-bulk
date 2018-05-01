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

try {
	/**
	 * Use filesystem locking to avoid duplication.
	 */
	$processLock = Mage::getModel('index/process');
	$processLock->setId('authnetcim_address_setup');
	
	if( !$processLock->isLocked() ) {	
		$processLock->lockAndBlock();
		
		$this->startSetup();
		
		$this->addAttribute('customer', 'authnetcim_profile_version', array(
			'label'				=> 'Authorize.Net CIM: Profile version (for updating legacy data)',
			'type'				=> 'int',
			'input'				=> 'text',
			'default'			=> '100',
			'position'			=> 71,
			'visible'			=> true,
			'required'			=> false,
			'user_defined'		=> true,
			'searchable'		=> false,
			'filterable'		=> false,
			'comparable'		=> false,
			'visible_on_front'	=> false,
			'unique'			=> false,
		));
		
		$this->addAttribute('customer_address', 'authnetcim_shipping_id', array(
			'type'				=> 'varchar',
			'input'				=> 'text',
			'label'				=> 'Authorize.Net CIM: Shipping Address ID',
			'global'			=> true,
			'visible'			=> false,
			'required'			=> false,
			'user_defined'		=> true,
			'visible_on_front'	=> false,
		));
		
		$this->endSetup();
		
		$processLock->unlock();
	}
}
catch( Exception $e ) {
	Mage::log( 'SETUP FAILURE in authnetcim_address_setup:mysql4-install-2.0.0: ' . (string)$e, null, 'authnetcim.log', true );
	
	if( isset( $processLock ) && $processLock instanceof Mage_Index_Model_Process ) {
		$processLock->unlock();
	}
	
	Mage::throwException( $e->getMessage() );
}
