<?php

class Extendware_EWCore_Block_Varien_Data_Form_Element_Direct_Value extends Varien_Data_Form_Element_Abstract {
	public function getElementHtml()
    {
    	$html = $this->getBeforeElementHtml();
    	$html .= $this->getValue();
    	$html .= $this->getAfterElementHtml();
    	
        return $html;
    }
}
