<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("
	
	CREATE TABLE IF NOT EXISTS {$this->getTable('stock_errors')}
	(
	`se_id` INT NOT NULL ,
	`se_product_id` INT NOT NULL ,
	`se_stock_id` INT NOT NULL ,
	`se_stored_qty` INT NOT NULL ,
	`se_expected_qty` INT NOT NULL ,
	`se_stored_reserved_qty` INT NOT NULL ,
	`se_expected_reserved_qty` INT NOT NULL ,
	`se_stored_ordered_qty` INT NOT NULL ,
	`se_expected_ordered_qty` INT NOT NULL ,
	`se_comments` VARCHAR( 255 ) NOT NULL ,
	PRIMARY KEY (  `se_id` ) ,
	INDEX (  `se_product_id` ,  `se_stock_id` )
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

	ALTER TABLE  {$this->getTable('stock_errors')}  CHANGE  `se_id`  `se_id` INT( 11 ) NOT NULL AUTO_INCREMENT
	
");


$installer->endSetup();
