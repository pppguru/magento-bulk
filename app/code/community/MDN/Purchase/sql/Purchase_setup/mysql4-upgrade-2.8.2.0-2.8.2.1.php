<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('purchase_supplier')} ADD sup_payment_delay INT;

");

$installer->endSetup();


