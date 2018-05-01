<?php

class MDN_Shipworks_Helper_Shipments extends Mage_Core_Helper_Abstract {

    public function getShipments($storeId)
    {
        $allowedShippingMethod = explode(',', Mage::getStoreConfig('shipworks/general/shipping_method'));
        
        //get order in order preparation with shipments
        $collection = Mage::getModel('Orderpreparation/ordertoprepare')
                            ->getCollection()
                            ->addFieldToFilter('sent_to_shipworks', 0)
                            ->addFieldToFilter('shipping_method', array('in' => $allowedShippingMethod));
        $shipments = array();
        foreach($collection as $item)
        {
            $shipmentIncrementId = $item->getshipment_id();
            if ($shipmentIncrementId)
            {
                $shipments[] = Mage::getModel('sales/order_shipment')->load($shipmentIncrementId, 'increment_id');
            }
        }
 
        return $shipments;
    }
    
    /**
     * 
     * @param type $storeId
     * @return type
     */
    public function getShipmentsCount($storeId)
    {
        $shipments = $this->getShipments($storeId);
        return count($shipments);
    }
    
    /**
     * 
     * @param type $shipmentIncrementId
     */
    public function flagAsSent($shipmentIncrementId)
    {
        $items = Mage::getModel('Orderpreparation/ordertoprepare')
                        ->getCollection()
                        ->addFieldToFilter('shipment_id', $shipmentIncrementId);
        foreach($items as $item)
        {
            $item->setsent_to_shipworks(1)->save();
        }
    }
 
    public function flagAsNotSent($shipmentIncrementId)
    {
        $items = Mage::getModel('Orderpreparation/ordertoprepare')
                        ->getCollection()
                        ->addFieldToFilter('shipment_id', $shipmentIncrementId);
        foreach($items as $item)
        {
            $item->setsent_to_shipworks(0)->save();
        }
    }
    
}