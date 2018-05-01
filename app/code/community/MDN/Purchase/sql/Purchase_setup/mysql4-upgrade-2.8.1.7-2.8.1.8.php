<?php
 
$installer = $this;
 
$installer->startSetup();

//add columns to manage ordered qty and reserved qty
$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')} ADD sup_discount_level decimal(10,4) NOT NULL ;

");
 
$installer->endSetup();


