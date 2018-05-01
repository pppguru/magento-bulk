<?php

$installer=$this;
$installer->startSetup();

//Create erp_sales_flat_order_item table
$installer->run("

CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_sales_flat_order_item')} (
`esfoi_item_id` INT NOT NULL ,
`preparation_warehouse` INT NOT NULL ,
`reserved_qty` INT NOT NULL DEFAULT  '0',
`comments` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY (  `esfoi_item_id` ) ,
INDEX (  `preparation_warehouse` )
) ENGINE = INNODB DEFAULT CHARSET=utf8; 

");

//copy datas from sales_flat_order_item
$installer->run("
    insert into {$this->getTable('erp_sales_flat_order_item')}
    (esfoi_item_id, preparation_warehouse, reserved_qty, comments)
    SELECT item_id, preparation_warehouse, reserved_qty, comments
    from {$this->getTable('sales_flat_order_item')};
");

//remove columns in sales_flat_order_item
$installer->run("
      ALTER TABLE {$this->getTable('sales_flat_order_item')}
      DROP `comments`,
      DROP `reserved_qty`,
      DROP `preparation_warehouse`;
");

$installer->endSetup();
