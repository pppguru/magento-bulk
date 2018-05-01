SELECT pa_product_id, count(*) 
FROM  `product_availability` 
group by pa_product_id
having count(*) > 1;

SELECT ppb_barcode, count(*) 
FROM  `purchase_product_barcodes` 
group by ppb_barcode
having count(*) > 1;

SELECT pop_product_id, pop_order_num, count(*) 
FROM  `purchase_order_product` 
group by pop_product_id, pop_order_num
having count(*) > 1;

SELECT sn_product_id, count(*) 
FROM  `supply_needs` 
group by sn_product_id
having count(*) > 1;

SELECT pps_product_id, pps_supplier_num, count(*) 
FROM  `purchase_product_supplier` 
group by pps_product_id, pps_supplier_num
having count(*) > 1;

