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

//add status field to purchase_order & init orders status
$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_product_barcodes')} (
  `ppb_product_id` int(20) NOT NULL,
  `ppb_barcode` varchar(20) NOT NULL,
  `ppb_id` int(11) NOT NULL auto_increment,
  `ppb_is_main` tinyint(4) NOT NULL default 0,
  PRIMARY KEY  (`ppb_id`),
  KEY `ppb_product_id` (`ppb_product_id`,`ppb_barcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_product_serial')} (
  `pps_id` int(11) NOT NULL auto_increment,
  `pps_product_id` int(11) default NULL,
  `pps_salesorder_id` int(11) default NULL,
  `pps_purchaseorder_id` int(11) default NULL,
  `pps_rmaitem_id` int(11) default NULL,
  `pps_serial` varchar(25) NOT NULL,
  `pps_sm_id` int(11) default NULL,
  `pps_shipment_item_id` int(11) default NULL,
  PRIMARY KEY  (`pps_id`),
  KEY `pps_product_id` (`pps_product_id`,`pps_salesorder_id`,`pps_purchaseorder_id`,`pps_rmaitem_id`,`pps_serial`),
  KEY `pps_serial` (`pps_serial`),
  KEY `pps_purchaseorder_id` (`pps_purchaseorder_id`),
  KEY `pps_salesorder_id` (`pps_salesorder_id`),
  KEY `pps_shipment_id` (`pps_shipment_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");


$installer->endSetup();
