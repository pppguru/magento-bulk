<?php
 
$installer = $this;
 
$installer->startSetup();

//upgrade stock movement table
$installer->run("

ALTER TABLE  {$this->getTable('stock_movement')} 
ADD  `sm_source_stock` INT NOT NULL ,
ADD  `sm_target_stock` INT NOT NULL ;

update {$this->getTable('stock_movement')} 
set sm_source_stock = 1
where sm_coef < 0;

update {$this->getTable('stock_movement')} 
set sm_target_stock = 1
where sm_coef > 0;

");
 
$installer->endSetup();
