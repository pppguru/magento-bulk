<?php
class Extendware_EWCore_Block_Frontend_Widget_Form_Renderer_Fieldset extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset
{
    protected function _construct()
    {
    	parent::_construct();
        $this->setTemplate('extendware/ewcore/frontend/widget/form/renderer/fieldset.phtml');
    }
    
	protected function _toHtml()
    {
    	// do this so adminhtml specific callback is not called in the frontend
        return Mage_Core_Block_Template::_toHtml();
    }
}
