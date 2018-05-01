<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("

ALTER TABLE  {$this->getTable('erp_sales_flat_order_item')}
ADD `serials` VARCHAR(255);

");
 
$installer->endSetup();
