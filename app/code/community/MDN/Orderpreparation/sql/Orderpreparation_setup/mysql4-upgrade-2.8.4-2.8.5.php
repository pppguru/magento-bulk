<?php


$installer=$this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('order_preparation_carrier_template_fields')} CHANGE  `ctf_format_argument`  `ctf_format_argument` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

");

$installer->endSetup();
