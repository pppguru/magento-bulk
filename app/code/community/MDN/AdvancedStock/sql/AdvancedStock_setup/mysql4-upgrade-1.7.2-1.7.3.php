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

CREATE TABLE IF NOT EXISTS {$this->getTable('erp_stock_transfer')} (
`st_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`st_created_at` DATETIME NOT NULL ,
`st_name` VARCHAR( 255 ) NOT NULL ,
`st_status` VARCHAR( 50 ) NOT NULL ,
`st_comments` TEXT NOT NULL ,
`st_source_warehouse` INT NOT NULL ,
`st_target_warehouse` INT NOT NULL ,
`st_transfered_at` DATETIME NOT NULL ,
INDEX (  `st_status` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
