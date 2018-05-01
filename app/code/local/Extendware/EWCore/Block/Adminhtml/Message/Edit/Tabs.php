<?php

class Extendware_EWCore_Block_Adminhtml_Message_Edit_Tabs extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('Message'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('general', array(
			'label' => $this->__('General'),
			'content' => $this->getLayout()->createBlock('ewcore/adminhtml_message_edit_tab_general')->toHtml(),
		
		));
		
		return parent::_beforeToHtml();
	}
}
