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
class MDN_Orderpreparation_Helper_Shipment extends Mage_Core_Helper_Abstract {

    /**
     * Create a partial shipment
     *
     */
    public function CreateShipment(&$order, $warehouseId = null, $operatorId = null) {

        try {
            $convertor = Mage::getModel('sales/convert_order');
            $shipment = $convertor->toShipment($order);

            Mage::dispatchEvent('orderpreparartion_before_create_shipment', array('order' => $order));

            //browse order items
            $items = $this->GetItemsToShipAsArray($order->getid(), $warehouseId, $operatorId);
            foreach ($order->getAllItems() as $orderItem) {

                //skip special cases
                if (!$orderItem->isDummy(true) && !$orderItem->getQtyToShip()) {
                    continue;
                }               

                //add product to shipment
                if (isset($items[$orderItem->getitem_id()])) {
                    $ShipmentItem = $convertor->itemToShipmentItem($orderItem);
                    $ShipmentItem->setQty($items[$orderItem->getitem_id()]);
                    $shipment->addItem($ShipmentItem);
                }
            }

            //Check that shipment is not empty
            // on some version of magento, there is no native protection for that
            if(count($shipment->getAllItems())==0){
                throw new Exception('Can t create an empty shipment ! ');
            }

            //carrier template integration
            $this->generatePackageUsingCarrierTemplate($order, $shipment, $items);

            //save shipment
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

            //save shipment id in order_to_prepare item
            $this->StoreShipmentId($order->getid(), $shipment->getincrement_id(), $warehouseId, $operatorId);

            //create organizer to log the operator who has packed this shipment
            $this->logShippingActionInOrganizers($order, $shipment, $warehouseId, $operatorId);

            Mage::dispatchEvent('orderpreparation_after_create_shipment', array('order' => $order, 'shipment' => $shipment));

            return $shipment;

        } catch (Exception $ex) {
            Mage::logException($ex);
            throw new Exception('Error while creating Shipment for Order ' . $order->getincrement_id() . ': ' . $ex->getMessage());
        }
        
        return null;
    }

    public function generatePackageUsingCarrierTemplate($order, $shipment, $items){
        $orderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($order->getId(), 'order_id');
        $carrierTemplate = mage::helper('Orderpreparation/CarrierTemplate')->getTemplateForOrder($order);

        if ($carrierTemplate != null && $orderToPrepare->getId()>0){
            $customValues = $orderToPrepare->getcustom_values();

            if($carrierTemplate->getct_type() != 'manual') {
                $specificCarrierTemplate = mage::helper('Orderpreparation/SpecificCarrierTemplates_' . $carrierTemplate->getct_type());
                if ($specificCarrierTemplate != null) {
                    $customValuesAsArray = $specificCarrierTemplate->getPrepareCustomValuesAsPackageData($customValues);
                    $specificCarrierTemplate->createPackages($shipment, array($customValuesAsArray));
                }
            }
        }
    }

    /**
     * Log into organizer who has shippied the order
     *
     * @param type $orderId
     * @param type $shipmentId
     * @param type $warehouseId
     * @param type $operatorId
     */
    public function logShippingActionInOrganizers($order, $shipment, $warehouseId = null, $operatorId = null) {

        if (Mage::getStoreConfig('orderpreparation/packing/create_organizer_on_commit')) {
            if($operatorId && $warehouseId){
                $date = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
                $helper = mage::helper('Orderpreparation');
                $adminUserId = 1;

                $operatorName = $helper->getOperatorName($operatorId);
                $warehouseName = mage::getModel('AdvancedStock/Warehouse')->load($warehouseId)->getstock_name();

                $title = $helper->__('Shipment #%s prepared by %s on warehouse %s', $shipment->getincrement_id(),$operatorName,$warehouseName);

                Mage::getModel('Organizer/Task')
                        ->setot_author_user($adminUserId)
                        ->setot_caption($title)
                        ->setot_description('')
                        ->setot_entity_type(MDN_Organizer_Model_Task::ENTITY_TYPE_ORDER)
                        ->setot_entity_id($order->getId())
                        ->setot_entity_description($helper->__('Order #%s',$order->getincrement_id()))
                        ->setot_notify_date($date)
                        ->setot_created_at($date)
                        ->save();
                
            }
        }
        
    }



    /*
     * Get items to ship for order id
     *
     * @param unknown_type $OrderId
     */

    public function GetItemsToShipAsArray($OrderId, $warehouseId = null, $operatorId = null) {
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                ->getCollection()
                ->addFieldToFilter('order_id', $OrderId);

        if ($warehouseId)
            $collection->addFieldToFilter('preparation_warehouse', $warehouseId);
        if ($operatorId)
            $collection->addFieldToFilter('user', $operatorId);

        $retour = array();
        foreach ($collection as $item) {
            $retour[$item->getorder_item_id()] = $item->getqty();
        }

        return $retour;
    }

    /**
     * Store shipment id in ordertoprepare model
     *
     * @param unknown_type $OrderId
     */
    public function StoreShipmentId($OrderId, $ShipmentId, $warehouseId = null, $operatorId = null) {
        $collection = mage::getModel('Orderpreparation/ordertoprepare')
                ->getCollection()
                ->addFieldToFilter('order_id', $OrderId);
        if ($warehouseId)
            $collection->addFieldToFilter('preparation_warehouse', $warehouseId);
        if ($operatorId)
            $collection->addFieldToFilter('user', $operatorId);

        $orderToPrepare = $collection->getFirstItem();
        $orderToPrepare->setshipment_id($ShipmentId)->save();
    }

    /**
     * Check if shipment is created for one order
     *
     */
    public function ShipmentCreatedForOrder($OrderId, $warehouseId = null, $operatorId = null) {
        $collection = mage::getModel('Orderpreparation/ordertoprepare')
                ->getCollection()
                ->addFieldToFilter('order_id', $OrderId);
        if ($warehouseId)
            $collection->addFieldToFilter('preparation_warehouse', $warehouseId);
        if ($operatorId)
            $collection->addFieldToFilter('user', $operatorId);

        $orderToPrepare = $collection->getFirstItem();

        if ($orderToPrepare->getshipment_id() != null && $orderToPrepare->getshipment_id() != '')
            return true;
        else
            return false;
    }

}