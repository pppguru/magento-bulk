<?php

class MDN_CompetitorPrice_Block_Wizard extends Mage_Core_Block_Template {

    public function isWizarded()
    {
        return Mage::getStoreConfig('competitorprice/general/wizarded');
    }

    public function getCountries()
    {
        return Mage::getSingleton('CompetitorPrice/System_Config_Source_GoogleShoppingCountry')->getAllOptions();
    }

    public function getAttributes()
    {
        return Mage::getSingleton('CompetitorPrice/System_Config_Source_Attribute')->getAllOptions();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/CompetitorPrice_Wizard/Save');
    }

}
