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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_UnconsistantQtyOrdered extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{

    const DEFAULT_ACTION = 'refresh';

    private $_productIdListToFix = null;
    private $_productCountToFix = null;


    /**
     * Check the sum of quantity ordered equal the quantity stored in stock_ordered_qty
     *
     * @return type
     */
    public static function getErrorsList(){
      
      $tableSfo = Mage::helper('HealthyERP')->getPrefixedTableName('sales_flat_order');
      $tableCisi = Mage::helper('HealthyERP')->getPrefixedTableName('cataloginventory_stock_item');
      $tableSfoi = Mage::helper('HealthyERP')->getPrefixedTableName('sales_flat_order_item');
      $tableEsfoi = Mage::helper('HealthyERP')->getPrefixedTableName('erp_sales_flat_order_item');
      $tableCpe = Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity');

      $additionnalCondition = '';
      if (mage::getStoreConfig('advancedstock/valid_orders/do_not_consider_invalid_orders_for_stocks')){
          $additionnalCondition = ' AND sfo.is_valid = 1 ';
      }

     $sql="
        SELECT
            cisi.product_id as pid,
            cpe.sku as sku,
            cisi.stock_id as stock_id,
            CAST(stock_ordered_qty AS SIGNED) as qty,
            CAST(SUM(if(qty_ordered - qty_shipped - qty_canceled - qty_refunded < 0, 0, qty_ordered - qty_shipped - qty_canceled - qty_refunded)) AS SIGNED) as expected
        FROM
            ".$tableCisi." cisi,
            ".$tableSfo." sfo,
            ".$tableSfoi." sfoi,
            ".$tableEsfoi." esfoi,
            ".$tableCpe." cpe
        WHERE
            sfo.entity_id = sfoi.order_id
            and sfoi.product_id = cisi.product_id
            and esfoi.esfoi_item_id = sfoi.item_id
            and esfoi.preparation_warehouse = cisi.stock_id
            and cpe.entity_id = cisi.product_id
            and sfo.state not in ('canceled', 'complete', 'closed')
            and cpe.type_id = 'simple'
            ".$additionnalCondition."
        GROUP BY
            cisi.product_id,
            cisi.stock_id,
            stock_ordered_qty
        HAVING
            CAST(SUM(if(qty_ordered - qty_shipped - qty_canceled - qty_refunded < 0, 0, qty_ordered - qty_shipped - qty_canceled - qty_refunded)) AS SIGNED) != CAST(stock_ordered_qty AS SIGNED)
    ";

       
      return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }



    protected function getCurrentSituation()
    {
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = $this->__('Product which the ordered qty is not consistant').' : '.$this->_countToFix.'<br/>';
        switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
             if (Mage::getStoreConfig('healthyerp/options/display_advanced_message')){
              if($this->_countToFix>0){
                 $situation .= $this->__('Product list : ').' : <br/>';
                 foreach ($this->_idListToFix as $diffItem) {
                    $situation .= $diffItem['sku']." orderedQtyStored=".$diffItem['qty']." in warehouseId=".$diffItem['stock_id']." orderedQtyExpected=".$diffItem['expected']."<br/>";
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
     * Refresh the calculation of the stock_quantity ordered for all product with an error
     * 
     * this will be executed in background tasks
     * 
     * @param type $productListToFix
     * @param type $action
     * @return boolean
     */
    public static function fixIssue($action){

        $redirect = true;

        $productListToFix = self::getErrorsList();
   

        if( count($productListToFix)>0){
           $helper = mage::helper('BackgroundTask');

           $taskGroupCode = 'refresh_stock_ordered_qty';
           $helper->AddGroup($taskGroupCode,
                             mage::helper('HealthyERP')->__('Refresh Stock Ordered Qty'),
                             'adminhtml/system_config/edit/section/healthyerp');

           $priority = 5;

           foreach ($productListToFix as $diffItem) {
               $productId = $diffItem['pid'];
               $expectedQty = $diffItem['expected'];
               $qty = $diffItem['qty'];
               $warehouseId = $diffItem['stock_id'];

               if($productId>0 && $expectedQty>=0 && $warehouseId>=0 && intval($qty) != intval($expectedQty) ){
                 $helper->AddTask('Refresh Stock Ordered Qty for product#'.$productId,
                                'AdvancedStock/Product_Ordered',
                                'updateStockOrderedQty',
                                $diffItem,
                                $taskGroupCode,
                                false,
                                $priority);
              }              
           }

         $helper->ExecuteTaskGroup($taskGroupCode);
         $redirect = false;
        }

      return $redirect;
    }

}