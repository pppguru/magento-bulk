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
$installer = $this;
$installer->startSetup();

$tableName = $this->getTable('erp_sales_history');
$connection = $installer->getConnection();

$connection->truncate($tableName);
$connection->dropIndex($tableName, 'sh_product_id');
$connection->addColumn($tableName, 'sh_stock_id', 'INT NULL');
$newFieldsForIndex = array('sh_product_id','sh_stock_id');
$connection->addIndex($tableName,$installer->getIdxName($tableName, $newFieldsForIndex), $newFieldsForIndex);

$installer->endSetup();
