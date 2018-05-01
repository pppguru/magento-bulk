<?php

class Extendware_EWCore_Block_Adminhtml_Message_Grid_Renderer_Severity extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$severity = $row->getSeverity();
		$class = strtolower($severity);
		$value = $severity;
		
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
