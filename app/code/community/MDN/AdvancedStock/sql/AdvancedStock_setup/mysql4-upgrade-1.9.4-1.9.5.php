<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('stock_movement'), 'sm_user', 'varchar(50) NULL');

$installer->endSetup();
