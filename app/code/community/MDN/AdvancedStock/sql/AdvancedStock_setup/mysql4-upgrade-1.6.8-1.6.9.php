<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cataloginventory_stock_item'), 'shelf_location', 'varchar(50)');

$installer->endSetup();
