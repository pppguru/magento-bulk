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
class MDN_Orderpreparation_Helper_Dispatcher extends Mage_Core_Helper_Abstract {

    /**
     * Dispatch order in fullstock / stockless / ignored tab
     */
    public function DispatchOrder($order) {
        
        //delete old record(s)
        $debug = '##Dispatch order #' . $order->getId();
        $this->removeOrderFromOrderToPreparePending($order);

        //status check is  Mage_Sales_Model_Order::STATE_CANCELED
        $orderState = $order->getstate();
        if (($orderState == 'complete') || ($orderState == 'canceled'))
        {
            $debug .= ', state '.$orderState.' is not supported in order preparation';
            return $debug;
        }

        //get all preparation warehouses for this order
        $warehouses = Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->getPreparationWarehouses($order);

        //apply dispatch for every warehouse
        foreach ($warehouses as $warehouse) {
            //Dispatch order only if it doesn't belong to selected orders
            if (!$this->orderBelongsToSelectedOrders($order, $warehouse)) {
                if (!Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->IsCompletelyShipped($order)) {

                    //dispatch order depending of stock state
                    $opp_type = 'stockless';

                    if (Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->IsFullStock($order, $warehouse->getId()))
                        $opp_type = 'fullstock';

                    if (!mage::helper('AdvancedStock/Sales_ValidOrders')->orderIsValid($order))
                        $opp_type = 'ignored';

                    //get Customer Name to be ship (if no shipping adress, get Billing name as Possible
                    $ShipToName = '';
                    $orderShippingAddress = $order->getShippingAddress();
                    if ($orderShippingAddress != null){
                        $ShipToName = $orderShippingAddress->getName();
                    }else{
                        $orderBillingAddress = $order->getBillingAddress();
                        if ($orderBillingAddress != null){
                            $ShipToName = $orderBillingAddress->getName();
                        }
                    }

                    //insert record
                    $sortOrderValue = mage::getModel('Orderpreparation/ordertopreparepending')->calculateSortValue($order);
                    $OrderToPreparePending = mage::getModel('Orderpreparation/ordertopreparepending')
                                    ->setopp_preparation_warehouse($warehouse->getId())
                                    ->setopp_order_id($order->getId())
                                    ->setopp_remain_to_ship($this->getRemainToShipForOrder($order, $warehouse))
                                    ->setopp_shipto_name($ShipToName)
                                    ->setopp_details(mage::getModel('Orderpreparation/Ordertoprepare')->getDetailsForOrder($order, false))
                                    ->setopp_order_increment_id($order->getIncrementId())
                                    ->setopp_type($opp_type)
                                    ->setopp_shipping_method($order->getshipping_description())
                                    ->setopp_payment_validated($order->getpayment_validated())
                                    ->setopp_sort_value($sortOrderValue);

                    //fix double orders
                    $this->removeOrderFromOrderToPreparePending($order,$warehouse->getId());

                    $OrderToPreparePending->save();

                    Mage::dispatchEvent('orderpreparartion_after_dispatch_order', array('order' => $order, 'order_to_prepare_pending' => $OrderToPreparePending));
                    $debug .= ' (added to list ' . $opp_type . ') ';
                }
                else
                    $debug .= ' (order completely shipped) ';
            }
            else
                $debug .= ' (order belong to selected orders) ';
        }

        //mage::log($debug);
        return $debug;
    }

    /**
     * remove order from OrderToPreparePending (table containing fullstock & stockless orders)
     *
     * @param unknown_type $order
     */
    public function removeOrderFromOrderToPreparePending($order, $warehouseId = null) {

        //use collection because we can have the same order twice or more (depending of order items preparation warehouse)
        $orders = mage::getModel('Orderpreparation/ordertopreparepending')
                        ->getCollection()
                        ->addFieldToFilter('opp_order_id', $order->getId());
        if ($warehouseId)
            $orders->addFieldToFilter('opp_preparation_warehouse', $warehouseId);
        foreach ($orders as $order) {
            if ($order->getId())
                $order->delete();
        }
    }

