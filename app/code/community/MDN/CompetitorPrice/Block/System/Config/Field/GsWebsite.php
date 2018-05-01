<?php

class MDN_CompetitorPrice_Block_System_Config_Field_GsWebsite extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

            $element->setComment('Select the channel to use to collect prices');
            $html = parent::_getElementHtml($element);


        return $html;

    }
}