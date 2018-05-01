<?php

$installer = $this;

$installer->startSetup();

$rmaMessages = $installer->getConnection()->newTable($installer->getTable('rma_messages'))
    ->addColumn('rmam_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true
    ), 'ID')
    ->addColumn('rmam_rma_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'RMA ID')
    ->addColumn('rmam_author', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        'nullable' => false
    ), 'AUTHOR')
    ->addColumn('rmam_message', Varien_Db_Ddl_Table::TYPE_TEXT, 2000, array(
        'nullable' => false
    ), 'MESSAGE')
    ->addColumn('rmam_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'unsigned' => true,
        'nullable' => false
    ), 'DATE')
    ->addIndex($installer->getIdxName('rma_messages', array('rmam_rma_id')), array('rmam_rma_id'));

$installer->getConnection()->createTable($rmaMessages);

$installer->getConnection()->addConstraint(
    'FK_rma_messages_REF_rma',
    $installer->getTable('rma_messages'),
    'rmam_rma_id',
    $installer->getTable('rma'),
    'rma_id',
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE,
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE,
    false
);

$installer->endSetup();