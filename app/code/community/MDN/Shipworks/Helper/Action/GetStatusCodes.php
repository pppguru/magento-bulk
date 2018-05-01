<?php

class MDN_Shipworks_Helper_Action_GetStatusCodes extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @param type $_GET
     */
    public function process() {
        Mage::helper('Shipworks/Xml')->writeStartTag("StatusCodes");

        $statuses_node = Mage::getConfig()->getNode('global/sales/order/statuses');

        foreach ($statuses_node->children() as $status) {
            Mage::helper('Shipworks/Xml')->writeStartTag("StatusCode");
            Mage::helper('Shipworks/Xml')->writeElement("Code", $status->getName());
            Mage::helper('Shipworks/Xml')->writeElement("Name", $status->label);
            Mage::helper('Shipworks/Xml')->writeCloseTag("StatusCode");
        }

        Mage::helper('Shipworks/Xml')->writeCloseTag("StatusCodes");
    }

}