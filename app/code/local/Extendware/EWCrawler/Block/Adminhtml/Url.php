<?php
class Extendware_EWCrawler_Block_Adminhtml_Url extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_headerText = $this->__('Custom Url');

		$this->_updateButton('add', 'label', $this->__('Add Custom Url'));
	}
}
