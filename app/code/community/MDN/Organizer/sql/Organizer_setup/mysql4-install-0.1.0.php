<?php
/**
 * Magento Fianet Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Gr
 * @package    Gr_Fianet
 * @author     Nicolas Fabre <nicolas.fabre@groupereflect.net>
 * @copyright  Copyright (c) 2008 Nicolas Fabre
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
								

$installer->run("
	
	CREATE TABLE IF NOT EXISTS {$this->getTable('organizer_task')} (
	`ot_id` INT NOT NULL AUTO_INCREMENT ,
	`ot_created_at` DATE NULL ,
	`ot_author_user` VARCHAR( 25 ) NOT NULL ,
	`ot_target_user` VARCHAR( 25 ) NOT NULL ,
	`ot_caption` VARCHAR( 255 ) NOT NULL ,
	`ot_description` TEXT NOT NULL ,
	`ot_deadline` DATE NULL ,
	`ot_notify_date` DATE NULL ,
	`ot_priority` TINYINT NOT NULL ,
	`ot_finished` TINYINT NOT NULL DEFAULT 0,
	`ot_read` TINYINT NOT NULL DEFAULT 0,
	`ot_origin` INT NOT NULL,
	`ot_category` INT NOT NULL,
	ot_entity_type varchar(50) null,
	ot_entity_id int null,
	ot_entity_description varchar(255) null,
	PRIMARY KEY ( `ot_id` ) 
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS {$this->getTable('organizer_task_category')} (
		`otc_id` INT NOT NULL AUTO_INCREMENT ,
		otc_name varchar(255) not null,
		PRIMARY KEY ( `otc_id` ) 
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	

	CREATE TABLE IF NOT EXISTS {$this->getTable('organizer_task_origin')} (
		`oto_id` INT NOT NULL AUTO_INCREMENT ,
		oto_name varchar(255) not null,
		PRIMARY KEY ( `oto_id` ) 
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	
	ALTER TABLE {$this->getTable('organizer_task')}
	CHANGE `ot_author_user` `ot_author_user` INT NOT NULL ,
	CHANGE `ot_target_user` `ot_target_user` INT NOT NULL;
	
	ALTER TABLE {$this->getTable('organizer_task')} ADD `ot_notification_read` TINYINT NOT NULL DEFAULT 0;
	
");
																															
$installer->endSetup();

