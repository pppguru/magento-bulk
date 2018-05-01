<?php
 
$installer = $this;
 
$installer->startSetup();

//init default assignments for website #1, stock #1
$installer->run("
	insert into {$this->getTable('cataloginventory_stock_assignment')} (csa_website_id, csa_assignment, csa_stock_id) values (1, 'sales', 1);
	insert into {$this->getTable('cataloginventory_stock_assignment')} (csa_website_id, csa_assignment, csa_stock_id) values (1, 'order_preparation', 1);
	insert into {$this->getTable('cataloginventory_stock_assignment')} (csa_website_id, csa_assignment, csa_stock_id) values (1, 'product_return', 1);
");


$installer->endSetup();
