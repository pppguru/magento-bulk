<?php

class MDN_Organizer_Block_Widget_Column_Renderer_Misc
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$html = '';
    	if ($row->isLate())
    		$html .= '<img src="'.$this->getSkinUrl('images/Organizer/alert.gif').'" alt="Late"> ';
    	if ($row->isFinished())
    		$html .= '<img src="'.$this->getSkinUrl('images/Organizer/finished.gif').'" alt="Finished"> ';
   		/*
    	if ($row->getot_read() == 0)
    		$html .= '<img src="'.$this->getSkinUrl('images/Organizer/new.gif').'" alt="New"> ';
    	*/
   		
    	return $html;
    }
    
}