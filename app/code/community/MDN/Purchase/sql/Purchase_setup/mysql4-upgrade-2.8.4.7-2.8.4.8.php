<?php

$installer = $this;

$installer->startSetup();

//add a po_updated_at field than will be update in each before save
$installer->getConnection()->addColumn($installer->getTable('purchase_order'), 'po_updated_at', 'DATETIME NULL');

//Then basically init it with the 'created date' of each PO
$installer->run("UPDATE {$installer->getTable('purchase_order')} SET `po_updated_at` = `po_date`");

//Then override it using the the last "update date" from purchase order history records
$installer->run("UPDATE `{$installer->getTable('purchase_order')}` `po` INNER JOIN
                (SELECT poh_po_id , MAX(`poh_created_at`) AS max_history
                 FROM `{$installer->getTable('Purchase/Order_History')}`
                 GROUP BY poh_po_id ) `poh`
                 ON `po`.`po_num`=`poh`.`poh_po_id`
                 SET `po`.`po_updated_at` = `poh`.`max_history`;
                ");

$installer->endSetup();

