<?php

 /**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Freightcommon
 * User         Joshua Stewart
 * Date         23/02/15
 * Time         10:38
 * @copyright   Copyright (c) 2015 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2015, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

//FREIGHT-199

$installer = $this;

$installer->startSetup();

if (Mage::helper('wsalogger')->getNewVersion() > 10) {
    $methodCode = array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'WebShopApps FreightCommon',
        'nullable' => 'true',
    );

    $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'original_shipping_method', $methodCode);
    $installer->getConnection()->addColumn($installer->getTable('sales/order'),'original_shipping_method',$methodCode);

} else {
    $quoteAddressTable = $installer->getTable('sales/quote_address');
    $orderAddressTable = $installer->getTable('sales/order');

    if(!$installer->getConnection()->tableColumnExists($quoteAddressTable, 'original_shipping_method')){
        $installer->run("
            ALTER IGNORE TABLE {$quoteAddressTable} ADD original_shipping_method TEXT NULL COMMENT 'WebShopApps FreightCommon';
        ");
    }

    if(!$installer->getConnection()->tableColumnExists($orderAddressTable, 'original_shipping_method')){
        $installer->run("
            ALTER IGNORE TABLE {$orderAddressTable} ADD original_shipping_method TEXT NULL COMMENT 'WebShopApps FreightCommon';
        ");
    }
}