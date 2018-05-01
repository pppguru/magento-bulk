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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_MissedReservedQty extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{  

    /**
     * Product  with qty is negative
     * @return type
     */
    private static function getMissedReservedQtys(){
      $tableOrders = Mage::helper('HealthyERP')->getPrefixedTableName('sales_flat_order');
      $tableOrdersItem = Mage::helper('HealthyERP')->getPrefixedTableName('sales_flat_order_item');
      $tableErpOrdersItem = Mage::helper('HealthyERP')->getPrefixedTableName('erp_sales_flat_order_item');

      $sql='SELECT sfo.entity_id as order_id,
                   sfo.created_at as order_date,
                   sfo.increment_id as increment_id,
                   sfoi.sku as sku,
                   sfoi.product_id as product_id,
                   sfoi.item_id as order_item_id
        FROM '.$tableOrders.' sfo
        INNER JOIN '.$tableOrdersItem.' sfoi ON sfo.entity_id = sfoi.order_id
        WHERE sfo.stocks_updated = 1
        AND sfo.state NOT IN("complete","canceled","closed")
        AND (
          (sfoi.item_id NOT IN(SELECT esfoi_item_id FROM '.$tableErpOrdersItem.'))
             OR
          (sfoi.item_id IN(SELECT esfoi_item_id FROM '.$tableErpOrdersItem.' WHERE preparation_warehouse = 0 ))
            )
        ORDER BY sfo.entity_id DESC
        ';

      return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }


    public static function getErrorsList(){
      return self::getMissedReservedQtys();
    }


    protected function getCurrentSituation()
    {
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = $this->__('Product with stock problem').' : '.$this->_countToFix.'<br/>';

        switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
             if (Mage::getStoreConfig('healthyerp/options/display_advanced_message')){
              if($this->_countToFix > 0){
                 foreach ($this->_idListToFix as $item) {
                    $situation .= "<p><b>Order# ".$item['increment_id']."</b> (".$item['order_date'].") - Sku =".$item['sku']."</p>";
                 }
               }
              }
            break;
          default:
             $situation .= $this->__(parent::DEFAULT_STATUS_MESSAGE);
             break;
        }
      }
      return $situation;
    }


  /**
   * Create stock movements of update quantity depending of the fix selected
   *
   * @param type $action
   * @return boolean
   */
    public static function fixIssue($action){

       $redirect = true;
        $orderIdListToConsiderList = self::getErrorsList();

        if(count($orderIdListToConsiderList)>0){
            $helper = mage::helper('BackgroundTask');

            $taskGroupCode = 'force_consider_order';
            $helper->AddGroup($taskGroupCode,
                mage::helper('HealthyERP')->__('Force to consider order by ERP'),
                'adminhtml/system_config/edit/section/healthyerp');

            $priority = 1;

            foreach ($orderIdListToConsiderList as $errorItem){
                $helper->AddTask('Force consider order #'.$errorItem['increment_id'].' and product '.$errorItem['sku'],
                    'AdvancedStock/Sales_Order',
                    'updateStocksForOneOrderProduct',
                    $errorItem['order_item_id'],
                    $taskGroupCode,
                    false,
                    $priority);
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
