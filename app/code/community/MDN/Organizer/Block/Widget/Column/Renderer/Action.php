<?php

class MDN_Organizer_Block_Widget_Column_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	
    public function render(Varien_Object $row)
    {
    	$url = 'javascript:editTask('.$row->getId().', '.$this->getColumn()->getguid().');';
    	$html = '<a href="'.$url.'">'.mage::helper('Organizer')->__('Show/Hide').'</a>';
    	return $html;
    }
    
}
