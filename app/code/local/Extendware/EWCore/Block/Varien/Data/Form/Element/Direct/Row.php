<?php

class Extendware_EWCore_Block_Varien_Data_Form_Element_Direct_Row extends Varien_Data_Form_Element_Abstract {
	public function getHtml()
    {
        return '<tr><td colspan="2">' . $this->getValue() . '</td></tr>';
    }
}
