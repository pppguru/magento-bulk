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
('Export Product / Supplier associations', '2009-10-13 06:26:39', '2009-11-12 14:21:16', '<action type=\"Purchase/convert_adapter_ExportProductSupplier\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_product_supplier.csv]]></var>    </action>', '', NULL, '', 0, NULL);
");
}
	
$installer->endSetup();
