<?php

$installer = $this;

$installer->startSetup();
$installer->updateAttribute('catalog_product', 'default_supply_delay', 'default_value', null);
$installer->endSetup();
