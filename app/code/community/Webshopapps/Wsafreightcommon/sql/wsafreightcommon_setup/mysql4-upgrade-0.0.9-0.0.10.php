<?php

 /**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_freight
 * User         Joshua Stewart
 * Date         24/03/2014
 * Time         17:04
 * @copyright   Copyright (c) 2014 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2014, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

$installer = $this;

$installer->startSetup();

if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
    $newDefinition = array( 'type'    	=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
                            'comment' 	=> 'Ship To Type',
                            'length'    => '1',
                            'nullable' 	=> 'false',
                            'default'   => 0);

    $installer->getConnection()->changeColumn(
        $installer->getTable('sales/quote_address'),
        'shipto_type',
        'shipto_type',
        $newDefinition

    );

    $installer->getConnection()->changeColumn(
        $installer->getTable('sales/order'),
        'shipto_type',
        'shipto_type',
        $newDefinition
    );
} else {
    $installer->run("
      ALTER TABLE  {$this->getTable('sales/quote_address')} CHANGE `shipto_type` `shipto_type` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Ship To Type';
      ALTER TABLE  {$this->getTable('sales/order')} CHANGE `shipto_type` `shipto_type` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Ship To Type';
      ");
}

$installer->endSetup();