<?php

class MDN_Organizer_Block_Widget_Column_Renderer_Entity
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$html = '<a href="'.$row->getEntityLink().'">'.$row->getot_entity_description().'</a>';
    	return $html;
    }
    
}