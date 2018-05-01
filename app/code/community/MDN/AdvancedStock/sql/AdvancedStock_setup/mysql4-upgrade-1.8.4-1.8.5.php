<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_stock_picture')}  (
`eisp_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`eisp_inventory_id` INT(11) NOT NULL,
`eisp_product_id` INT(11) NOT NULL,
`eisp_stock` INT(11) NOT NULL,
INDEX (eisp_inventory_id, eisp_product_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  {$this->getTable('erp_inventory')}
ADD `ei_stock_picture_date` DATE NULL;

ALTER TABLE  {$this->getTable('erp_inventory_stock_picture')}
ADD `eisp_shelf_location` VARCHAR(25);

");
 
$installer->endSetup();
