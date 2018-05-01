<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("

CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_inventory')}  (
`ei_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ei_warehouse_id` INT NOT NULL,
`ei_name` VARCHAR( 255 ) NOT NULL ,
`ei_date` DATE NOT NULL ,
`ei_status` VARCHAR( 25 ) NOT NULL ,
`ei_comments` TEXT NOT NULL ,
INDEX (  `ei_status` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_inventory_product')}  (
`eip_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`eip_inventory_id` INT NOT NULL ,
`eip_product_id` INT NOT NULL ,
`eip_shelf_location` INT NOT NULL ,
`eip_qty` INT NOT NULL ,
INDEX (  `eip_inventory_id` ,  `eip_product_id` ,  `eip_shelf_location` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  {$this->getTable('erp_inventory_product')}
CHANGE  `eip_shelf_location`  `eip_shelf_location` VARCHAR( 25 ) NOT NULL;


");
 
$installer->endSetup();
