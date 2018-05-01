<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_order_product')} ADD pop_discount decimal(10,4) NOT NULL ;

");
 
$installer->endSetup();


