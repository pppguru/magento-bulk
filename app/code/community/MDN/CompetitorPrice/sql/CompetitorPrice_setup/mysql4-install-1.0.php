<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('bms_competitor_price_product')} (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `product_id` INT NOT NULL ,
 `channel` VARCHAR( 25 ) NOT NULL ,
 `last_update` TIMESTAMP NULL,
 `details` TEXT NULL,
INDEX (  `product_id`, `channel` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup();
