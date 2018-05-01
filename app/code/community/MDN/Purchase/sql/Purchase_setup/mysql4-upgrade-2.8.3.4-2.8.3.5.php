<?php

$installer = $this;

$installer->startSetup();

$installer->run("
        
ALTER TABLE  {$this->getTable('purchase_order_product')} CHANGE  `pop_price_ht`  `pop_price_ht` DECIMAL( 12, 4 ) NOT NULL;
ALTER TABLE  {$this->getTable('purchase_order_product')} CHANGE  `pop_price_ht_base`  `pop_price_ht_base` DECIMAL( 12, 4 ) NOT NULL;
ALTER TABLE  {$this->getTable('purchase_order_product')} CHANGE  `pop_eco_tax`  `pop_eco_tax` DECIMAL( 12, 4 ) NOT NULL;
ALTER TABLE  {$this->getTable('purchase_order_product')} CHANGE  `pop_eco_tax_base`  `pop_eco_tax_base` DECIMAL( 12, 4 ) NOT NULL;

");


$installer->endSetup();
