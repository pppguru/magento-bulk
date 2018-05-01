<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_order_product')} ADD  pop_weight decimal(6,2);

");
 
$installer->endSetup();


