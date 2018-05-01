<?php

$installer = $this;

$installer->startSetup();

$installer->run("
        
ALTER TABLE  {$this->getTable('purchase_product_barcodes')} ADD UNIQUE  `barcode_unique` (  `ppb_barcode` );

ALTER TABLE  {$this->getTable('purchase_order_product')} ADD UNIQUE  `unique_product_in_po` (  `pop_order_num` ,  `pop_product_id` );

ALTER TABLE  {$this->getTable('supply_needs')} DROP INDEX  `sn_product_id` ,
ADD UNIQUE  `sn_product_id` (  `sn_product_id` );

ALTER TABLE  {$this->getTable('purchase_product_supplier')} DROP INDEX  `pps_product_id` ,
ADD UNIQUE  `pps_product_id` (  `pps_product_id` ,  `pps_supplier_num` );

");

$installer->endSetup();


