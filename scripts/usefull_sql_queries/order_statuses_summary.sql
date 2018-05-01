-- Run this query to check if there are inconsistent state / status associations
SELECT state, status, count(*) FROM `sales_flat_order` group by state, status;