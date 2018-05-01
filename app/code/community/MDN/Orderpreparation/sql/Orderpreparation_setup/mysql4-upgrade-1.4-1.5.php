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
('Export Order To Prepare', '2009-11-12 07:53:42', '2009-11-12 07:54:12', '<action type=\"Orderpreparation/convert_adapter_OrderToPrepare\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_ordertoprepare.csv]]></var>    <var name=\"fields\"><![CDATA[order_id;id;shipment_id;invoice_id;real_weight;force_invoice_date;ship_mode;package_count;details;entity_id;entity_type_id;attribute_set_id;increment_id;parent_id;store_id;created_at;updated_at;is_active;customer_id;tax_amount;shipping_amount;discount_amount;subtotal;grand_total;total_paid;total_refunded;total_qty_ordered;total_canceled;total_invoiced;total_online_refunded;total_offline_refunded;base_tax_amount;base_shipping_amount;base_discount_amount;base_subtotal;base_grand_total;base_total_paid;base_total_refunded;base_total_qty_ordered;base_total_canceled;base_total_invoiced;base_total_online_refunded;base_total_offline_refunded;subtotal_refunded;subtotal_canceled;discount_refunded;discount_canceled;discount_invoiced;tax_refunded;tax_canceled;shipping_refunded;shipping_canceled;base_subtotal_refunded;base_subtotal_canceled;base_discount_refunded;base_discount_canceled;base_discount_invoiced;base_tax_refunded;base_tax_canceled;base_shipping_refunded;base_shipping_canceled;subtotal_invoiced;tax_invoiced;shipping_invoiced;base_subtotal_invoiced;base_tax_invoiced;base_shipping_invoiced;shipping_tax_amount;base_shipping_tax_amount;shipping_tax_refunded;base_shipping_tax_refunded;stocks_updated]]></var></action>', '', NULL, '', 0, NULL),
('Export Order To Prepare Pending', '2009-11-12 07:56:02', '2009-11-12 07:56:02', '<action type=\"Orderpreparation/convert_adapter_OrderToPreparePending\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_ordertopreparepending.csv]]></var>    <var name=\"fields\"><![CDATA[opp_num;opp_order_id;opp_type;opp_shipto_name;opp_remain_to_ship;opp_details;opp_order_increment_id;entity_id;entity_type_id;attribute_set_id;increment_id;parent_id;store_id;created_at;updated_at;is_active;customer_id;tax_amount;shipping_amount;discount_amount;subtotal;grand_total;total_paid;total_refunded;total_qty_ordered;total_canceled;total_invoiced;total_online_refunded;total_offline_refunded;base_tax_amount;base_shipping_amount;base_discount_amount;base_subtotal;base_grand_total;base_total_paid;base_total_refunded;base_total_qty_ordered;base_total_canceled;base_total_invoiced;base_total_online_refunded;base_total_offline_refunded;subtotal_refunded;subtotal_canceled;discount_refunded;discount_canceled;discount_invoiced;tax_refunded;tax_canceled;shipping_refunded;shipping_canceled;base_subtotal_refunded;base_subtotal_canceled;base_discount_refunded;base_discount_canceled;base_discount_invoiced;base_tax_refunded;base_tax_canceled;base_shipping_refunded;base_shipping_canceled;subtotal_invoiced;tax_invoiced;shipping_invoiced;base_subtotal_invoiced;base_tax_invoiced;base_shipping_invoiced;shipping_tax_amount;base_shipping_tax_amount;shipping_tax_refunded;base_shipping_tax_refunded;stocks_updated]]></var></action>', NULL, NULL, '', 0, NULL),
('Export Order To Prepare Items', '2009-11-12 07:57:32', '2009-11-12 07:59:32', '<action type=\"Orderpreparation/convert_adapter_OrderToPrepareItems\" method=\"save\">    <var name=\"type\">file</var>    <var name=\"path\">var/export</var>    <var name=\"filename\"><![CDATA[export_purchase_ordertoprepareitems.csv]]></var>    <var name=\"fields\"><![CDATA[order_id;product_id;qty;id;is_config;order_item_id;item_id;order_id;parent_item_id;quote_item_id;created_at;updated_at;product_id;product_type;product_options;weight;is_virtual;sku;name;description;applied_rule_ids;additional_data;free_shipping;is_qty_decimal;no_discount;qty_backordered;qty_canceled;qty_invoiced;qty_ordered;qty_refunded;qty_shipped;cost;price;base_price;original_price;base_original_price;tax_percent;tax_amount;base_tax_amount;tax_invoiced;base_tax_invoiced;discount_percent;discount_amount;base_discount_amount;discount_invoiced;base_discount_invoiced;amount_refunded;base_amount_refunded;row_total;base_row_total;row_invoiced;base_row_invoiced;row_weight;gift_message_id;gift_message_available;base_tax_before_discount;tax_before_discount;ext_order_item_id;locked_do_invoice;locked_do_ship;weee_tax_applied;weee_tax_applied_amount;weee_tax_applied_row_amount;base_weee_tax_applied_amount;base_weee_tax_applied_row_amount;weee_tax_disposition;weee_tax_row_disposition;base_weee_tax_disposition;base_weee_tax_row_disposition;comments;reserved_qty]]></var></action>', '', NULL, '', 0, NULL);
");
}

$installer->endSetup();
