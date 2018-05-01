<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'wishlist/wishlist'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('Purchase/Order_History'))
    ->addColumn('poh_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'ID')
    ->addColumn('poh_po_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'PO Id')
    ->addColumn('poh_created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created at')
    ->addColumn('poh_message', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Message')
    ->addIndex($installer->getIdxName('Purchase/Order_History', 'poh_id'), 'poh_id')
    ->addIndex($installer->getIdxName('Purchase/Order_History', 'poh_po_id'), 'poh_po_id')
    ->setComment('Purchase order history table');
$installer->getConnection()->createTable($table);


$installer->endSetup();
