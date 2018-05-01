<?php

$installer = $this;

$installer->startSetup();

$installer->run("

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';
select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id and attribute_set_name='Default';

insert ignore into {$this->getTable('eav_attribute_group')}
    set attribute_set_id 	= @attribute_set_id,
	attribute_group_name	= 'Shipping',
	sort_order		= 99;

select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='Shipping' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'freight_class',
	backend_type	= 'int',
	frontend_input	= 'text',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Freight Class';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='freight_class';

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
	attribute_set_id 	= @attribute_set_id,
	attribute_group_id	= @attribute_group_id,
	attribute_id		= @attribute_id;


insert ignore into {$this->getTable('catalog_eav_attribute')}
	set attribute_id 	= @attribute_id,
	is_visible 	= 1,
	used_in_product_listing	= 1,
    is_filterable_in_search	= 1;

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'freight_quote_id',
    	backend_type	= 'varchar',
    	frontend_input	= 'text';

select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id;
select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='freight_quote_id';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'liftgate_required',
    	backend_type	= 'int',
    	frontend_input	= 'boolean';

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD liftgate_required tinyint(1);

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'notify_required',
    	backend_type	= 'int',
    	frontend_input	= 'boolean';

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD notify_required tinyint(1);

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'shipto_type',
    	backend_type	= 'varchar',
    	frontend_input	= 'text';

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD shipto_type varchar(20);

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='liftgate_required';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='shipto_type';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;
    	
select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='notify_required';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD freight_quote_id varchar(30);

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD freight_quote_id varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD freight_quote_id varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD shipto_type varchar(20);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD liftgate_required tinyint(1);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD notify_required int(1);

");

$installer->endSetup();


