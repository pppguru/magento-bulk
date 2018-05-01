<?php
    die('Uncomment this code in the file to run uninstall script');

    require_once ('../app/Mage.php');
    session_start();
    Mage::reset();
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    ini_set('display_errors', 1);
    $tablePrefix = Mage::getConfig()->getTablePrefix();

    function executeSql($query)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        try
        {
            $writeConnection->query($query);
            return '<font color="green">OK</font>';
        }
        catch(Exception $ex)
        {
            return '<font color="red">NOK</font>';
        }
    }

    //remove attributes
    echo "<hr><p><b>Remove attributes</b></p>";
    $attributes = array(
        'outofstock_period_to',
        'outofstock_period_from',
        'outofstock_period_enabled',
        'waiting_for_delivery_qty',
        'manual_supply_need_date',
        'manual_supply_need_comments',
        'manual_supply_need_qty',
        'override_subproducts_planning',
        'purchase_tax_rate',
        'exclude_from_supply_needs',
        'supply_date',
        'default_supply_delay',
        'reserved_qty',
        'ordered_qty'
    );
    $installer = new Mage_Eav_Model_Entity_Setup('core_setup');
    $installer->startSetup();
    foreach($attributes as $attribute)
    {
        echo "<br>Remove attribute ".$attribute." ";
        $installer->removeAttribute('catalog_product', $attribute);
    }
    $installer->endSetup();

    //remove tables
    echo "<hr><p><b>Remove tables</b></p>";
    $tables = array('backgroundtask_group',
                  'backgroundtask',
                    'cataloginventory_stock_assignment',
                    'erp_inventory',
                    'erp_inventory_log',
                    'erp_inventory_product',
                    'erp_inventory_stock_picture',
                    'erp_sales_flat_order_item',
                    'erp_sales_history',
                    'erp_stockmovement_adjustment',
                    'erp_stock_transfer',
                    'erp_stock_transfer_products',
                    'order_preparation_carrier_template',
                    'order_preparation_carrier_template_fields',
                    'order_to_prepare',
                    'order_to_prepare_item',
                    'order_to_prepare_pending',
                    'organizer_task',
                    'organizer_task_category',
                    'organizer_task_origin',
                    'product_availability',
                    'purchase_contact',
                    'purchase_manufacturer',
                    'purchase_manufacturer_supplier',
                    'purchase_order',
                    'purchase_order_history',
                    'purchase_order_product',
                    'purchase_packaging',
                    'purchase_product_barcodes',
                    'purchase_product_manufacturer',
                    'purchase_product_serial',
                    'purchase_product_supplier',
                    'purchase_sales_order_planning',
                    'purchase_shipping_delay',
                    'purchase_supplier',
                    'purchase_tva_rates',
                    'stock_errors',
                    'stock_movement',
                    'supply_needs'
                    );
    foreach($tables as $table)
    {
        echo "<br>Remove ".$table." : ".executeSql('drop table IF EXISTS '.$tablePrefix.$table);
    }

    //Remove views
    echo "<hr><p><b>Remove views</b></p>";
    $views = array(
        'erp_inventory_missed_location',
        'erp_view_supplyneeds_base',
        'erp_view_supplyneeds_global',
        'erp_view_supplyneeds_warehouse'
    );
    foreach($views as $view)
    {
        echo "<br>Remove ".$view." : ".executeSql('drop view if exists '.$tablePrefix.$view);
    }

    //remove columns
    echo "<hr><p><b>Remove columns</b></p>";
    $columns = array(
                array('table' => 'cataloginventory_stock', 'column' => 'stock_description'),
                array('table' => 'cataloginventory_stock', 'column' => 'stock_address'),
                array('table' => 'cataloginventory_stock', 'column' => 'stock_code'),
                array('table' => 'cataloginventory_stock', 'column' => 'stock_available_for_sales'),
                array('table' => 'cataloginventory_stock', 'column' => 'stock_own_warehouse'),
                array('table' => 'cataloginventory_stock', 'column' => 'stock_disable_supply_needs'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'stock_ordered_qty'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'stock_reserved_qty'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'stock_ordered_qty_for_valid_orders'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'shelf_location'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'is_favorite_warehouse'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'ideal_stock_level'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'use_config_ideal_stock_level'),
                array('table' => 'cataloginventory_stock_item', 'column' => 'erp_exclude_automatic_warning_stock_level_update'),
                array('table' => 'catalog_product_entity', 'column' => 'exclude_from_supply_needs'),
                array('table' => 'sales_flat_order', 'column' => 'stocks_updated'),
                array('table' => 'sales_flat_order', 'column' => 'fullstock_date'),
                array('table' => 'sales_flat_order', 'column' => 'estimated_shipping_date'),
                array('table' => 'sales_flat_order', 'column' => 'payment_validated'),
                array('table' => 'sales_flat_order', 'column' => 'is_valid'),
                array('table' => 'sales_flat_order', 'column' => 'anounced_date'),
                array('table' => 'sales_flat_order', 'column' => 'anounced_date_max'),
                array('table' => 'sales_flat_quote', 'column' => 'anounced_date'),
                array('table' => 'sales_flat_quote', 'column' => 'anounced_date_max'),
                array('table' => 'sales_flat_order_item', 'column' => 'comments'),
                array('table' => 'sales_flat_order_item', 'column' => 'reserved_qty'),

                );
    foreach($columns as $column)
    {
        echo "<br>Remove columns ".implode('/', $column)." : ".executeSql('ALTER TABLE '.$tablePrefix.$column['table'].' DROP COLUMN '.$column['column'].'; ');
    }

    //remove core email templates
    echo "<hr><p><b>Email templates</b></p>";
    $templates = array(
        'Commande fournisseur',
        'Purchase Order'
    );
    foreach($templates as $template)
    {
        echo "<br>Remove template ".$template." : ".executeSql('delete from '.$tablePrefix.'core_email_template  where template_code = "'.$template.'"; ');
    }

    //remove magento attributes



    //remove core_resource records
    echo "<hr><p><b>Remove modules</b></p>";
    $modules = array('AdvancedStock_setup',
                    'BackgroundTask_setup',
                    'HealthyERP_setup',
                    'Orderpreparation_setup',
                    'Organizer_setup',
                    'Purchase_setup',
                    'SalesOrderPlanning_setup'
                    );
    foreach ($modules as $module)
    {
        echo "<br>Remove module ".$module." : ".executeSql('delete from '.$tablePrefix.'core_resource where code = "'.$module.'"; ');
    }

