    
DROP TABLE IF EXISTS cataloginventory_stock_assignment;
    
DROP TABLE IF EXISTS backgroundtask;
DROP TABLE IF EXISTS backgroundtask_group;

DROP TABLE IF EXISTS order_to_prepare;
DROP TABLE IF EXISTS order_to_prepare_item;
DROP TABLE IF EXISTS order_to_prepare_pending;
DROP TABLE IF EXISTS order_preparation_carrier_template;
DROP TABLE IF EXISTS order_preparation_carrier_template_fields;

DROP TABLE IF EXISTS organizer_task;
DROP TABLE IF EXISTS organizer_task_category;
DROP TABLE IF EXISTS organizer_task_origin;

DROP TABLE IF EXISTS product_availability;

DROP TABLE IF EXISTS stock_errors;
DROP TABLE IF EXISTS stock_movement;
DROP TABLE IF EXISTS supply_needs;

DROP TABLE IF EXISTS purchase_contact;
DROP TABLE IF EXISTS purchase_manufacturer;
DROP TABLE IF EXISTS purchase_manufacturer_supplier;
DROP TABLE IF EXISTS purchase_order;
DROP TABLE IF EXISTS purchase_order_product;
DROP TABLE IF EXISTS purchase_packaging;
DROP TABLE IF EXISTS purchase_product_barcodes;
DROP TABLE IF EXISTS purchase_product_manufacturer;
DROP TABLE IF EXISTS purchase_product_serial;
DROP TABLE IF EXISTS purchase_product_supplier;
DROP TABLE IF EXISTS purchase_sales_order_planning;
DROP TABLE IF EXISTS purchase_shipping_delay;
DROP TABLE IF EXISTS purchase_supplier;
DROP TABLE IF EXISTS purchase_tva_rates;

DROP TABLE IF EXISTS erp_sales_history;
DROP TABLE IF EXISTS erp_stock_transfer_products;
DROP TABLE IF EXISTS erp_stock_transfer;
DROP TABLE IF EXISTS erp_sales_flat_order_item;

DROP VIEW IF EXISTS erp_view_supplyneeds_base;
DROP VIEW IF EXISTS erp_view_supplyneeds_global;
DROP VIEW IF EXISTS erp_view_supplyneeds_warehouse;


ALTER TABLE sales_flat_order DROP stocks_updated;
ALTER TABLE sales_flat_order DROP anounced_date;
ALTER TABLE sales_flat_order DROP anounced_date_max;
ALTER TABLE sales_flat_order DROP is_valid;

ALTER TABLE sales_order DROP stocks_updated;
ALTER TABLE sales_order DROP anounced_date;
ALTER TABLE sales_order DROP anounced_date_max;
ALTER TABLE sales_order DROP is_valid;

ALTER TABLE  cataloginventory_stock DROP stock_description;
ALTER TABLE  cataloginventory_stock DROP stock_address;
ALTER TABLE  cataloginventory_stock DROP stock_code;

ALTER TABLE  cataloginventory_stock_item DROP stock_reserved_qty;
ALTER TABLE  cataloginventory_stock_item DROP stock_ordered_qty_for_valid_orders;
ALTER TABLE  cataloginventory_stock_item DROP shelf_location;
ALTER TABLE  cataloginventory_stock_item DROP is_favorite_warehouse;
ALTER TABLE  cataloginventory_stock_item DROP ideal_stock_level;
ALTER TABLE  cataloginventory_stock_item DROP use_config_ideal_stock_level;

