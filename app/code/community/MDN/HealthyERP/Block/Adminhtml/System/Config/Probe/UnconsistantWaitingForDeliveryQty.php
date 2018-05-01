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
 * @copyright  Copyright (c) 2013 Boostmyshop (http://www.boostmyshop.com)
 * @author : Guillauem SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_UnconsistantWaitingForDeliveryQty extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{

  
    const DEFAULT_ACTION = 'refresh';


    /**
     * Check if waiting_for_delivery_qty are consistant with current data
     *
     * @return type
     */
    public static function getErrorsList(){

        $list = array();

        $conn = mage::getResourceModel('sales/order_item_collection')->getConnection();
        $tableprefix =  mage::getModel('Purchase/Constant')->getTablePrefix();

        $attributeCode = 'waiting_for_delivery_qty';
        
        //get an array from pending purchase orders
        $sql = "select
                    pop_product_id product_id,
                    SUM(IF((IFNULL(pop_qty, 0) - IFNULL(pop_supplied_qty, 0) > 0), (IFNULL(pop_qty, 0) - IFNULL(pop_supplied_qty, 0)), 0)) qty
                from
                    ".Mage::helper('HealthyERP')->getPrefixedTableName('purchase_order')."
                    inner join ".Mage::helper('HealthyERP')->getPrefixedTableName('purchase_order_product')." on (pop_order_num = po_num)
                    inner join ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity')." tbl_product on (pop_product_id = entity_id)
                where
                    po_status = '" . MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY . "'
                group by
                    pop_product_id
                ";

        $fromPo = $conn->fetchAll($sql);

        //get an array with product having a waiting for delivery value
        $sql = "select
                    tbl_product.entity_id as product_id,
                    tbl_wfd.value as qty
                from
                    ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity')." tbl_product
                    inner join ".Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity_int')." tbl_wfd on (tbl_product.entity_id = tbl_wfd.entity_id and store_id = 0)
                    inner join ".Mage::helper('HealthyERP')->getPrefixedTableName('eav_attribute')." tbl_attribute on (tbl_attribute.attribute_id = tbl_wfd.attribute_id)
                where
                    tbl_attribute.attribute_code = '".$attributeCode."'
                    and tbl_product.type_id = 'simple'";
        
        $fromProducts = $conn->fetchAll($sql);

        //transform arrays to get product id as key
        $t = array();
        foreach($fromPo as $item)
        {
            $t[$item['product_id']] = $item['qty'];
        }       
        $fromPo = $t;        
        $t = array();
        foreach($fromProducts as $item)
        {
            $t[$item['product_id']] = $item['qty'];
        }
        $fromProducts = $t;

        //compare arrays
        $allPoKeys = array_keys($fromPo);
        $allProductKeys = array_keys($fromProducts);
        $allKeys = array_merge($allPoKeys, $allProductKeys);
        $allKeys = array_unique($allKeys);        

        foreach($allKeys as $productId)
        {
            $valueInPo = (isset($fromPo[$productId]) ? $fromPo[$productId] : 0);
            $valueInProduct = (isset($fromProducts[$productId]) ? $fromProducts[$productId] : 0);
            if ($valueInPo != $valueInProduct){
                $list[] = array('pid' => $productId, 'qty' => $valueInProduct, 'expected' => $valueInPo);
            }
        }

        return $list;
    }



    protected function getCurrentSituation()
    {
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = $this->__('Products with a Waiting for delivery Qty issue').' : '.$this->_countToFix.'<br/>';
        switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
            if (Mage::getStoreConfig('healthyerp/options/display_advanced_message')){
              if($this->_countToFix>0){
                $situation .= $this->__('Product list').' : <br/>';
                foreach ($this->_idListToFix as $diffItem) {
                   $situation .= "Product#".$diffItem['pid']." WaitingForDeliveryQty=".$diffItem['qty']."  WaitingForDeliveryExpected=".$diffItem['expected']."<br/>";
                }
              }
             }
             break;
          default:
             $situation = $this->__(parent::DEFAULT_STATUS_MESSAGE);
             break;
        }
      }
      return $situation;
    }

    /**
     * Update the twaiting_for_delivery_qty for all products in error
     * 
     * @param type $productListToFix
     * @param type $action
     * @return boolean
     */
    public static function fixIssue($action){

       $redirect = true;

       $productListToFix = self::getErrorsList();
       
       if(count($productListToFix)>0){

        $helper = mage::helper('BackgroundTask');
        $taskGroupCode = 'force_refresh_wating_for_delivery';
        $helper->AddGroup($taskGroupCode,
                          mage::helper('HealthyERP')->__('Force to refresh waiting for delivery status'),
                          'adminhtml/system_config/edit/section/healthyerp');

        $priority = 5;

        foreach ($productListToFix as $productIssue){
          $productId = $productIssue['pid'];
          if($productId>0){
            $helper->AddTask('Update waiting for delivery qty for product #' . $productId,
                           'purchase',
                           'updateProductWaitingForDeliveryQty',
                           $productId,
                           $taskGroupCode, false, $priority);
          }
        }

        //set debug to off to avoid crash
        if (Mage::getStoreConfig('advancedstock/cron/debug')){
          Mage::getConfig()->saveConfig('advancedstock/cron/debug', 0);
          Mage::getConfig()->cleanCache();
        }

        $helper->ExecuteTaskGroup($taskGroupCode);
        $redirect = false;
       }
       
       return $redirect;
    }

}