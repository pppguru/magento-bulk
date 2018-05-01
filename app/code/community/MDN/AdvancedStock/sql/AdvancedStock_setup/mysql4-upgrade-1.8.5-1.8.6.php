<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("

create or replace VIEW {$this->getTable('erp_inventory_missed_location')}
AS
select 
        eisp_shelf_location, 
        eisp_inventory_id,
	SUM(eisp_stock) product_count
from
	{$this->getTable('erp_inventory_stock_picture')}
where
	eisp_shelf_location not in 
	(
		select 
			eip_shelf_location
		from
			{$this->getTable('erp_inventory_product')}
		where
			eisp_inventory_id = eip_inventory_id
			
			
	)
group by
        eisp_shelf_location, 
        eisp_inventory_id

");
 
$installer->endSetup();
