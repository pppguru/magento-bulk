select
    *
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
    and state in ('new', 'pending', 'processing','holded')
    and type_id = 'simple'
    and cataloginventory_stock_item.product_id = 114920
;
