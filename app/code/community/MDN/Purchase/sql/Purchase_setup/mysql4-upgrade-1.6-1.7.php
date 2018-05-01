<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

//add status field to purchase_order & init orders status
$installer->run("
	ALTER TABLE  {$this->getTable('purchase_order')} ADD  `po_status` VARCHAR( 25 ) NOT NULL DEFAULT 'new' ;
	ALTER TABLE  {$this->getTable('purchase_order')} ADD INDEX (  `po_status` );
	
	update {$this->getTable('purchase_order')}
	set po_status = 'new';
	
	update {$this->getTable('purchase_order')}
	set po_status = 'complete'
	where po_finished = 1;

	update {$this->getTable('purchase_order')}
	set po_status = 'waiting_for_delivery'
	where po_finished = 0 and po_sent = 1;

");


$installer->endSetup();
