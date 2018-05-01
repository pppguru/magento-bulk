update cataloginventory_stock_item
set
	qty = 0,
	shelf_location = '',
	stock_ordered_qty_for_valid_orders = 0,
	stock_reserved_qty = 0,
	stock_ordered_qty = 0;

TRUNCATE `backgroundtask`;
TRUNCATE `backgroundtask_group`;
TRUNCATE `order_to_prepare`;
TRUNCATE `order_to_prepare_item`;
TRUNCATE `order_to_prepare_pending`;
TRUNCATE `organizer_task`;
TRUNCATE `organizer_task_category`;
TRUNCATE `organizer_task_origin`;

TRUNCATE `product_availability`;
TRUNCATE `purchase_contact`;
TRUNCATE `purchase_manufacturer`;
TRUNCATE `purchase_manufacturer_supplier`;
TRUNCATE `purchase_order`;
TRUNCATE `purchase_order_product`;
TRUNCATE `purchase_packaging`;
TRUNCATE `purchase_product_barcodes`;
TRUNCATE `purchase_product_manufacturer`;
TRUNCATE `purchase_product_serial`;
TRUNCATE `purchase_product_supplier`;
TRUNCATE `purchase_sales_order_planning`;
TRUNCATE `purchase_shipping_delay`;
TRUNCATE `purchase_supplier`;
TRUNCATE `purchase_tva_rates`;
TRUNCATE `stock_errors`;
TRUNCATE `stock_movement`;
TRUNCATE `supply_needs`;

TRUNCATE erp_sales_history;
TRUNCATE erp_stock_transfer_products;
TRUNCATE erp_stock_transfer;

TRUNCATE erp_sales_flat_order_item ;