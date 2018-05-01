<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS  `{$this->getTable('purchase_packaging')}` (
 `pp_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `pp_product_id` INT NOT NULL ,
 `pp_supplier_id` INT NOT NULL ,
 `pp_name` VARCHAR( 50 ) NOT NULL ,
 `pp_qty` INT NOT NULL ,
INDEX (  `pp_product_id` ,  `pp_supplier_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE  `{$this->getTable('purchase_order_product')}`
ADD  `pop_packaging_id` INT NULL ,
ADD  `pop_packaging_value` INT NOT NULL DEFAULT  '1';

ALTER TABLE  `{$this->getTable('purchase_order_product')}`
ADD  `pop_packaging_name` VARCHAR(50) NULL;

ALTER TABLE  `{$this->getTable('purchase_packaging')}` 
ADD  `pp_is_default` TINYINT NOT NULL DEFAULT  '0';

ALTER TABLE  `{$this->getTable('purchase_packaging')}` 
ADD  `pp_is_default_sales` TINYINT NOT NULL DEFAULT  '0';

");
 
$installer->endSetup();


