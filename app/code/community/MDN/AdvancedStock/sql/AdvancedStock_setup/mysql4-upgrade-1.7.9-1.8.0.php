<?php

$installer = $this;

$installer->startSetup();

$prefix = Mage::getConfig()->getTablePrefix();

//find attribute ids
$waitingForDeliveryAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'waiting_for_delivery_qty')->getId();
$manualSupplyNeedsAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'manual_supply_need_qty')->getId();
$nameAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'name')->getId();

//create query
$sql = "
    create or replace view
        ".$prefix."erp_view_supplyneeds_base
    AS
    select
        tbl_stock_item.product_id,
        tbl_stock_item.stock_id,
        tbl_product.sku,
        tbl_name.value as name,
        tbl_manufacturer.value manufacturer_id,
        tbl_stock_item.qty as stock,
        if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0) as available_qty,
        if (use_config_notify_stock_qty = 1, CONVERT(tbl_config_notify.value, signed), notify_stock_qty) as warning_stock_level,
        if (use_config_ideal_stock_level = 1, CONVERT(tbl_config_ideal.value, signed), ideal_stock_level) as ideal_stock_level,
        if (tbl_waiting_for_delivery_qty.value, tbl_waiting_for_delivery_qty.value, 0) as waiting_for_delivery_qty,
        if (tbl_manual_supply_needs.value, tbl_manual_supply_needs.value, 0) as manual_supply_needs_qty,
        if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty_for_valid_orders, 0, tbl_stock_item.stock_ordered_qty_for_valid_orders - tbl_stock_item.qty) as qty_needed_for_valid_orders,
        if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, 0, tbl_stock_item.stock_ordered_qty - tbl_stock_item.qty) as qty_needed_for_orders,
        if (if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0) < if (use_config_notify_stock_qty = 1, CONVERT(tbl_config_notify.value, signed), notify_stock_qty), if (use_config_ideal_stock_level = 1, CONVERT(tbl_config_ideal.value, signed), ideal_stock_level) - if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0), 0) as qty_needed_for_ideal_stock,
        if (tbl_manual_supply_needs.value > if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0),tbl_manual_supply_needs.value  - if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0) , 0) as qty_needed_for_manual_supply_needs

    from
        ".$prefix."cataloginventory_stock_item tbl_stock_item
        JOIN ".$prefix."cataloginventory_stock tbl_stock on (tbl_stock_item.stock_id = tbl_stock.stock_id)
        JOIN ".$prefix."catalog_product_entity tbl_product on (tbl_stock_item.product_id = tbl_product.entity_id)
        JOIN ".$prefix."core_config_data tbl_config_notify on (1=1)
        JOIN ".$prefix."core_config_data tbl_config_ideal on (1=1)
        JOIN ".$prefix."core_config_data tbl_config_manufacturer on (1=1)
        LEFT JOIN ".$prefix."catalog_product_entity_int tbl_waiting_for_delivery_qty on (tbl_waiting_for_delivery_qty.entity_id = tbl_stock_item.product_id and tbl_waiting_for_delivery_qty.attribute_id = {waiting_for_delivery_attribute_id})
        LEFT JOIN ".$prefix."catalog_product_entity_int tbl_manual_supply_needs on (tbl_manual_supply_needs.entity_id = tbl_stock_item.product_id and tbl_manual_supply_needs.attribute_id = {manual_supply_needs_attribute_id})
        LEFT JOIN ".$prefix."catalog_product_entity_int tbl_manufacturer on (tbl_manufacturer.entity_id = tbl_stock_item.product_id and tbl_manufacturer.attribute_id = tbl_config_manufacturer.value)
        LEFT JOIN ".$prefix."catalog_product_entity_varchar tbl_name on (tbl_name.entity_id = tbl_stock_item.product_id and tbl_name.attribute_id = {name_attribute_id} and tbl_name.store_id = 0)

    where
        tbl_stock.stock_disable_supply_needs <> 1
        and ((tbl_stock_item.use_config_manage_stock = 1) or (tbl_stock_item.manage_stock = 1))
        and tbl_product.exclude_from_supply_needs = 0
        and tbl_config_notify.path = 'cataloginventory/item_options/notify_stock_qty'
        and tbl_config_ideal.path = 'advancedstock/prefered_stock_level/ideal_stock_default_value'
        and tbl_config_manufacturer.path = 'purchase/supplyneeds/manufacturer_attribute'

";

