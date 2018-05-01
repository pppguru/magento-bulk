<?php
 
$installer = $this;
 
$installer->startSetup();

//init default assignments for website #0 (admin), stock #1
$installer->run("
	insert into {$this->getTable('cataloginventory_stock_assignment')} (csa_website_id, csa_assignment, csa_stock_id) values (0, 'sales', 1);
");


$installer->endSetup();
