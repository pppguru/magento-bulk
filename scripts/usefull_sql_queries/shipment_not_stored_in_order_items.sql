-- run this query to find order for which there are shipment but shipped qty is incorrect in sales order item
-- Issues identification :
select
    tbl_order.entity_id,
    tbl_order.increment_id,
    tbl_order_item.item_id,
    tbl_order_item.qty_shipped,
    SUM(tbl_shipment_item.qty)
from
    sales_flat_order tbl_order,
    sales_flat_order_item tbl_order_item,
    sales_flat_shipment_item tbl_shipment_item
where
    tbl_order.entity_id = tbl_order_item.order_id
    and tbl_shipment_item.order_item_id = tbl_order_item.item_id
group by
    tbl_order.entity_id,
    tbl_order.increment_id,
    tbl_order_item.item_id,
    tbl_order_item.qty_shipped
having
    SUM(tbl_shipment_item.qty) <> tbl_order_item.qty_shipped;

-- Apply fix :

select

    concat("update sales_flat_order_item set qty_shipped = ", SUM(tbl_shipment_item.qty), " where item_id = ", tbl_order_item.item_id, ";") as query
from
    sales_flat_order tbl_order,
    sales_flat_order_item tbl_order_item,
    sales_flat_shipment_item tbl_shipment_item
where
    tbl_order.entity_id = tbl_order_item.order_id
    and tbl_shipment_item.order_item_id = tbl_order_item.item_id
group by
    tbl_order.entity_id,
    tbl_order.increment_id,
    tbl_order_item.item_id,
    tbl_order_item.qty_shipped
having
    SUM(tbl_shipment_item.qty) <> tbl_order_item.qty_shipped;
