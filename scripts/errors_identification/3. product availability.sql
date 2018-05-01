-- doublons
select
    pa_product_id,
    count(*)
from
    product_availability
group by 
    pa_product_id
having
    count(*) > 1;

-- identification des product availability non cohérents
select 
    pa_product_id,
    pa_available_qty,
    pa_status,
    pa_is_saleable
from
    product_availability
where
    (pa_available_qty > 0 and (pa_status <> 0 or pa_is_saleable = 0))
    or
    (pa_available_qty = 0 and (pa_status = 0 or pa_is_saleable = 1))
;

-- pa_available_qty ne correspond pas à la réalité
select 
    pa_product_id,
    pa_available_qty,
    sum(qty - stock_ordered_qty)
from
    product_availability,
    cataloginventory_stock_item,
    catalog_product_entity
where
    pa_product_id = product_id
    and product_id = entity_id
    and type_id = 'simple'

group by 
    pa_product_id,
    pa_available_qty
having
    (
        pa_available_qty <> sum(qty - stock_ordered_qty)
        or sum(stock_ordered_qty) > sum(qty)
    )
    and sum(qty) >= 0
;
