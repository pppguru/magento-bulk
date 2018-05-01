<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

//add table
$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('purchase_sales_order_planning')}`
(
	`psop_id` INT NOT NULL AUTO_INCREMENT ,
	`psop_order_id` INT NOT NULL ,
	`psop_consideration_date` DATE NULL ,
	`psop_consideration_date_force` DATE NULL ,
	`psop_consideration_comments` TEXT NULL ,
	`psop_consideration_date_max` DATE NULL ,
	`psop_fullstock_date` DATE NULL ,
	`psop_fullstock_date_force` DATE NULL ,
	`psop_fullstock_comments` TEXT NULL ,
	`psop_fullstock_date_max` DATE NULL ,
	`psop_shipping_date` DATE NULL ,
	`psop_shipping_date_force` DATE NULL ,
	`psop_shipping_comments` TEXT NULL ,
	`psop_shipping_date_max` DATE NULL ,
	`psop_delivery_date` DATE NULL ,
	`psop_delivery_date_force` DATE NULL ,
	`psop_delivery_comments` TEXT NULL ,
	`psop_delivery_date_max` DATE NULL ,
	psop_anounced_date DATE NULL,
	psop_anounced_date_max DATE NULL,
    
	PRIMARY KEY ( `psop_id` ) ,
	UNIQUE (`psop_order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS  `{$this->getTable('purchase_shipping_delay')}`
(
`psd_id` INT NOT NULL AUTO_INCREMENT ,
`psd_carrier` VARCHAR( 100 ) NOT NULL ,
`psd_default` INT NULL ,
`psd_exceptions` TEXT NOT NULL ,
psd_carrier_title VARCHAR( 255 ) NOT NULL,
PRIMARY KEY (  `psd_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE  `{$this->getTable('supply_needs')}` ADD  `sn_purchase_deadline` DATE NULL ;

");


$installer->endSetup();
