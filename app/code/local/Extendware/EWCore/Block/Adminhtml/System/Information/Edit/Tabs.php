<?php

class Extendware_EWCore_Block_Adminhtml_System_Information_Edit_Tabs extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('System Information'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('general', array(
			'label' => $this->__('General'),
			'content' => $this->getLayout()->createBlock('ewcore/adminhtml_system_information_edit_tab_general')->toHtml(),
		
		));
		
		if ($this->mHelper('config')->isWhiteLabeled() !== true or isset($_GET['force_show'])) {
			$this->addTab('rewrites', array(
				'label' => $this->__('Class Rewrites'),
				'url'       => $this->getUrl('*/*/rewritesGrid', array('_current'=>true)),
	            'class'     => 'ajax',
			));
		}
		$this->addTab('events', array(
			'label' => $this->__('Event Observers'),
			'url'       => $this->getUrl('*/*/eventsGrid', array('_current'=>true)),
            'class'     => 'ajax',
		));
		
		$this->addTab('php', array(
			'label' => $this->__('PHP Information'),
			'content' => $this->getLayout()->createBlock('ewcore/adminhtml_system_information_edit_tab_php')->toHtml(),
		
		));
		
		return parent::_beforeToHtml();
	}
}
