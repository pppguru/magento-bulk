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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_QtyDifferentSumStockMovements extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{  
    const FIX_METHOD_SM_TO_QTY = 'sm_to_qty';
    const FIX_METHOD_QTY_TO_SM = 'qty_to_sm';

    /**
     * Product for with QTY != sum of stock movements for each warehouse
     * @return type
     */
    private static function getQtyDifferentSumStockMovementsList(){
      $tableCisi = Mage::helper('HealthyERP')->getPrefixedTableName('cataloginventory_stock_item');
      $tableSm = Mage::helper('HealthyERP')->getPrefixedTableName('stock_movement');
      $tableCpi = Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity');
      
      $sql='SELECT cisi.product_id, cisi.stock_id, cisi.qty as sqty,
                SUM(if(sm.sm_source_stock = cisi.stock_id, -sm.sm_qty, sm.sm_qty)) as mqty,
                (cisi.qty - SUM(if(sm.sm_source_stock = cisi.stock_id, -sm.sm_qty, sm.sm_qty))) as delta
        FROM '.$tableCisi.' cisi, '.$tableSm.' sm, '.$tableCpi.' cpi 
        WHERE sm.sm_product_id = cisi.product_id
        AND cisi.product_id = cpi.entity_id
        AND cpi.type_id = "simple"
        AND (sm.sm_target_stock = cisi.stock_id OR sm.sm_source_stock = cisi.stock_id)
        AND (sm.sm_target_stock != sm.sm_source_stock)
        GROUP BY product_id, stock_id
        HAVING (sqty <> mqty)';

      return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }


    public static function getErrorsList(){
      return self::getQtyDifferentSumStockMovementsList();
    }


    protected function getActions()
    {
      $actions = array();

      $openMode = parent::OPEN_URL_NEW_WINDOWS;

      switch($this->_indicator_status){
        case parent::STATUS_OK :
          break;
        case parent::STATUS_PARTIAL :
        case parent::STATUS_NOK :
           $actions[] = array($this->__('Update Stock Movements using stock quantity'),
               self::FIX_METHOD_QTY_TO_SM,
               $openMode);

           $actions[] = array($this->__('Update stock quantity using Stock Movements'),
               self::FIX_METHOD_SM_TO_QTY,
               $openMode);
           break;
      }

      return $actions;
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
              if($this->_countToFix>0){
                 $situation .= '<p>'.$this->__('product list : ').' : </p>';
                 foreach ($this->_idListToFix as $diffItem) {
                    $productId = $diffItem['product_id'];
                    $warehouseId = $diffItem['stock_id'];
                    $delta = $diffItem['delta'];
                    $qtyFromCatalogInventoryStockItem = $diffItem['sqty'];
                    $qtyFromStockMovement = $diffItem['mqty'];

                    $situation .= "<p>Product#".$productId." Warehouse#".$warehouseId." Delta=".$delta." Stock_QTY=".$qtyFromCatalogInventoryStockItem." StockMovements_QTY=".$qtyFromStockMovement."</p>";

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
   * @param type $productListToFix
   * @param type $action
   * @return boolean
   */
    public static function fixIssue($action){

      $redirect = true;
      $debug = false;
      
      $productListToFix = self::getErrorsList();


      
       $trace = array();
       $date = date('Y-m-d H:i');

       $helperPlanning = mage::helper('SalesOrderPlanning/ProductAvailabilityStatus');
       $helperStock = mage::helper('AdvancedStock/Product_Base');


       foreach ($productListToFix as $diffItem) {
           $productId = $diffItem['product_id'];
           $product = Mage::getModel('catalog/product')->load($productId);
           
           if($product->getId()>0){
              $warehouseId = $diffItem['stock_id'];
              $delta = $diffItem['delta'];
              $qtyFromCatalogInventoryStockItem = $diffItem['sqty'];
              $qtyFromStockMovement = $diffItem['mqty'];
              $fixDirection = (String)$action;

              if($debug){
                $trace[] = "Product#".$productId." Warehouse #".$warehouseId." Delta#".$delta." Stock_QTY=".$qtyFromCatalogInventoryStockItem." StockMovements_QTY=".$qtyFromStockMovement.' FixDirection'.$fixDirection;
              }


              $additionalDatas = array('sm_date' => $date, 'sm_type' => 'adjustment');

              //First case, Create positive of negative Stock movements to adjust stock movement history based on the current Qty in Stock
              if($fixDirection == self::FIX_METHOD_QTY_TO_SM){
                  try{
                     if($delta>0){
                         mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                                 null,
                                 $warehouseId,
                                 $delta,
                                 mage::helper('AdvancedStock')->__('Stock adjustement from StockQty %s', $qtyFromCatalogInventoryStockItem),
                                 $additionalDatas);
                     }else{
                       $delta = -$delta;
                       mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                                 $warehouseId,
                                 null,
                                 $delta,
                                 mage::helper('AdvancedStock')->__('Stock adjustement from StockQty %s', $qtyFromCatalogInventoryStockItem),
                                 $additionalDatas);
                     }
                  }catch(Exception $ex){
                       $trace[] = "Fix for product#".$productId." SKIPPED #".$ex->getMessage();
                  }
              }

              //Second case, update stock Quantity based on stock movements 
              if($fixDirection == self::FIX_METHOD_SM_TO_QTY){
                try
                {                  
                  $tableCisi = Mage::helper('HealthyERP')->getPrefixedTableName('cataloginventory_stock_item');

                  $sql='UPDATE '.$tableCisi.' SET qty ='.$qtyFromStockMovement.' 
                    WHERE product_id = '.$productId.'
                    AND stock_id = '.$warehouseId.';';

                  mage::getResourceModel('sales/order_item_collection')->getConnection()->query($sql);

                }catch(Exception $ex){
                    $trace[] = "Fix for product#".$productId." SKIPPED #".$ex->getMessage();
                }
              }

              //force reservation update
              $helperStock->updateStocks($product);

              //launch event to allow other updates for product (for example, purchase module handles this event to update waiting for delivery date and quantities
              Mage::dispatchEvent('advancedstock_product_force_stocks_update_requested', array('product' => $product));

              //refresh product availability status
              $helperPlanning->RefreshForOneProduct($productId);
               
          }else{
             $trace[] = "Product#".$productId." skipped because it does not exist anymore";
          }           
        }

        if($debug){
            foreach($trace as $task){
              echo '<br>'.$task;
            }
            die();
        }
      
      return $redirect;
    }

}
