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
																						
//Creation de la table de cache pour les besoins d'approvisionnement
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('supply_needs')};
CREATE TABLE IF NOT EXISTS {$this->getTable('supply_needs')} (
  sn_id bigint(20) NOT NULL auto_increment,
  sn_product_sku varchar(50) NOT NULL,
  sn_product_id int(11) NOT NULL,
  sn_manufacturer_id  int(11) NULL,
  sn_manufacturer_name varchar(255),
  sn_product_name varchar(255),
  sn_status varchar(20),
  sn_needed_qty int(11) NOT NULL,
  sn_details text,
  sn_deadline date,
  sn_suppliers_ids varchar(25),
  sn_suppliers_name text,
  sn_is_warning tinyint,
  PRIMARY KEY  (sn_id),
  KEY sn_product_id (sn_product_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

																																											
$installer->endSetup();
