<?php

class MDN_CompetitorPrice_Block_Js extends Mage_Core_Block_Template {

    public function getHostUrl()
    {
        return Mage::helper('adminhtml')->getUrl("adminhtml/CompetitorPrice_Api");
    }

    public function getConfigurationUrl()
    {
        return Mage::helper('adminhtml')->getUrl("adminhtml/system_config/edit", array('section' => 'competitorprice'));
    }

}
