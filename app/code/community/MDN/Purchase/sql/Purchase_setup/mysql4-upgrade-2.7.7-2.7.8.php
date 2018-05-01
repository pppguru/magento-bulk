<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')} ADD  `sup_locale` VARCHAR( 50 ) NOT NULL ;

");
 
$installer->endSetup();


