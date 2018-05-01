<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_product_supplier')}
ADD pps_last_unit_price_supplier_currency DECIMAL(10,2)
;

UPDATE {$this->getTable('purchase_product_supplier')}
set pps_last_unit_price_supplier_currency = pps_last_unit_price;

");

$installer->endSetup();


