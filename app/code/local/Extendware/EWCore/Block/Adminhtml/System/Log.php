<?php
class Extendware_EWCore_Block_Adminhtml_System_Log extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_headerText = $this->__('Logs');
		$this->_removeButton('add');
	}
}
