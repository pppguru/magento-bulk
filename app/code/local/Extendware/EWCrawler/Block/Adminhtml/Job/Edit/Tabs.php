<?php

class Extendware_EWCrawler_Block_Adminhtml_Job_Edit_Tabs extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('Job'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('general', array(
			'label' => $this->__('General'),
			'content' => $this->getLayout()->createBlock('ewcrawler/adminhtml_job_edit_tab_general')->toHtml(),
		
		));
		
		$this->addTab('log', array(
			'label' => $this->__('Log'),
			'content' => $this->getLayout()->createBlock('ewcrawler/adminhtml_job_edit_tab_log')->toHtml(),
		
		));
		
		return parent::_beforeToHtml();
	}
}
