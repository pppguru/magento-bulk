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
class MDN_Orderpreparation_Block_CarrierTemplate_ImportTracking extends Mage_Adminhtml_Block_Widget_Form {

    public function getCarrierTemplateAsCombo($name) {
        $retour = '<select name="' . $name . '" id="' . $name . '">';
        $retour .= '<option value=""></option>';
        $collection = mage::getModel('Orderpreparation/CarrierTemplate')->getCollection();
        foreach ($collection as $item) {
            $retour .= '<option value="' . $item->getct_id() . '">' . $item->getct_name() . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    public function getTemplateList() {
        return mage::getModel('Orderpreparation/CarrierTemplate')->getCollection();
    }

    public function getOrders() {
        return mage::getModel('Orderpreparation/ordertoprepare')->getSelectedOrders();
    }

    /*
     * Display the number of Orders matching with this shiping method
     */
    public function getShippingMethodCount($item) {
      $html = '';
      $count = 0;

      $templateShippingMethod = $item->getct_shipping_method();
      if($templateShippingMethod != null){
        $orders = mage::getModel('Orderpreparation/ordertoprepare')->getSelectedOrders();
        foreach ($orders as $order){
            $orderShippingMethod = $order->getshipping_method();            
            if($orderShippingMethod != null){
              if(strpos($orderShippingMethod, $templateShippingMethod) !== false){
                $count ++;
              }
            }
        }
      }
      
      $html .= '('.$count.')';

      return $html;
    }

    /**
     * Return carrier template for one order
     *
     * @param unknown_type $order
     */
    public function getCarrierTemplateForOrder($order) {
        return mage::helper('Orderpreparation/CarrierTemplate')->getTemplateForOrder($order);
    }
    
    /**
     * Return tracking numbers for current order
     * @param type $order 
     */
    public function getTrackingNumbers($order)
    {
        $trackings = null;
        
        $orderId = $order->getId();
        $orderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
        $shipment = $orderToPrepare->getShipment();
        if ($shipment)
        {
            $tracks = $shipment->getAllTracks();
            foreach($tracks as $track)
            {
                $trackings[] = $track->gettrack_number();
                
            }
        }

        if (!$trackings)
            return null;
        else
            return implode(',', $trackings);
    }

}