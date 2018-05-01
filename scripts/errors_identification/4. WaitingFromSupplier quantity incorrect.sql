-- Identify waiting for delivery quantity that doesnt match to pending purchase orders
select
    tbl_product.entity_id as product_id,
    tbl_wfd.value as qty_stored,
    SUM(IF((pop_qty - pop_supplied_qty > 0), (pop_qty - pop_supplied_qty), 0)) as qty_calculated
from
    mage_catalog_product_entity tbl_product
    inner join mage_catalog_product_entity_int tbl_wfd on (tbl_product.entity_id = tbl_wfd.entity_id and store_id = 0)
    inner join mage_eav_attribute tbl_attribute on (tbl_attribute.attribute_id = tbl_wfd.attribute_id)
    inner join mage_purchase_order_product tbl_pop on (tbl_pop.pop_product_id = tbl_product.entity_id)
    inner join mage_purchase_order tbl_po on (tbl_pop.pop_order_num = tbl_po.po_num)
where  
    tbl_attribute.attribute_code = 'waiting_for_delivery_qty'
    and tbl_po.po_status = 'waiting_for_delivery'
group by 
    tbl_product.entity_id,
    tbl_wfd.value
having
    SUM(IF((pop_qty - pop_supplied_qty > 0), (pop_qty - pop_supplied_qty), 0)) <> tbl_wfd.value
;
