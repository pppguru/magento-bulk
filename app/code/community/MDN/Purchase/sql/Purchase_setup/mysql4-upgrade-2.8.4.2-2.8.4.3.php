<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('purchase_order'), 'po_default_product_discount','decimal(4,2) default 0');

$installer->endSetup();


