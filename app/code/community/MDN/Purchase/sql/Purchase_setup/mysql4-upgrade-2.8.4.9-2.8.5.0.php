<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS  {$this->getTable('purchase_supplier_invoice')} (
`psi_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`psi_po_id` INT NOT NULL ,
`psi_invoice_id`  VARCHAR(255) NULL ,
`psi_date` DATE NULL ,
`psi_due_date` DATE NULL ,
`psi_payment_date` DATE NULL ,
`psi_amount` DECIMAL(12,4) DEFAULT 0 NOT NULL,
`psi_status` VARCHAR(50) NOT NULL ,
`psi_notes` TEXT NULL ,
`psi_attachment` LONGBLOB  NULL,
`psi_attachment_name` VARCHAR(255) NULL,
`psi_attachment_type` VARCHAR(100) NULL,
INDEX (  `psi_id` ,  `psi_po_id` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

