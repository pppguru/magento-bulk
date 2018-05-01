<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cataloginventory_stock'), 'stock_disable_supply_needs', 'tinyint');

$installer->run("

	ALTER TABLE  {$this->getTable('erp_sales_history')} ADD  `sh_updated_at` DATE NOT NULL
	
");

$installer->endSetup();