    /**
     * Function to know if an order belong to selected orders
     *
     * @param unknown_type $order
     */
    public function orderBelongsToSelectedOrders($order, $warehouse) {
        $retour = true;
        $OrderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $order->getId())
                        ->addFieldToFilter('preparation_warehouse', $warehouse->getId())
                        ->getFirstItem();
        $retour = ($OrderToPrepare->getId() > 0);
        return $retour;
    }

    /**
     * Return order items with colors according to their preparation state
     *
     * @param unknown_type $order
     */
    public function getRemainToShipForOrder($order, $warehouse) {
        $retour = '';
        $websiteId = $order->getStore()->getwebsite_id();
        
        //parcourt la liste des produits
        $lines = array();
        foreach ($order->getItemsCollection() as $item) {

            $remaining_qty = $item->getRemainToShipQty();
            $productId = $item->getproduct_id();
            $name = $item->getName();
            $name .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($productId);
            $name .= $item->getOrderItemOptions('<br>');

            $style='';
            $tab = '';
            if (($item->getProductType() == 'configurable') || ($item->getProductType() == 'bundle')){
                $style = 'bold';
            }else{
                if ($item->getparent_item_id()){
                    $tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }else{
                    $style = 'bold';
                }
            }


            if ($item->getpreparation_warehouse() == $warehouse->getId()) {
                if ($remaining_qty > 0) {
                    $productStockManagement = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

                    if ($productStockManagement->getManageStock()) {
                        if ($item->getreserved_qty() >= $remaining_qty) {
                            $lines[] = array('sku' => $item->getsku(), 'color' => 'green', 'style' => $style, 'label' => $tab.((int) $remaining_qty) . 'x ' . $name);
                        } else {
                            if (($item->getreserved_qty() < $remaining_qty) && ($item->getreserved_qty() > 0)) {
                                $lines[] = array('sku' => $item->getsku(), 'color' => 'orange', 'style' => $style, 'label' =>  $tab.((int) $remaining_qty) . 'x ' . $name . " (" . $item->getreserved_qty() . '/' . $remaining_qty . ")");
                            } else {
                                $lines[] = array('sku' => $item->getsku(), 'color' => 'red', 'style' => $style, 'label' =>  $tab.((int) $remaining_qty) . 'x ' . $name);
                            }
                        }
                    }
                    else
                        $lines[] = array('sku' => $item->getsku(), 'color' => '#808080', 'style' => $style, 'label' =>  '<i>'.$tab.((int) $item->getqty_ordered()) . 'x ' . $name.'</i>');
                }
                else {
                        $lines[] = array('sku' => $item->getsku(), 'color' => '#000000', 'style' => $style, 'label' =>  '<s>'.$tab.((int) $item->getqty_ordered()) . 'x ' . $name.'</s>');
                }
            } else {
                $lines[] = array('sku' => $item->getsku(), 'color' => '#bbbbbb', 'style' => $style, 'label' =>  $tab.((int) $remaining_qty) . 'x ' . $name);
            }
        }

        $suffix = '';
        $prefix = '';

        //build array
        //don't use table because Magento rewrite class using js
        $skuWidth = '180px';
        $even = '#ffffff';
        $odd = '#f6f6f6';        
        $css = 'display: table-cell; border-style: solid; border-width: 1px; border-color: #FFFFFF; padding-bottom: 5px; padding: 5px;';
        $count = 0;
        $retour .= '<div style="display: table; table-layout: fixed;">';
        foreach($lines as $line)
        {
          if($line){
            $count ++;
            if(array_key_exists('style', $line)){
              switch($line['style'])
              {

                  case 'bold':
                      $prefix = '<b>';
                      $suffix = '</b>';
                      break;
                  case 'italic':
                      $prefix = '<i>';
                      $suffix = '</i>';
                      break;
                  case 'stroke':
                      $prefix = '<s>';
                      $suffix = '</s>';
                      break;
                  default:
                      $prefix = '';
                      $suffix = '';
                      break;
              }
            }
            $bgcolor= ($count%2) ? $even : $odd;
            $retour .= '<div style="display: table-row;">';
            $retour .= '<div style="'.$css.' background:'.$bgcolor.'; width: '.$skuWidth.'"><font color="'.$line['color'].'">'.$prefix.$line['sku'].$suffix.'</font></div>';
            $retour .= '&nbsp;<div style="'.$css.' background:'.$bgcolor.';"><font color="'.$line['color'].'">'.$prefix.$line['label'].$suffix.'</font></div>';
            $retour .= '</div>';
          }
        }
        $retour .= '<div/>';

        return $retour;
    }

}