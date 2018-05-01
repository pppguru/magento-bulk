<?php
class Extendware_EWCrawler_Block_Adminhtml_Job extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_headerText = $this->__('Job');

		$this->_updateButton('add', 'label', $this->__('Add Job'));
		$this->_updateButton('add', 'onclick', 'confirmSetLocation(\'' . $this->__('Adding a new queued job will delete any existing queued jobs.') . '\', \'' . $this->getUrl('*/*/new') . '\');');
	}
}
