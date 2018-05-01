<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_order_product')} ADD pop_delivery_date DATE NULL;

");

$installer->endSetup();


