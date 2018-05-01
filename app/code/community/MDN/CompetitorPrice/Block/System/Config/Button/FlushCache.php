<?php

class MDN_CompetitorPrice_Block_System_Config_Button_FlushCache extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/CompetitorPrice_Admin/FlushCache');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel(Mage::helper('CompetitorPrice')->__('Flush'))
            ->setOnClick("setLocation('$url')")
            ->toHtml();

        return $html;
    }
}