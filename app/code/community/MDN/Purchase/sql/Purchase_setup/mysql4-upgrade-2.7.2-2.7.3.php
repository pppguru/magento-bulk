<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_order_product')} ADD INDEX (  `pop_product_id` )

");
 
$installer->endSetup();
