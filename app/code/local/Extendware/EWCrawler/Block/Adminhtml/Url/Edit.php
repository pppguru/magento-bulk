<?php

class Extendware_EWCrawler_Block_Adminhtml_Url_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getHeaderText()
    {
        return $this->__('Url');
    }
    
	public function getCustomUrl() {
        return Mage::registry('ew:current_job');
    }
}
