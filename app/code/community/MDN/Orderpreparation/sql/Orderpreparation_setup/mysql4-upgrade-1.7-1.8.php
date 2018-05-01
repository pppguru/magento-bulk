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

CREATE TABLE IF NOT EXISTS `{$this->getTable('order_preparation_carrier_template')}` (
  `ct_id` int(11) NOT NULL auto_increment,
  `ct_name` varchar(50) NOT NULL,
  `ct_shipping_method` varchar(50) NOT NULL,
  `ct_export_filename` varchar(100) NOT NULL,
  `ct_export_format` varchar(15) NOT NULL,
  `ct_export_file_separator` varchar(15) NOT NULL,
  `ct_export_file_delimiter` varchar(15) NOT NULL,
  `ct_export_add_header` tinyint(4) NOT NULL,
  `ct_import_format` varchar(15) NOT NULL,
  `ct_import_file_separator` varchar(15) NOT NULL,
  `ct_import_file_delimiter` varchar(15) NOT NULL,
  `ct_import_skip_first_record` tinyint(4) NOT NULL,
  `ct_export_custom_header` text,
  `ct_export_remove_accent` tinyint(4) NOT NULL default 0,
  `ct_export_convert_utf8` tinyint(4) NOT NULL default 0,
  `ct_export_line_end` varchar(2) NOT NULL default 'rn',
  `ct_export_witness_filename` varchar(255) default NULL,
  PRIMARY KEY  (`ct_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;


CREATE TABLE IF NOT EXISTS `{$this->getTable('order_preparation_carrier_template_fields')}` (
  `ctf_id` int(11) NOT NULL auto_increment,
  `ctf_name` varchar(50) NOT NULL,
  `ctf_size` int(11) NOT NULL,
  `ctf_content` varchar(100) NOT NULL,
  `ctf_format` varchar(50) NOT NULL,
  `ctf_format_argument` varchar(50) NOT NULL,
  `ctf_template_id` int(11) NOT NULL,
  `ctf_type` varchar(10) NOT NULL,
  `ctf_position` int(11) NOT NULL,
  PRIMARY KEY  (`ctf_id`),
  KEY `ctf_template_eid` (`ctf_template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

");
	
$installer->endSetup();
