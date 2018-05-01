<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_product_supplier')}
ADD pps_product_name varchar(255)
;

");

$installer->endSetup();


