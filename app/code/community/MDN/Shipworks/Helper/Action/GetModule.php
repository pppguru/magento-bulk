<?php

class MDN_Shipworks_Helper_Action_GetModule extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @param type $_GET
     */
    public function process($data) {
        Mage::helper('Shipworks/Xml')->writeStartTag("Module");

        Mage::helper('Shipworks/Xml')->writeElement("Platform", "Magento");
        Mage::helper('Shipworks/Xml')->writeElement("Developer", "Interapptive, Inc. (support@interapptive.com)");

        Mage::helper('Shipworks/Xml')->writeStartTag("Capabilities");
        Mage::helper('Shipworks/Xml')->writeElement("DownloadStrategy", "ByModifiedTime");
        Mage::helper('Shipworks/Xml')->writeFullElement("OnlineCustomerID", "", array("supported" => "true", "dataType" => "numeric"));
        Mage::helper('Shipworks/Xml')->writeFullElement("OnlineStatus", "", array("supported" => "true", "dataType" => "text", "downloadOnly" => "true"));
        Mage::helper('Shipworks/Xml')->writeFullElement("OnlineShipmentUpdate", "", array("supported" => "false"));
        Mage::helper('Shipworks/Xml')->writeCloseTag("Capabilities");

        Mage::helper('Shipworks/Xml')->writeCloseTag("Module");
    }

}