<?php

$installer=$this;
$installer->startSetup();

																						
//Create tables
$installer->run("
	

ALTER TABLE  {$this->getTable('order_to_prepare')} ADD  `sent_to_shipworks` TINYINT NOT NULL DEFAULT  '0';

");

																																											
$installer->endSetup();

