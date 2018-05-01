<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
 
CREATE TABLE IF NOT EXISTS {$this->getTable('cataloginventory_stock_assignment')} (
 `csa_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `csa_website_id` INT NOT NULL ,
 `csa_assignment` VARCHAR( 25 ) NOT NULL ,
 `csa_stock_id` INT NOT NULL,
INDEX (  `csa_website_id` ,  `csa_assignment`, `csa_stock_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");
 
$installer->endSetup();
