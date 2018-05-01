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
('Export Purchase Order', '2009-10-13 06:26:39', '2009-11-12 14:21:16', '<action type=\"Purchase/convert_adapter_orders\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_orders.csv]]></var>    <var name=\"fields\"><![CDATA[po_num;po_sup_num;sup_name;po_date;po_order_id;po_supply_date;po_carrier;po_payment_type;po_currency;po_invoice_date;po_invoice_ref;po_paid;po_sent;po_currency_change_rate;po_shipping_cost;po_shipping_cost_base;po_zoll_cost;po_zoll_cost_base;po_finished;po_tax_rate;po_mediabox_num;po_comments;po_payment_date;po_supplier_order_ref;po_status;po_delivery_percent;po_supplier_notification_date]]></var></action>', '', NULL, '', 0, NULL),
('Export Purchase Order Products', '2009-11-12 06:33:31', '2009-11-12 14:26:15', '<action type=\"Purchase/convert_adapter_orders_products\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_orders_products.csv]]></var>    <var name=\"fields\"><![CDATA[pop_num;pop_order_num;pop_product_id;sku;pop_product_name;pop_qty;pop_supplied_qty;pop_price_ht;pop_price_ht_base;pop_supplier_ref;pop_eco_tax;pop_eco_tax_base;pop_extended_costs;pop_extended_costs_base;pop_tax_rate]]></var></action>', '', NULL, '', 0, NULL),
('Export Suppliers', '2009-11-12 06:40:51', '2009-11-12 06:41:29', '<action type=\"Purchase/convert_adapter_suppliers\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_suppliers.csv]]></var>    <var name=\"fields\"><![CDATA[sup_id;sup_name;sup_address1;sup_address2;sup_zipcode;sup_country;sup_city;sup_tel;sup_fax;sup_contact;sup_mail;sup_website;sup_sale_online;sup_account_login;sup_account_password;sup_order_mini;sup_supply_delay;sup_supply_delay_max;sup_carrier;sup_comments;sup_rma_tel;sup_rma_mail;sup_rma_comments;sup_mediabox_id]]></var></action>', '', NULL, '', 0, NULL),
('Export Contact', '2009-11-12 06:43:30', '2009-11-12 06:45:02', '<action type=\"Purchase/convert_adapter_contacts\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_contacts.csv]]></var>    <var name=\"fields\"><![CDATA[pc_num;pc_lastname;pc_firstname;pc_function;pc_phone;pc_fax;pc_mobile;pc_email;pc_comments;pc_type;pc_entity_id;pc_country]]></var></action>', '', NULL, '', 0, NULL),
('Export Supply Needs', '2009-11-12 06:49:32', '2009-11-12 08:11:48', '<action type=\"Purchase/convert_adapter_supplyneeds\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_supplyneeds.csv]]></var>    <var name=\"fields\"><![CDATA[sn_id;sn_product_sku;sn_product_id;sn_manufacturer_id;sn_manufacturer_name;sn_product_name;sn_status;sn_needed_qty;sn_details;sn_deadline;sn_suppliers_ids;sn_suppliers_name;sn_is_warning;sn_purchase_deadline;sn_is_critical;sn_priority]]></var></action>', '', NULL, '', 0, NULL),
('Export Products (purchase view)', '2009-11-12 06:54:04', '2009-11-12 06:54:04', '<action type=\"Purchase/convert_adapter_products\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_products.csv]]></var>    <var name=\"fields\"><![CDATA[sku;name;cost;price;margin;stock_qty;real_notify_stock_qty;ordered_qty;qty_needed]]></var></action>', NULL, NULL, '', 0, NULL),
('Export Products Stock Movements', '2009-11-12 06:56:59', '2009-11-12 07:11:19', '<action type=\"AdvancedStock/convert_adapter_stockmovements\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_stockmovements.csv]]></var>    <var name=\"fields\"><![CDATA[sku;sm_id;sm_product_id;sm_qty;sm_coef;sm_description;sm_type;sm_date;sm_estimated_date;sm_ui;sm_po_num]]></var></action>', '', NULL, '', 0, NULL);

");
}
	
$installer->endSetup();
