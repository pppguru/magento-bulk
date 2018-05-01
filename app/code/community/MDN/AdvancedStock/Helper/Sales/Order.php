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
class MDN_AdvancedStock_Helper_Sales_Order extends Mage_Core_Helper_Abstract {

    //todo : remove !
    public function getPreparationWarehouse($order) {
        try {
            throw new Exception('11');
        } catch (Exception $ex) {
            die('getPreparationWarehouse is deprecated<br>' . $ex->getTraceAsString());
        }


        //get preparation warehouse from assignments
        $websiteId = $order->getStore()->getwebsite_id();
        $warehouse = mage::helper('AdvancedStock/Warehouse')->getWarehouseForAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentOrderPreparation);

        //if cant find warehouse, return default warehouse
        if (!$warehouse->getId()) {
            $warehouse = mage::getModel('cataloginventory/stock')->load(1);
        }

        return $warehouse;
    }

   /**
    * Try to consider the order for erp
    * 
    * @param type $orderId
    */
   public function updateStocksForOneOrder($orderId){
     if($orderId >0){
      $order  = mage::getModel('sales/order')->load($orderId);
      if($order->getId()){
        mage::getModel('AdvancedStock/Observer')->UpdateStocksForOneOrder($order);
      }
     }
   }

    public function updateStocksForOneOrderProduct($orderItemId){

        if($orderItemId >0){

            $debug = 'UpdateStocksForOneOrderProduct for '.$orderItemId;

            $orderItem  = mage::getModel('sales/order_item')->load($orderItemId);
            try
            {
                Mage::getResourceModel('AdvancedStock/SalesFlatOrderItem')->initializeRecord($orderItem);
            }
            catch(Exception $ex)
            {
                $debug .= "InitializeRecord exception : ".$ex->getMessage();
                mage::log($debug, null, 'erp_new_orders_consideration.log');
            }

            $order  = mage::getModel('sales/order')->load($orderItem->getorder_id());
            if($order->getId()){
                $preparationWarehouseId = mage::helper('AdvancedStock/Router')->getWarehouseForOrderItem($orderItem, $order);
                if (!$preparationWarehouseId)
                    $preparationWarehouseId = 1;

                $debug .= 'Affect warehouse #'.$preparationWarehouseId;

                //Affect order item to warehouse
                try
                {
                    Mage::helper('AdvancedStock/Router')->affectWarehouseToOrderItem(array('order_item_id' => $orderItem->getId(), 'warehouse_id' => $preparationWarehouseId));
                }
                catch(Exception $ex)
                {
                    $exMessage = $ex->getMessage();
                    if($exMessage){
                        $debug .= '<br/>--> Error updating stocks  because of product : '.$orderItem->getsku().' (Id='.$orderItem->getproduct_id().') for OrderItem #' . $orderItem->getId() . ' : ' . $exMessage;
                    }
                }
                mage::log($debug, null, 'erp_new_orders_consideration.log');
            }
        }
    }




}