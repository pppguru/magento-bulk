-- Check that stored quantity match to stock movement sums
select
    product_id,
    (SUM(if(sm_source_stock = 1, -sm_qty, sm_qty)) - qty) as diff
from
    cataloginventory_stock_item,
    stock_movement,
    catalog_product_entity
where
    stock_id = 1
    and product_id = sm_product_id
    and entity_id = product_id
    and type_id = 'simple'
    and (sm_source_stock = 1 or sm_target_stock = 1)
group by
    product_id, 
    qty
having 
    qty <> SUM(if(sm_source_stock = 1, -sm_qty, sm_qty))
;

-- check that ordered quantity are ok
select
    cataloginventory_stock_item.product_id,
    stock_ordered_qty,
    SUM(if(qty_ordered - qty_shipped - qty_canceled < 0, 0, qty_ordered - qty_shipped - qty_canceled))
from
    cataloginventory_stock_item,
    sales_flat_order,
    sales_flat_order_item,
    catalog_product_entity
where
    sales_flat_order.entity_id = sales_flat_order_item.order_id
    and sales_flat_order_item.product_id = cataloginventory_stock_item.product_id
    and cataloginventory_stock_item.stock_id = 1
    and catalog_product_entity.entity_id = cataloginventory_stock_item.product_id
    and state not in ('canceled', 'complete', 'closed')
    and type_id = 'simple'
group by
    product_id,
    stock_ordered_qty
having
    SUM(if(qty_ordered - qty_shipped - qty_canceled - qty_refunded < 0, 0, qty_ordered - qty_shipped - qty_canceled - qty_refunded)) <> stock_ordered_qty
;

-- check that is_in_stock matches to product availability
select
    distinct product_id
from
    cataloginventory_stock_item,
    product_availability
where
    pa_product_id = product_id
    and pa_is_saleable = 0
    and is_in_stock = 1
    and stock_id = 1
;

-- check that is_in_stock is synchronized
select
    distinct product_id,
    min(is_in_stock),
    max(is_in_stock),
    pa_available_qty
from
    cataloginventory_stock_item,
    product_availability
where
    pa_product_id = product_id
group by 
    product_id
having 
    max(is_in_stock) <> min(is_in_stock);
