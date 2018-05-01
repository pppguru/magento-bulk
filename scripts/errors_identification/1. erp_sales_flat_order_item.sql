-- identify order items without erp_sales_flat_order_item
select
    item_id
from 
    sales_flat_order_item
where
    item_id not in (select esfoi_item_id from erp_sales_flat_order_item)
;

-- Fix it (associate to warehouse #1)
insert into erp_sales_flat_order_item
    (esfoi_item_id, preparation_warehouse)
select
    item_id,
    1
from 
    sales_flat_order_item
where
    item_id not in (select esfoi_item_id from erp_sales_flat_order_item)
;