<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('purchase_supplier'), 'sup_state','varchar(255) default NULL');

$installer->endSetup();


