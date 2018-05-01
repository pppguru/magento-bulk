<?php

$installer = $this;

$installer->startSetup();

$installer->run("

    CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_stockmovement_adjustment')}  (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `sm_id` INT NOT NULL,
    `log` TEXT,
    INDEX (  `sm_id` )
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
