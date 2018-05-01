<?php

class MDN_CompetitorPrice_Block_System_Config_Explanations extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = array();

        $html[] = $this->__('To use the Google Shopping prices feature in ERP, you need to enter your credentials.');
        $html[] = $this->__('You can retrieve your credentials in your customer account on boostmyshop.com, in the credits tab');

        return implode('<br>', $html);
    }
}