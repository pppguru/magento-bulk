<?php

class MDN_AdvancedStock_Block_System_Config_Button_UpdateSalesHistoryForAllProducts extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/AdvancedStock_SalesHistory/UpdateForAllProducts');
        
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('AdvancedStock')->__('Update now'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}