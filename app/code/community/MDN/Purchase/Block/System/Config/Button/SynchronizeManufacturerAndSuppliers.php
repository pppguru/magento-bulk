<?php

class MDN_Purchase_Block_System_Config_Button_SynchronizeManufacturerAndSuppliers extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/Purchase_Suppliers/SynchronizeWithManufacturers');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('purchase')->__('Synchronize Now'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}