<?php

class MDN_Purchase_Block_Widget_Column_Renderer_ProductSerial_DeleteSerial
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$url = $this->getUrl('adminhtml/Purchase_Products/DeleteSerial', array('pps_id' => $row->getId(), 'product_id' => $row->getpps_product_id()));
    	$retour = '<a href="'.$url.'">'.$this->__('Delete').'</a>';
		return $retour;
    }
    
}