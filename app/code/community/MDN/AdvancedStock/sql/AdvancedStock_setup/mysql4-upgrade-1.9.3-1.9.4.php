<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cataloginventory_stock_item'), 'erp_exclude_automatic_warning_stock_level_update', 'tinyint NOT NULL default 0');

$installer->endSetup();
