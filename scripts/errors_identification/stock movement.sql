-- verifie les mouvements de stock en doublon
select
    sm_product_id,
    sm_description,
    count(*)
from 
    stock_movement
where
    sm_description like '%Shipment for order%'
group by
    sm_product_id,
    sm_description
having
    count(*) > 1