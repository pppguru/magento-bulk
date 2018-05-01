<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')}
ADD sup_currency VARCHAR(3),
ADD sup_tax_rate INT
;

");

$installer->endSetup();


