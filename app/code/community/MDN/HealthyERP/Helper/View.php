<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_HealthyERP_Helper_View extends Mage_Core_Helper_Abstract {

  //Supply Need
  const ERP_VIEW_SUPPLYNEEDS_BASE = 'erp_view_supplyneeds_base';
  const ERP_VIEW_SUPPLYNEEDS_GLOBAL = 'erp_view_supplyneeds_global';
  const ERP_VIEW_SUPPLYNEEDS_WAREHOUSE = 'erp_view_supplyneeds_warehouse';

  //Inventory
  const ERP_VIEW_INVENTORY_MISSED_LOCATION = 'erp_inventory_missed_location';


  /**
   * Return the list of the View of ERP
   */
  public function getViewList(){

      $views = array();

      $helper = Mage::helper('HealthyERP');
      $views[] = $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_BASE);
      $views[] = $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_GLOBAL);
      $views[] = $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_WAREHOUSE);
      $views[] = $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_INVENTORY_MISSED_LOCATION);

      return $views;
  }

  public function DropAndRecreateSupplyNeedsViews(){

        $conn = mage::getResourceModel('sales/order_item_collection')->getConnection();
        $helper = Mage::helper('HealthyERP');

        $conn->query('DROP TABLE IF EXISTS '. $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_BASE));
        $conn->query('DROP VIEW IF EXISTS '. $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_BASE));
        $conn->query($this->getCreateViewQuery(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_BASE));

        $conn->query('DROP TABLE IF EXISTS '. $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_GLOBAL));
        $conn->query('DROP VIEW IF EXISTS '. $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_GLOBAL));
        $conn->query($this->getCreateViewQuery(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_GLOBAL));

        $conn->query('DROP TABLE IF EXISTS '. $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_WAREHOUSE));
        $conn->query('DROP VIEW IF EXISTS '. $helper->getPrefixedTableName(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_WAREHOUSE));
        $conn->query($this->getCreateViewQuery(MDN_HealthyERP_Helper_View::ERP_VIEW_SUPPLYNEEDS_WAREHOUSE));
  }


  /**
   * Return the SQL to execute to recreate a Missing View
   *
   * @param String $viewname
   * @return String the SQL
   */
   public function getCreateViewQuery($viewName){

      $sql = '';

      $prefix = Mage::getConfig()->getTablePrefix();
      if(!empty($prefix) && !empty($viewName)){
          $prefixLen = strlen($prefix);
          $viewName = substr($viewName,$prefixLen);
      }
      
      switch ($viewName) {
        case self::ERP_VIEW_SUPPLYNEEDS_BASE :
          $sql = $this->getSQLforSupplyNeedsBase();
          break;
        case self::ERP_VIEW_SUPPLYNEEDS_GLOBAL :
          $sql = $this->getSQLforSupplyNeedsGlobal();
          break;
        case self::ERP_VIEW_SUPPLYNEEDS_WAREHOUSE :
          $sql = $this->getSQLforSupplyNeedWarehouse();
          break;
        case self::ERP_VIEW_INVENTORY_MISSED_LOCATION :
          $sql = $this->getSQLforInventoryMissedLocation();
          break;
        default:
        break;
      }

      return $sql;
   }



    public function getDeleteViewOrTableQuery($viewName){

        $sql = '';

        $prefix = Mage::getConfig()->getTablePrefix();
        if(!empty($prefix) && !empty($viewName)){
            $prefixLen = strlen($prefix);
            $viewName = substr($viewName,$prefixLen);
        }



        return $sql = $this->getSQLforSupplyNeedsBase();
    }
   /**
    * Get the SQL to recreate the view erp_view_supplyneeds_base
    * @return String SQL
    */
   private function getSQLforSupplyNeedsBase(){

     //find attribute ids
      $waitingForDeliveryAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'waiting_for_delivery_qty')->getId();
      $manualSupplyNeedsAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'manual_supply_need_qty')->getId();
      $nameAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'name')->getId();

     //create query
    $sql = "
        create or replace view
            ".Mage::helper('HealthyERP')->getPrefixedTableName(self::ERP_VIEW_SUPPLYNEEDS_BASE)."
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
            cast(if (use_config_ideal_stock_level = 1, CONVERT(tbl_config_ideal.value, signed), ideal_stock_level) AS UNSIGNED INT) as ideal_stock_level,
            if (tbl_waiting_for_delivery_qty.value, tbl_waiting_for_delivery_qty.value, 0) as waiting_for_delivery_qty,
            if (tbl_manual_supply_needs.value, tbl_manual_supply_needs.value, 0) as manual_supply_needs_qty,
            if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty_for_valid_orders, 0, tbl_stock_item.stock_ordered_qty_for_valid_orders - tbl_stock_item.qty) as qty_needed_for_valid_orders,
            if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, 0, tbl_stock_item.stock_ordered_qty - tbl_stock_item.qty) as qty_needed_for_orders,
            if (if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0) < if (use_config_notify_stock_qty = 1, CONVERT(tbl_config_notify.value, signed), notify_stock_qty), if (use_config_ideal_stock_level = 1, CONVERT(tbl_config_ideal.value, signed), ideal_stock_level) - if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0), 0) as qty_needed_for_ideal_stock,
            if (tbl_manual_supply_needs.value > if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0),tbl_manual_supply_needs.value  - if (tbl_stock_item.qty > tbl_stock_item.stock_ordered_qty, tbl_stock_item.qty - tbl_stock_item.stock_ordered_qty, 0) , 0) as qty_needed_for_manual_supply_needs

        from
            ".Mage::helper('HealthyERP')->getPrefixedTableName('cataloginventory_stock_item')." tbl_stock_item
            JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('cataloginventory_stock')." tbl_stock on (tbl_stock_item.stock_id = tbl_stock.stock_id)
            JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity')." tbl_product on (tbl_stock_item.product_id = tbl_product.entity_id)
            JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('core_config_data')." tbl_config_notify on (1=1)
            JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('core_config_data')." tbl_config_ideal on (1=1)
            JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('core_config_data')." tbl_config_manufacturer on (1=1)
            LEFT JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity_int')." tbl_waiting_for_delivery_qty on (tbl_waiting_for_delivery_qty.entity_id = tbl_stock_item.product_id and tbl_waiting_for_delivery_qty.attribute_id = {waiting_for_delivery_attribute_id} and tbl_waiting_for_delivery_qty.store_id = 0)
            LEFT JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity_int')." tbl_manual_supply_needs on (tbl_manual_supply_needs.entity_id = tbl_stock_item.product_id and tbl_manual_supply_needs.attribute_id = {manual_supply_needs_attribute_id} and tbl_manual_supply_needs.store_id = 0)
            LEFT JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity_int')." tbl_manufacturer on (tbl_manufacturer.entity_id = tbl_stock_item.product_id and tbl_manufacturer.attribute_id = tbl_config_manufacturer.value and tbl_manufacturer.store_id = 0)
            LEFT JOIN ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity_varchar')." tbl_name on (tbl_name.entity_id = tbl_stock_item.product_id and tbl_name.attribute_id = {name_attribute_id} and tbl_name.store_id = 0)

        where
            tbl_stock.stock_disable_supply_needs <> 1
            and ((tbl_stock_item.use_config_manage_stock = 1) or (tbl_stock_item.manage_stock = 1))
            and tbl_product.exclude_from_supply_needs = 0
            and tbl_config_notify.path = 'cataloginventory/item_options/notify_stock_qty'
            and tbl_config_ideal.path = 'advancedstock/prefered_stock_level/ideal_stock_default_value'
            and tbl_config_manufacturer.path = 'purchase/supplyneeds/manufacturer_attribute'
            ;

    ";

    //replace attribute values
    $sql = str_replace('{waiting_for_delivery_attribute_id}', $waitingForDeliveryAttributeId, $sql);
    $sql = str_replace('{manual_supply_needs_attribute_id}', $manualSupplyNeedsAttributeId, $sql);
    $sql = str_replace('{name_attribute_id}', $nameAttributeId, $sql);

    return $sql;

   }

   /**
    * Get the SQL to recreate the view erp_view_supplyneeds_global
    * @return String SQL
    */
   private function getSQLforSupplyNeedsGlobal(){

     $sql = "

      create or replace view
          ".Mage::helper('HealthyERP')->getPrefixedTableName(self::ERP_VIEW_SUPPLYNEEDS_GLOBAL)."
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
          ".Mage::helper('HealthyERP')->getPrefixedTableName(self::ERP_VIEW_SUPPLYNEEDS_BASE)."
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
          ;

      ";

     //put before the sql for the parent View to avoid crash if supply needbase is missing
     $sql = $this->getSQLforSupplyNeedsBase().' '.$sql;

     return $sql;
   }

   /**
    * Get the SQL to recreate the view erp_view_supplyneeds_warehouse
    * @return String SQL
    */
   private function getSQLforSupplyNeedWarehouse(){
       $sql = "create or replace view
          ".Mage::helper('HealthyERP')->getPrefixedTableName(self::ERP_VIEW_SUPPLYNEEDS_WAREHOUSE)."
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
          ".Mage::helper('HealthyERP')->getPrefixedTableName(self::ERP_VIEW_SUPPLYNEEDS_BASE)."
      where
          (qty_needed_for_valid_orders > 0 )
          OR
          (qty_needed_for_orders > 0)
          OR
          (qty_needed_for_ideal_stock > 0)
          OR
          (qty_needed_for_manual_supply_needs > 0)
          ;

     ";

      //put before the sql for the parent View to avoid crash if supply needbase is missing
      $sql = $this->getSQLforSupplyNeedsBase().' '.$sql;

      return $sql;
   }

   /**
    * Get the SQL to recreate the view erp_inventory_missed_location
    * @return String SQL
    */
   private function getSQLforInventoryMissedLocation(){

     $sql = "create or replace VIEW ".Mage::helper('HealthyERP')->getPrefixedTableName(self::ERP_VIEW_INVENTORY_MISSED_LOCATION)."
                      AS
                      select
                              eisp_shelf_location,
                              eisp_inventory_id,
                          SUM(eisp_stock) product_count
                      from ".Mage::helper('HealthyERP')->getPrefixedTableName('erp_inventory_stock_picture')."

                      where
                          eisp_shelf_location not in
                          (
                              select
                                  eip_shelf_location
                              from
                                ".Mage::helper('HealthyERP')->getPrefixedTableName('erp_inventory_product')."

                              where
                                  eisp_inventory_id = eip_inventory_id
                          )
                      group by 
                        eisp_shelf_location, eisp_inventory_id
                        ;

                ";

     return $sql;
   }
   
}