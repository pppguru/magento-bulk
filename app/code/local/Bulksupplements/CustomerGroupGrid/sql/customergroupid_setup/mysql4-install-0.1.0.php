<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
        ->addColumn($installer->getTable('sales/order_grid'), 'customer_group_id', array(
            'TYPE' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'NULLABLE'  => false,
            'DEFAULT'   => '0',
            'COMMENT' => 'Customer Group'
        ));
$installer->endSetup();
