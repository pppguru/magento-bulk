<?php
 
$installer = $this;
 
$installer->startSetup();

//add columns to manage ordered qty and reserved qty
$installer->run("

ALTER TABLE  {$this->getTable('supply_needs')}
ADD  `sn_needed_qty_for_valid_orders` INT NOT NULL DEFAULT  '0';

");
 
$installer->endSetup();
