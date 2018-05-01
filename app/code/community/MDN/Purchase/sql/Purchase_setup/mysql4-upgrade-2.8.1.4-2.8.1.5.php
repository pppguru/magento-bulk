<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')} ADD  `sup_free_carriage_amount` decimal(10,2);
ALTER TABLE  {$this->getTable('purchase_supplier')} ADD  `sup_free_carriage_weight` INT;

");
 
$installer->endSetup();


