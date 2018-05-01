<?php
 
$installer = $this;
 
$installer->startSetup();

//add columns to manage ordered qty and reserved qty
$installer->run("

ALTER TABLE  {$this->getTable('cataloginventory_stock_item')}
ADD  `stock_ordered_qty` INT NOT NULL DEFAULT  '0',
ADD  `stock_reserved_qty` INT NOT NULL DEFAULT  '0';

");
 
$installer->endSetup();
