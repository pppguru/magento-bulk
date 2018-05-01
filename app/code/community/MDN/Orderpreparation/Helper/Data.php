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
class MDN_Orderpreparation_Helper_Data extends Mage_Core_Helper_Abstract {

    private $_preparationWarehouseSessionKey = 'op_preparation_warehouse';
    private $_operatorSessionKey = 'op_operator';

    /**
     * Notify Shipment
     *
     * @param unknown_type $shipmentId
     */
    public function notifyShipment($shipmentId) {
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        if ($shipment->getId()) {
            if (!$shipment->getEmailSent()) {
                $shipment->sendEmail(true);
                $shipment->setEmailSent(true)->save();
            }
        }
    }

    /**
     * Notify Invoice
     *
     * @param unknown_type $invoiceId
     */
    public function notifyInvoice($invoiceId) {
        $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        if ($invoice->getId()) {
            if (!$invoice->getEmailSent()) {

                $invoice->sendEmail(true);
                $invoice->setEmailSent(true)->save();
            }
        }
    }

    /**
     * Add an order to selected orders
     *
     * @param unknown_type $orderId
     */
    public function addToSelectedOrders($orderId) {
        //Charge le num�ro de commande � partir du no de l'enregistrement dans le cache
        $RealOrderId = mage::getModel('Orderpreparation/ordertopreparepending')
                        ->load($orderId)
                        ->getopp_order_id();
        Mage::getModel('Orderpreparation/ordertoprepare')->AddSelectedOrder($RealOrderId);
    }

    /**
     * Remove an order from selected orders
     *
     * @param unknown_type $orderId
     */
    public function removeFromSelectedOrders($orderId) {
        Mage::getModel('Orderpreparation/ordertoprepare')->RemoveSelectedOrder($orderId);
    }

    /**
     * Create invoice & shipment for an order
     *
     * @param unknown_type $orderToPrepareId
     */
    public function createShipmentAndInvoices($orderId) {
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();

        //Load order to prepare
        $error = '';
        $order = mage::getModel('sales/order')->load($orderId);
        $OrderToPrepare = $this->getOrderToPrepareForCurrentContext($orderId);

        //if order cancelled, return false Mage_Sales_Model_Order::STATE_CANCELED
        if ($order->getstate() == 'canceled')
            return false;

        //si la commande n'a pas de shipment on la traite
        if (mage::getStoreConfig('orderpreparation/create_shipment_and_invoices_options/create_shipment') == 1) {
            if (!Mage::helper('Orderpreparation/Shipment')->ShipmentCreatedForOrder($order->getid(), $preparationWarehouseId, $operatorId)) {
                try {
                    if ($order->canShip()) {
                        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
                        $operatorId = mage::helper('Orderpreparation')->getOperator();
                        Mage::helper('Orderpreparation/Shipment')->CreateShipment($order, $preparationWarehouseId, $operatorId);
                    }
                } catch (Exception $ex) {
                    $error .= 'Error creating Shipment : ' . "\n" . $ex->getMessage();
                }
            }
        }
        
        //si la commande n'a pas de facture, on la traite
        if (mage::getStoreConfig('orderpreparation/create_shipment_and_invoices_options/create_invoice') == 1) {
            if (!Mage::helper('Orderpreparation/Invoice')->InvoiceCreatedForOrder($order->getid())) {
                try {
                    Mage::helper('Orderpreparation/Invoice')->CreateInvoice($order);
                } catch (Exception $ex) {
                    $error .= 'Error creating invoice : ' . "\n" . $ex->getMessage();
                }
            }
        }

        //raise error if exists
        if ($error != '')
            throw new Exception($error);
    }

    /**
     * Refresh in order preparation screen orders to fullstoc, stockless and ignored tab
     *
     * @param unknown_type $orderId
     */
    public function dispatchOrder($orderId)
    {
        $order = mage::getModel('sales/order')->load($orderId);

        if ($order != null && $order->getId() > 0) {
            mage::getmodel('Orderpreparation/ordertoprepare')->DispatchOrder($order);
        }
    }

    /**
     * Is an order is selected in order prepration screen
     *
     * if Yes, returns the object
     * If No, returns false
     *
     * @param sales_order $order
     */
    public function orderIsBeingPrepared($order) {
        $return = false;

        if ($order != null && $order->getId() > 0) {
            $obj = mage::getModel('Orderpreparation/ordertoprepare')->load($order->getId(), 'order_id');
            if ($obj->getId())
                $return = $obj;
        }

        return $return;
    }

    /**
     * Return preparation warehouse (for current user)
     */
    public function getPreparationWarehouse() {
        $session = Mage::getSingleton('adminhtml/session');
        $warehouseId = $session->getData($this->_preparationWarehouseSessionKey);

        //if not set, force to first preparation warehouse
        if (!$warehouseId) {
            $warehouse = mage::helper('AdvancedStock/Warehouse')
                            ->getWarehousesForPreparation()
                            ->getFirstItem();
            $this->setPreparationWarehouse($warehouse->getId());
            $warehouseId = $warehouse->getId();
        }

        return $warehouseId;
    }

    /**
     * Set preparation warehouse for current user
     */
    public function setPreparationWarehouse($warehouseId) {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setData($this->_preparationWarehouseSessionKey, $warehouseId);
    }

    /**
     * Return operator
     */
    public function getOperator() {
        $operatorId = 1;

        if (!mage::getStoreConfig('orderpreparation/misc/single_user_mode')) {
          $session = Mage::getSingleton('adminhtml/session');
          $operatorId = $session->getData($this->_operatorSessionKey);

          if (!$operatorId && Mage::getSingleton('admin/session')->getUser()) {
              $operatorId = Mage::getSingleton('admin/session')->getUser()->getId();
              $this->setOperator($operatorId);
          }
        }

        return $operatorId;
    }

    /**
     * Set operator id
     */
    public function setOperator($userId) {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setData($this->_operatorSessionKey, $userId);
    }

    /**
     * return order to prepare from order id using context (ie: preparation warehouse & operator)
     */
    public function getOrderToPrepareForCurrentContext($orderId) {
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $object = mage::getModel('Orderpreparation/ordertoprepare')
                        ->getCollection()
                        ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId)
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('user', $operatorId)
                        ->getFirstItem();
        return $object;
    }

    public function getCurrentOrderPreparationTab($orderId, $warehouseId){
        $tabType = '';
        if($orderId>0 && $warehouseId>0){
            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'select opp_type from '.$prefix.'order_to_prepare_pending where opp_order_id = '.$orderId.' and opp_preparation_warehouse='.$warehouseId;
            $tabType = mage::getResourceModel('catalog/product')->getReadConnection()->fetchOne($sql);
        }
        return $tabType;
    }
    
    public function getCurrentOrderPreparer($orderId, $warehouseId){
        $user = '';
        if($orderId>0 && $warehouseId>0){
            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'select user from '.$prefix.'order_to_prepare where order_id = '.$orderId.' and preparation_warehouse='.$warehouseId;
            $user = mage::getResourceModel('catalog/product')->getReadConnection()->fetchOne($sql);
        }
        return $user;
    }


    public function getOperatorName($operatorId){
        $operatorName = '';
        if($operatorId>0 && is_numeric($operatorId)){
            $operator = mage::getModel('admin/user')->load($operatorId);
            if($operator->getId()>0){
                $operatorName = $operator->getusername();
            }
        }
        return $operatorName;
    }


}

?>