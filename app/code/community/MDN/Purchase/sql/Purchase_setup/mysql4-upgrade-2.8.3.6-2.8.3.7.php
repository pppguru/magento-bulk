<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('purchase_order'), 'po_is_locked','TINYINT default 0');

$installer->getConnection()->addColumn($installer->getTable('purchase_order'), 'po_last_notify_text','TEXT default NULL');


if (!(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
$installer->run("


DELETE FROM {$this->getTable('dataflow_profile')} WHERE name = 'Export Supply Needs';
INSERT INTO {$this->getTable('dataflow_profile')} (`name`, `created_at`, `updated_at`, `actions_xml`, `gui_data`, `direction`, `entity_type`, `store_id`, `data_transfer`) VALUES
('Export Supply Needs', '2013-07-05 12:00:00', '2013-07-05 12:00:00', '<action type=\"Purchase/convert_adapter_supplyneeds\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_supplyneeds.csv]]></var>
<var name=\"fields\"><![CDATA[product_id;stock_id;sku;name;manufacturer_id;waiting_for_delivery_qty;status;qty_min;qty_max]]></var></action>', NULL, NULL, NULL, 0, NULL);


");

}

$installer->endSetup();


