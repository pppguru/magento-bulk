<?php

/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    WebShopApps
 * @package     WebShopApps Wsafreightcommon
 * @copyright   Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("

	set @attribute_id = (select attribute_id from {$this->getTable('eav_attribute')} where attribute_code = 'freight_class');
	update {$this->getTable('eav_attribute')} set backend_type = 'text' where attribute_id = @attribute_id

	");

$attributeId = $installer->getConnection()->fetchOne("select attribute_id from {$this->getTable('eav_attribute')} where attribute_code = 'freight_class'");

$entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();

$sql = 'SELECT * FROM '. $this->getTable('catalog_product_entity_int').' WHERE attribute_id = '.$attributeId.' AND entity_type_id = '.$entityTypeId;



$rows = $installer->getConnection()->fetchAll($sql);

foreach ($rows as $row){

    $sql = 'INSERT IGNORE INTO '. $this->getTable('catalog_product_entity_text').' (`entity_type_id`,`attribute_id`,`store_id`,`entity_id`,`value`) VALUES (?,?,?,?,?)';

    $installer->getConnection()->query($sql, array($row['entity_type_id'], $row['attribute_id'], $row['store_id'], $row['entity_id'], $row['value']));

}



$sql = 'DELETE FROM '. $this->getTable('catalog_product_entity_int WHERE attribute_id').' = '.$attributeId.' AND entity_type_id = '.$entityTypeId;

$installer->getConnection()->query($sql, $row['value_id']);

$installer->endSetup();
