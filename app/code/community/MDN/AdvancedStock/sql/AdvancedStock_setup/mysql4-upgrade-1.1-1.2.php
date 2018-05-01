<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
 
ALTER TABLE  {$this->getTable('cataloginventory_stock')} 
ADD  `stock_code` VARCHAR(50) NULL;

");
 
$installer->endSetup();
