<?php

$installer=$this;

$installer->startSetup();
								

$installer->run("
	
	CREATE TABLE IF NOT EXISTS {$this->getTable('smart_report_report')} (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`created_at` DATE NULL ,
	`type` VARCHAR( 25 ) NOT NULL ,
	`name` VARCHAR( 25 ) NOT NULL ,
	`filters` TEXT NULL ,
	`display_in_dashboard` TINYINT NOT NULL default 0,
	`dashboard_position` TINYINT NOT NULL default 0,
	PRIMARY KEY ( `id` )
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


");
																															
$installer->endSetup();

