<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("


ALTER TABLE  `{$this->getTable('purchase_order')}` 
ADD  `po_target_warehouse` INT NULL;

");
 
$installer->endSetup();


