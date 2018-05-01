<?php

class Extendware_EWCore_Block_Adminhtml_System_Report_Edit_Tabs extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('Report'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('general', array(
			'label' => $this->__('General'),
			'content' => $this->getLayout()->createBlock('ewcore/adminhtml_system_report_edit_tab_general')->toHtml(),
		
		));
		
		$this->addTab('trace', array(
			'label' => $this->__('Trace'),
			'content' => $this->getLayout()->createBlock('ewcore/adminhtml_system_report_edit_tab_trace')->toHtml(),
		
		));
		
		return parent::_beforeToHtml();
	}
}
