<?php

$installer = $this;

$installer->startSetup();

$installer->run("

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'freight_class',
	backend_type	= 'text',
	frontend_input	= 'text',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Freight Class';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='freight_class';

insert ignore into {$this->getTable('catalog_eav_attribute')}
	set attribute_id 	= @attribute_id,
	is_visible 	= 1,
	used_in_product_listing	= 0,
    is_filterable_in_search	= 0;

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'must_ship_freight',
	backend_type	= 'int',
	frontend_input	= 'boolean',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Must ship freight';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='must_ship_freight';

insert ignore into {$this->getTable('catalog_eav_attribute')}
	set attribute_id 	= @attribute_id,
	is_visible 	= 1,
	used_in_product_listing	= 0,
    is_filterable_in_search	= 0;

");

$freightCarriers = array ('yrcfreight','wsaupsfreight','estesfreight','echofreight','abffreight','conwayfreight','wsafedexfreight','rlfreight', 'wsaolddominion');

foreach ($freightCarriers as $carrier) {

    $rows = $installer->_conn->fetchAll("select * from {$this->getTable('core_config_data')} where path in ('carriers/$carrier/min_weight','carriers/$carrier/use_accessories', 'carriers/$carrier/max_package_weight','carriers/$carrier/default_freight_class','carriers/$carrier/liftgate_fee','carriers/$carrier/residential_fee','carriers/$carrier/apply_live_liftgate',	'carriers/$carrier/apply_live_business','carriers/$carrier/liftgate_origin','carriers/$carrier/residential_origin','carriers/$carrier/restrict_rates','carriers/$carrier/force_freight','carriers/$carrier/show_carriers','carriers/$carrier/hazardous','carriers/$carrier/default_address','carriers/$carrier/default_liftgate')");
    $search = array('carriers',$carrier);
    $replace = array ('shipping','wsafreightcommon');

    foreach ($rows as $r) {
        $r['path'] = str_replace($search,$replace,$r['path']);
        $installer->_conn->update($this->getTable('core_config_data'), $r, 'config_id='.$r['config_id']);
    }
}

if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
    $lifeGateAttr = array(
        'type'    	=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'comment' 	=> 'Liftgate Required',
        'length'  	=> '1',
        'nullable' 	=> 'false',
        'default'   => 0);

    $notifyReqdAttr = array(
        'type'    	=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'comment' 	=> 'Notify Required',
        'length'  	=> '1',
        'nullable' 	=> 'false',
        'default'   => 0);

    $insideDeliveryAttr = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'comment'   => 'Inside Delivery',
        'length'    => '1',
        'nullable'  => 'false',
        'default'   => 0);

    $shipToAttr =  array(
        'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' 	=> 'Ship To Type',
        'nullable' 	=> 'false',
    );

    $freightQuoteIdAttr =  array(
        'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' 	=> 'Freight Quote Id',
        'nullable' 	=> 'true',
    );

    $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'liftgate_required', $lifeGateAttr );
    $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'notify_required',$notifyReqdAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'inside_delivery',$insideDeliveryAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'shipto_type',$shipToAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'freight_quote_id',$freightQuoteIdAttr);

    $installer->getConnection()->addColumn($installer->getTable('sales/order'),'freight_quote_id',$freightQuoteIdAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/order'),'shipto_type',$shipToAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/order'),'liftgate_required',$lifeGateAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/order'),'notify_required',$notifyReqdAttr);
    $installer->getConnection()->addColumn($installer->getTable('sales/order'),'inside_delivery',$insideDeliveryAttr);

} else {

    $installer->run("
	
	ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD liftgate_required tinyint(1);
	ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD shipto_type varchar(20);
    ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD inside_delivery tinyint(1);
	ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')} ADD notify_required tinyint(1);
	ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD freight_quote_id varchar(30);

	ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD freight_quote_id varchar(30);
	ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD shipto_type varchar(20);
	ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD liftgate_required tinyint(1);
    ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD notify_required int(1);
    ALTER IGNORE TABLE {$this->getTable('sales_flat_order')} ADD inside_delivery int(1);
    
	");
}

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$attributeSetArr = $installer->getConnection()->fetchAll("SELECT attribute_set_id FROM {$this->getTable('eav_attribute_set')} WHERE entity_type_id={$entityTypeId}");

$attributeIdArry = array($installer->getAttributeId($entityTypeId,'freight_class'),$installer->getAttributeId($entityTypeId,'must_ship_freight'));

foreach( $attributeSetArr as $attr)
{

    $attributeSetId= $attr['attribute_set_id'];

    $installer->addAttributeGroup($entityTypeId,$attributeSetId,'Shipping','99');

    $attributeGroupId = $installer->getAttributeGroupId($entityTypeId,$attributeSetId,'Shipping');

    foreach( $attributeIdArry as $attributeId) {
        $installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$attributeId,'99');
    }

};

$installer->endSetup();


