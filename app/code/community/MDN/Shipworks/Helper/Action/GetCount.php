<?php

class MDN_Shipworks_Helper_Action_GetCount extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @param type $_GET
     */
    public function process() {

        // Write the params for easier diagnostics
        Mage::helper('Shipworks/Xml')->writeStartTag("Parameters");
        Mage::helper('Shipworks/Xml')->writeCloseTag("Parameters");

        $storeId = Mage::app()->getStore()->storeId;
        
        Mage::helper('Shipworks/Xml')->writeElement("OrderCount", Mage::helper('Shipworks/Shipments')->getShipmentsCount($storeId));
    }

}