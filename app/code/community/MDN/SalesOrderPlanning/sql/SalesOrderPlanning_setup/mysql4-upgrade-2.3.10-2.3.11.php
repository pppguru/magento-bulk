<?php

$installer = $this;

$installer->startSetup();

//Fix the fact that pa_supply_delay was limited to 127, which is really to low if supplier deliver once a year for example
$installer->run("ALTER TABLE  {$this->getTable('product_availability')}  CHANGE `pa_supply_delay` `pa_supply_delay` INT NOT NULL");

$installer->endSetup();


