<?php
 
$installer = $this;
 
$installer->startSetup();

//add columns to manage ordered qty and reserved qty
$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')} ADD  `sup_code` VARCHAR( 50 ) NOT NULL ;

");
 
$installer->endSetup();


