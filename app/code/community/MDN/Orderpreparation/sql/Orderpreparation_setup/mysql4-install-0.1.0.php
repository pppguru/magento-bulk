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

$installer->run("


-- --------------------------------------------------------

--
-- Structure de la table 'order_to_prepare'
--

DROP TABLE IF EXISTS {$this->getTable('order_to_prepare')};
CREATE TABLE IF NOT EXISTS {$this->getTable('order_to_prepare')} (
  order_id int(11) unsigned NOT NULL,
  id int(11) NOT NULL auto_increment,
  shipment_id int(11) NOT NULL default 0,
  invoice_id varchar(25) NOT NULL,
  real_weight decimal(10,2) NOT NULL,
  force_invoice_date date default NULL,
  ship_mode varchar(25) default NULL,
  package_count int(11) NOT NULL default 1,
  PRIMARY KEY  (id),
  UNIQUE KEY order_id_unique (order_id),
  KEY order_id (order_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'order_to_prepare_item'
--

DROP TABLE IF EXISTS {$this->getTable('order_to_prepare_item')};
CREATE TABLE IF NOT EXISTS {$this->getTable('order_to_prepare_item')} (
  order_id int(11) unsigned NOT NULL,
  product_id int(11) NOT NULL,
  qty int(11) NOT NULL,
  id int(11) NOT NULL auto_increment,
  is_config tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY order_id (order_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'order_to_prepare_pending'
--

DROP TABLE IF EXISTS {$this->getTable('order_to_prepare_pending')};
CREATE TABLE IF NOT EXISTS {$this->getTable('order_to_prepare_pending')} (
  opp_num int(11) NOT NULL auto_increment,
  opp_order_id int(11) NOT NULL,
  opp_type varchar(20) NOT NULL,
  PRIMARY KEY  (opp_num),
  KEY opp_order_id (opp_order_id,opp_type)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


");

																															
$installer->endSetup();

