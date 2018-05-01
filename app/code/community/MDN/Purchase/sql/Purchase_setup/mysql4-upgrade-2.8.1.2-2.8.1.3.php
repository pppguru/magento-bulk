<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("
 
ALTER TABLE  {$this->getTable('purchase_product_supplier')} ADD  `pps_is_default_supplier` TINYINT NOT NULL DEFAULT  '0';
ALTER TABLE  {$this->getTable('purchase_product_supplier')} ADD  `pps_can_dropship` TINYINT NOT NULL DEFAULT  '0';

");

$installer->endSetup();


