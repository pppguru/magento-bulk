<?php

class Extendware_EWCore_Block_Varien_Data_Form extends Varien_Data_Form {

	public function addFieldset($elementId, $config, $after = false)
    {
        $element = new Extendware_EWCore_Block_Varien_Data_Form_Element_Fieldset($config);
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }
}