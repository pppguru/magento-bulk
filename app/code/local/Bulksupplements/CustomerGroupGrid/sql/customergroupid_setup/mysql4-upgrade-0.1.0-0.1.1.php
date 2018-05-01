<?php
$installer = $this;
$installer->startSetup();
// Add key to table for this field,
// It will improve the speed of searching & sorting by the field
$installer->getConnection()->addKey(
    $installer->getTable('sales/order_grid'),
    'customer_group_id',
    'customer_group_id'
);

// Now you need to fullfill existing rows with data from address table
$select = $installer->getConnection()->select();
$select->join(
    array('order' => $installer->getTable('sales/order')),
    $installer->getConnection()->quoteInto(
        'order.entity_id = order_grid.entity_id'
    ),
    array('customer_group_id' => 'customer_group_id')
);
$installer->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $installer->getTable('sales/order_grid'))
    )
);
$installer->endSetup();
