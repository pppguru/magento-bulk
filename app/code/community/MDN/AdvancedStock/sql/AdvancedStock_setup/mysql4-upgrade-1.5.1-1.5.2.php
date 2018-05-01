<?php
 
$installer = $this;
 
$installer->startSetup();

//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order');
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');

if ($useEavModel)
	$installer->addAttribute('order', 'stocks_updated', array('type'=>'static'));

$installer->endSetup();
