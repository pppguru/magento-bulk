<?php

$installer = $this;

$installer->startSetup();

$installer->run("

    ALTER TABLE  {$this->getTable('erp_inventory')} CHANGE `ei_stock_picture_date` `ei_stock_picture_date` DATETIME NULL;
    ALTER TABLE  {$this->getTable('erp_inventory')} CHANGE `ei_date` `ei_date` DATETIME NULL;    
    ALTER TABLE  {$this->getTable('erp_inventory')} ADD `ei_stock_take_mode` VARCHAR(20) NOT NULL DEFAULT 'by_location';
    ALTER TABLE  {$this->getTable('erp_inventory')} ADD `ei_stock_take_method_code` VARCHAR(20) NULL;
    ALTER TABLE  {$this->getTable('erp_inventory')} ADD `ei_stock_take_method_value` INT(11) DEFAULT 0;

    CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_inventory_log')}  (
    `eil_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `eil_ei_id` INT NOT NULL,
    `eil_sm_id` INT NOT NULL,
    INDEX (  `eil_ei_id` )
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
