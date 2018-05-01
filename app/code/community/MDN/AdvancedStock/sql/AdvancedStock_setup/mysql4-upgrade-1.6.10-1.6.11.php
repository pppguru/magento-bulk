<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

if (!(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
$installer->run("

INSERT INTO {$this->getTable('dataflow_profile')} (`name`, `created_at`, `updated_at`, `actions_xml`, `gui_data`, `direction`, `entity_type`, `store_id`, `data_transfer`) VALUES
('Import product / warehouse stock levels', '2011-01-17 13:09:32', '2011-01-17 13:09:32', '<!-- file import -->\r\n<action type=\"dataflow/convert_adapter_io\" method=\"load\">\r\n    <var name=\"type\">file</var>\r\n    <var name=\"path\">var/import</var>\r\n    <var name=\"filename\"><![CDATA[product_warehouse_stock_level.csv]]></var>\r\n    <var name=\"format\"><![CDATA[csv]]></var>\r\n</action>\r\n\r\n<!-- file parser -->\r\n<action type=\"dataflow/convert_parser_csv\" method=\"parse\">\r\n    <var name=\"delimiter\"><![CDATA[;]]></var>\r\n    <var name=\"fieldnames\">true</var>\r\n    <var name=\"decimal_separator\"><![CDATA[.]]></var>\r\n    <var name=\"method\">parse</var>\r\n    <var name=\"adapter\">AdvancedStock/Convert_Adapter_ProductWarehouseStock</var>\r\n    <var name=\"method\">saveRow</var>\r\n</action>\r\n', NULL, NULL, '', 0, NULL),
('Import product barcodes', '2011-01-18 10:27:41', '2011-01-18 10:27:41', '<!-- file import -->\r\n<action type=\"dataflow/convert_adapter_io\" method=\"load\">\r\n    <var name=\"type\">file</var>\r\n    <var name=\"path\">var/import</var>\r\n    <var name=\"filename\"><![CDATA[product_barcodes.csv]]></var>\r\n    <var name=\"format\"><![CDATA[csv]]></var>\r\n</action>\r\n\r\n<!-- file parser -->\r\n<action type=\"dataflow/convert_parser_csv\" method=\"parse\">\r\n    <var name=\"delimiter\"><![CDATA[;]]></var>\r\n    <var name=\"fieldnames\">true</var>\r\n    <var name=\"decimal_separator\"><![CDATA[.]]></var>\r\n    <var name=\"method\">parse</var>\r\n    <var name=\"adapter\">AdvancedStock/Convert_Adapter_ImportBarcode</var>\r\n    <var name=\"method\">saveRow</var>\r\n</action>\r\n', NULL, NULL, '', 0, NULL);

");
}

$installer->endSetup();
