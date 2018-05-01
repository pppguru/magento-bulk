<?php

class MDN_Shipworks_Helper_Action_GetShipments extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @param type $_GET
     */
    public function process($data) {
        $storeId = Mage::app()->getStore()->storeId;


        // Write the params for easier diagnostics
        Mage::helper('Shipworks/Xml')->writeStartTag("Parameters");
        Mage::helper('Shipworks/Xml')->writeElement("MaxCount", $maxcount);
        Mage::helper('Shipworks/Xml')->writeCloseTag("Parameters");

        // setup the query
        $shipments = Mage::helper('Shipworks/Shipments')->getShipments($storeId);

        Mage::helper('Shipworks/Xml')->writeStartTag("Orders");

        foreach ($shipments as $shipment) {
            Mage::helper('Shipworks/Xml_Shipment')->WriteShipment($shipment);
            
            //flag shipment as "sent" to shipworks
            Mage::helper('Shipworks/Shipments')->flagAsSent($shipment->getIncrementId());
        }

        Mage::helper('Shipworks/Xml')->writeCloseTag("Orders");
    }

  

}