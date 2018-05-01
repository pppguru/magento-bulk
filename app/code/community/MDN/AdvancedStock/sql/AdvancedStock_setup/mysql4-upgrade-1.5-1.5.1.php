<?php
 
$installer = $this;
 
$installer->startSetup();

//set default values for ordered qty in cataloginventory_stock_item table
$installer->run("

update 
	{$this->getTable('cataloginventory_stock_item')},
	(
		select 
			tbl_product.entity_id as entity_id,
			tbl_ordered.value as ordered_qty
		from
			{$this->getTable('catalog_product_entity')} tbl_product,
			{$this->getTable('catalog_product_entity_int')} tbl_ordered,
			{$this->getTable('eav_attribute')}
		where
			tbl_product.entity_id = tbl_ordered.entity_id
			and tbl_ordered.store_id = 0
			and tbl_ordered.attribute_id = {$this->getTable('eav_attribute')}.attribute_id
			and {$this->getTable('eav_attribute')}.attribute_code = 'ordered_qty'
	) tbl_ordered
set 
	stock_ordered_qty = tbl_ordered.ordered_qty
where
	product_id = tbl_ordered.entity_id;
	
");
 
//set default values for reserved qty in cataloginventory_stock_item table
$installer->run("

update 
	{$this->getTable('cataloginventory_stock_item')},
	(
		select 
			tbl_product.entity_id as entity_id,
			tbl_reserved.value as reserved_qty
		from
			{$this->getTable('catalog_product_entity')} tbl_product,
			{$this->getTable('catalog_product_entity_int')} tbl_reserved,
			{$this->getTable('eav_attribute')}
		where
			tbl_product.entity_id = tbl_reserved.entity_id
			and tbl_reserved.store_id = 0
			and tbl_reserved.attribute_id = {$this->getTable('eav_attribute')}.attribute_id
			and {$this->getTable('eav_attribute')}.attribute_code = 'reserved_qty'
	) tbl_reserved
set 
	stock_reserved_qty = tbl_reserved.reserved_qty
where
	product_id = tbl_reserved.entity_id;
	
");


$installer->endSetup();
