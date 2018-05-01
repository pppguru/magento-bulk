<?php

class Extendware_EWCrawler_Block_Adminhtml_Job_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getHeaderText()
    {
        return $this->__('Job');
    }
    
	public function getJob() {
        return Mage::registry('ew:current_job');
    }
}
