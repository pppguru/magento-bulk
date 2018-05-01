<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('purchase_order_product')} CHANGE  `pop_supplier_ref`  `pop_supplier_ref` VARCHAR( 255 )");
$installer->run("ALTER TABLE {$this->getTable('purchase_product_supplier')} CHANGE  `pps_reference`  `pps_reference` VARCHAR( 255 )");

$installer->endSetup();


