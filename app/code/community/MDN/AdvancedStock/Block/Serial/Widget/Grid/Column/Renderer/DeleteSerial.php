<?php

class MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_DeleteSerial
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$url = $this->getUrl('adminhtml/AdvancedStock_Serial/DeleteSerial', array('pps_id' => $row->getId(), 'product_id' => $row->getpps_product_id()));
		return '<a href="'.$url.'">'.$this->__('Delete').'</a>';
    }
    
}