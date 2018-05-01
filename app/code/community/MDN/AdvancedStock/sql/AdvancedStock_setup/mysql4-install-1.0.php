<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
 
ALTER TABLE  {$this->getTable('cataloginventory_stock')} 
ADD  `stock_description` TEXT NULL ,
ADD  `stock_address` TEXT NULL ;

");
 
$installer->endSetup();
