<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cataloginventory_stock'), 'stock_disable_supply_needs', 'tinyint');

try {

  $installer->run("ALTER TABLE  {$this->getTable('erp_sales_history')} ADD UNIQUE (`sh_product_id`);");

}catch(Exception $ex){
  //ignore, because if there was allready soem double entries if can crash
}


try {

  //remove non unique Index created on table creation
  $installer->run("ALTER TABLE  {$this->getTable('cataloginventory_stock_assignment')} DROP INDEX  `csa_website_id`;");

  //add a non uniqiue Index to avoid
  $installer->run("ALTER TABLE  {$this->getTable('cataloginventory_stock_assignment')} ADD UNIQUE (`csa_website_id`,`csa_assignment`,`csa_stock_id`);");

}catch(Exception $ex){
  //ignore, because if there was allready soem double entries if can crash
}

$installer->endSetup();
