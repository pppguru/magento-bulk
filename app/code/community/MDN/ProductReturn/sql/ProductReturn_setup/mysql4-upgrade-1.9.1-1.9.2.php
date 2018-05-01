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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();


$installer->run("

DROP TABLE IF EXISTS {$this->getTable('rma_supplier_return')};

CREATE TABLE {$this->getTable('rma_supplier_return')} (
  `rsr_id` int(11) NOT NULL auto_increment,
  `rsr_supplier_id` int(11) NOT NULL,
  `rsr_created_at` datetime NOT NULL,
  `rsr_updated_at` datetime default NULL,
  `rsr_status` varchar(50) NOT NULL default 'new',
  `rsr_reference` varchar(50) default NULL,
  `rsr_supplier_reference` varchar(50) default NULL,
  `rsr_comments` text default NULL,
  `rsr_status_set_to_inquiry_at` DATETIME default NULL,
  `rsr_status_set_to_sent_at` DATETIME default NULL,
  PRIMARY KEY  (`rsr_id`),
  KEY `rsr_supplier_id` (`rsr_supplier_id`),
  KEY `rsr_status` (`rsr_status`)
);

CREATE TABLE {$this->getTable('rma_supplier_return_history')} (
  `rsrh_id` int(11) NOT NULL auto_increment,
  `rsrh_rsr_id` int(11) NOT NULL,
  `rsrh_date` datetime NOT NULL,
  `rsrh_description` text NOT NULL,
  PRIMARY KEY  (`rsrh_id`),
  KEY `rsrh_rsr_id` (`rsrh_rsr_id`)
);

CREATE TABLE {$this->getTable('rma_supplier_return_product')} (
  `rsrp_id` int(11) NOT NULL auto_increment,
  `rsrp_rsr_id` int(11) DEFAULT NULL,
  `rsrp_product_id` int(11) NOT NULL,
  `rsrp_product_sku` varchar(64) NOT NULL,
  `rsrp_product_name` varchar(255) NOT NULL,
  `rsrp_rp_id` int(11) NOT NULL,
  `rsrp_sup_id` int(11) DEFAULT NULL,
  `rsrp_pop_id` int(11) DEFAULT NULL,
  `rsrp_serial` varchar(50) DEFAULT NULL,
  `rsrp_purchase_price` DECIMAL(8,4) DEFAULT NULL,
  `rsrp_comments` text NOT NULL,
  `rsrp_creation_date` datetime NOT NULL,
  `rsrp_status` varchar(50) NOT NULL,
  `rsrp_do_not_value_it` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`rsrp_id`),
  KEY `rsrp_rsr_id` (`rsrp_rsr_id`),
  KEY `rsrp_product_id` (`rsrp_product_id`),
  KEY `rsrp_rp_id` (`rsrp_rp_id`),
  KEY `rsrp_pop_id` (`rsrp_pop_id`),
  KEY `rsrp_status` (`rsrp_status`)
);

");


$installer->endSetup();