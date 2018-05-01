<?php
class Extendware_EWCore_Block_Adminhtml_System_Message extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_headerText = $this->__('System Messages');

		$this->_removeButton('add');
	}
}
