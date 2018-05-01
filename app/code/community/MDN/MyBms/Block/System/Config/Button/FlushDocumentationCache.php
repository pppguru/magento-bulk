<?php

class MDN_MyBms_Block_System_Config_Button_FlushDocumentationCache extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getFlushCacheUrl();
        
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('MyBms')->__('Flush documentation cache'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }

    public function getFlushCacheUrl() {
        return $this->getUrl('adminhtml/MyBms_ChangeLog/FlushDocumentationCache');
    }
}