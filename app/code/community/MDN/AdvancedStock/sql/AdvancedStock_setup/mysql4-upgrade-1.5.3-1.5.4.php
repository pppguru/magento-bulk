<?php
 
$installer = $this;
 
$installer->startSetup();

//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order');
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');

if ($useEavModel)
{
	$installer->run("
	
	ALTER TABLE  {$this->getTable('sales_order')}
	ADD  `is_valid` INT NOT NULL DEFAULT  '0';
	
	");
}
else 
{
	$installer->run("

	ALTER TABLE  {$this->getTable('sales_flat_order')}
	ADD  `is_valid` INT NOT NULL DEFAULT  '0' COMMENT  'set if order is valid';
	
	");
	
}

$installer->endSetup();
