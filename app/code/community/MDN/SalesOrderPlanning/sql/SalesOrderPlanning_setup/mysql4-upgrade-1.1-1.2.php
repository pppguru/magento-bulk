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

//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order');
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');

if ($useEavModel)
{
	$installer->run("	
	
	ALTER TABLE  {$this->getTable('sales_order')} 
	ADD  `anounced_date` DATE NULL,
	ADD  `anounced_date_max` DATE NULL
	;
	
	");
	
	$installer->addAttribute('order', 'anounced_date', array('type'=>'static'));
	
	$installer->addAttribute('order', 'anounced_date_max', array('type'=>'static'));

}
else 
{
	$installer->run("	
		ALTER TABLE  {$this->getTable('sales_flat_order')} 
		ADD  `anounced_date` DATE NULL COMMENT  'date for delivery',
		ADD  `anounced_date_max` DATE NULL COMMENT  'max date for delivery';
	");
}
	
$installer->endSetup();
