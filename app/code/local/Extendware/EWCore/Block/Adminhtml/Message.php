<?php
class Extendware_EWCore_Block_Adminhtml_Message extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_headerText = $this->__('Messages');

		$this->_updateButton('add', 'label', $this->__('Update Messages'));
	}
	
	public function getCreateUrl()
    {
        return $this->getUrl('*/*/update');
    }
}
