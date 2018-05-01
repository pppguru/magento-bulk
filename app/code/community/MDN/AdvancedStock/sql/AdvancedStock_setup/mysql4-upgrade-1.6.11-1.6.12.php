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

//disable stock management for both configurable & bundle products
$installer->run("

update 
    {$this->getTable('cataloginventory_stock_item')},
    {$this->getTable('catalog_product_entity')}
set 
    manage_stock = 0,
    use_config_manage_stock = 0
where 
    entity_id = product_id
    and type_id in ('configurable', 'bundle');

");

$installer->endSetup();
