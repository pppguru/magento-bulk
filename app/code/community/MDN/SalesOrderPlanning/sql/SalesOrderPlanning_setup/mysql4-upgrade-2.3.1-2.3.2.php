<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('product_availability')} ADD  pa_available_dropship_qty int(11) NOT NULL DEFAULT 0;
ALTER TABLE  {$this->getTable('product_availability')} ADD  pa_stock_default_supplier mediumint(9) NOT NULL DEFAULT 0;
ALTER TABLE  {$this->getTable('product_availability')} ADD  pa_supplier_default_delay tinyint(2) NOT NULL;
ALTER TABLE  {$this->getTable('product_availability')} ADD  pa_stock_other_supplier mediumint(9) NOT NULL DEFAULT 0;
ALTER TABLE  {$this->getTable('product_availability')} ADD  pa_supplier_other_delay tinyint(2) NOT NULL;
ALTER TABLE  {$this->getTable('product_availability')} ADD  pa_subproduct_ids VARCHAR( 255 ) NOT NULL;

");

$installer->endSetup();


