<?php
 
$installer = $this;
 
$installer->startSetup();

//if magento version is 1.4.1.1 or more, add payment_validated column to sales_order_grid table
if (mage::helper('AdvancedStock/MagentoVersionCompatibility')->useSalesOrderGrid())
{

	//create column
	$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_grid'), 'payment_validated', 'tinyint default 0');

}

$installer->endSetup();
