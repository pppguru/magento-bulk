<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('purchase_order'), 'po_tracking', 'VARCHAR(255) NULL');

$installer->endSetup();

