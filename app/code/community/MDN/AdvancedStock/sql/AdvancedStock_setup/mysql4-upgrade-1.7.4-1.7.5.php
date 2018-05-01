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

ALTER TABLE  {$this->getTable('cataloginventory_stock_item')}
ADD  `ideal_stock_level` INT NULL ,
ADD  `use_config_ideal_stock_level` TINYINT NOT NULL DEFAULT  '1';

update {$this->getTable('cataloginventory_stock_item')}
set ideal_stock_level = notify_stock_qty, use_config_ideal_stock_level = use_config_notify_stock_qty;

");

$installer->endSetup();
