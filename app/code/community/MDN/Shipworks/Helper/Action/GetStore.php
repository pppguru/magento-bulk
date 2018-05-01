<?php

class MDN_Shipworks_Helper_Action_GetStore extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @param type $_GET
     */
    public function process($data) {
        // get state name
        $region_model = Mage::getModel('directory/region');
        if (is_object($region_model)) {
            $state = $region_model->load(Mage::getStoreConfig('shipping/origin/region_id'))->getDefaultName();
        }

        $name = Mage::getStoreConfig('system/store/name');
        $owner = Mage::getStoreConfig('trans_email/ident_general/name');
        $email = Mage::getStoreConfig('trans_email/ident_general/email');
        $country = Mage::getStoreConfig('shipping/origin/country_id');
        $website = Mage::getURL();

        Mage::helper('Shipworks/Xml')->writeStartTag("Store");
        Mage::helper('Shipworks/Xml')->writeElement("Name", $name);
        Mage::helper('Shipworks/Xml')->writeElement("CompanyOrOwner", $owner);
        Mage::helper('Shipworks/Xml')->writeElement("Email", $email);
        Mage::helper('Shipworks/Xml')->writeElement("State", $state);
        Mage::helper('Shipworks/Xml')->writeElement("Country", $country);
        Mage::helper('Shipworks/Xml')->writeElement("Website", $website);
        Mage::helper('Shipworks/Xml')->writeCloseTag("Store");
    }

}