<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
 
ALTER TABLE  {$this->getTable('cataloginventory_stock')} 
ADD  `stock_available_for_sales` tinyint NULL,
ADD  `stock_own_warehouse` tinyint NULL;

");
 
$installer->endSetup();
