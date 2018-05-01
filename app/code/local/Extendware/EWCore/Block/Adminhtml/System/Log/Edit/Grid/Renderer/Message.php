<?php
class Extendware_EWCore_Block_Adminhtml_System_Log_Edit_Grid_Renderer_Message extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	public function render(Varien_Object $row)
    {
    	$value = nl2br($this->_getValue($row));
    	$value = '<div style="max-height: 100px; overflow:auto;">' . $value . '</div>';
    	return $value;
    }
   
}