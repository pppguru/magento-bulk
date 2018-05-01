<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cataloginventory_stock'), 'stock_disable_supply_needs', 'tinyint');

$installer->run("

	CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_sales_history')} (
	`sh_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`sh_product_id` INT NOT NULL ,
	`sh_period_1` INT NOT NULL ,
	`sh_period_2` INT NOT NULL ,
	`sh_period_3` INT NOT NULL
	) ENGINE = INNODB DEFAULT CHARSET=utf8;
	
");

$installer->endSetup();
