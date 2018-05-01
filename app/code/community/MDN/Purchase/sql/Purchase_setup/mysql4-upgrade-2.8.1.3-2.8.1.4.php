<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')} ADD  `sup_shipping_delay` TINYINT;

");
 
$installer->endSetup();


