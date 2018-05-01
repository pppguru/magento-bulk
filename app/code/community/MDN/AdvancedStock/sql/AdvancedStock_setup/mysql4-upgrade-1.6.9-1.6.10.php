<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cataloginventory_stock'), 'stock_disable_supply_needs', 'tinyint');

$installer->run("
    ALTER TABLE  {$this->getTable('stock_movement')} CHANGE  `sm_date`  `sm_date` DATETIME NOT NULL
");

$installer->endSetup();
