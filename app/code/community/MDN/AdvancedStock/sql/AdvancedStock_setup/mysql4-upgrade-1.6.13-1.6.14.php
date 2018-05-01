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

//Add column to affect a preparation warehouse to a order item
$installer->run("

ALTER TABLE  {$this->getTable('sales_flat_order_item')} 
 ADD  `preparation_warehouse` INT NOT NULL ,
ADD INDEX (  `preparation_warehouse` );

");

//Add column to set main warehouse for a product
$installer->run("

ALTER TABLE  {$this->getTable('cataloginventory_stock_item')}
ADD  `is_favorite_warehouse` TINYINT NOT NULL DEFAULT  '0'

");

$installer->endSetup();