//replace attribute values
$sql = str_replace('{waiting_for_delivery_attribute_id}', $waitingForDeliveryAttributeId, $sql);
$sql = str_replace('{manual_supply_needs_attribute_id}', $manualSupplyNeedsAttributeId, $sql);
$sql = str_replace('{name_attribute_id}', $nameAttributeId, $sql);


//run query
$installer->run($sql);

//run query
$installer->run("

    create or replace view
        ".$prefix."erp_view_supplyneeds_global
    AS
    select
        product_id,
        manufacturer_id,
        sku,
        name,
        waiting_for_delivery_qty,
        if (
            SUM(qty_needed_for_valid_orders) > 0 and (SUM(qty_needed_for_valid_orders) > waiting_for_delivery_qty ),
            '1_valid_orders',
            if (
                SUM(qty_needed_for_orders) > 0 and (SUM(qty_needed_for_orders) - SUM(qty_needed_for_valid_orders) > (waiting_for_delivery_qty - SUM(qty_needed_for_valid_orders)) ),
                '2_orders',
                if (
                    SUM(qty_needed_for_ideal_stock) > 0 and (SUM(qty_needed_for_orders) + SUM(qty_needed_for_ideal_stock) > waiting_for_delivery_qty),
                    '3_prefered_stock_level',
                    if (
                        SUM(qty_needed_for_manual_supply_needs) > 0,
                        '4_manual_supply_need',
                        '5_pending_delivery'
                       )
                    )
                )
            )
            as status,
        if (SUM(qty_needed_for_valid_orders) - waiting_for_delivery_qty > 0, SUM(qty_needed_for_valid_orders) - waiting_for_delivery_qty, 0) as qty_min,
        if (SUM(qty_needed_for_orders + qty_needed_for_ideal_stock + qty_needed_for_manual_supply_needs) - waiting_for_delivery_qty > 0, SUM(qty_needed_for_orders + qty_needed_for_ideal_stock + qty_needed_for_manual_supply_needs) - waiting_for_delivery_qty, 0) as qty_max
    from
        ".$prefix."erp_view_supplyneeds_base
    where
        (qty_needed_for_valid_orders > 0 )
        OR
        (qty_needed_for_orders > 0)
        OR
        (qty_needed_for_ideal_stock > 0)
        OR
        (qty_needed_for_manual_supply_needs > 0)
    group by
        product_id,
        manufacturer_id,
        sku,
        name


");

//run query
$installer->run("

    create or replace view
        ".$prefix."erp_view_supplyneeds_warehouse
    AS
    select
        stock_id,
        product_id,
        manufacturer_id,
        sku,
        name,
        waiting_for_delivery_qty,
        if (
            (qty_needed_for_valid_orders) > 0 and ((qty_needed_for_valid_orders) > waiting_for_delivery_qty ),
            '1_valid_orders',
            if (
                (qty_needed_for_orders) > 0 and ((qty_needed_for_orders) > (waiting_for_delivery_qty - (qty_needed_for_valid_orders)) ),
                '2_orders',
                if (
                    (qty_needed_for_ideal_stock) > 0 and ((qty_needed_for_ideal_stock) > (waiting_for_delivery_qty - ((qty_needed_for_valid_orders)) + (qty_needed_for_orders)) ),
                    '3_prefered_stock_level',
                    if (
                        (qty_needed_for_manual_supply_needs) > 0,
                        '4_manual_supply_need',
                        '5_pending_delivery'
                       )
                    )
                )
            )
            as status,
        qty_needed_for_valid_orders as qty_min,
        (qty_needed_for_orders + qty_needed_for_ideal_stock + qty_needed_for_manual_supply_needs) as qty_max
    from
        ".$prefix."erp_view_supplyneeds_base
    where
        (qty_needed_for_valid_orders > 0 )
        OR
        (qty_needed_for_orders > 0)
        OR
        (qty_needed_for_ideal_stock > 0)
        OR
        (qty_needed_for_manual_supply_needs > 0)


");

//run query
$installer->run("
    delete
    from ".$prefix."backgroundtask
    WHERE  `bt_description` LIKE  '%supply%';
");

//apply 0 as default value for disable supply in warehouse
$installer->run("
    ALTER TABLE  ".$prefix."cataloginventory_stock
    CHANGE  `stock_disable_supply_needs`  
    `stock_disable_supply_needs` TINYINT( 4 ) NULL DEFAULT  '0';
    
    update ".$prefix."cataloginventory_stock
    set stock_disable_supply_needs = 0
    where stock_disable_supply_needs is null;
    
    ");

$installer->endSetup();


