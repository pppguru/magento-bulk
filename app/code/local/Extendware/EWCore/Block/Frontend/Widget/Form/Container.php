<?php
abstract class Extendware_EWCore_Block_Frontend_Widget_Form_Container extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
	public function __construct()
    {
        parent::__construct();
		$this->setTemplate('extendware/ewcore/frontend/widget/form/container.phtml');
    }
    
	public function getIntroductionHtml()
    {
    	return $this->getChildHtml('introduction');
    }
    
	public function getBeforeFormHtml()
    {
    	return $this->getChildHtml('before_form');
    }
    
	public function getAfterFormHtml()
    {
    	return $this->getChildHtml('after_form');
    }
    
	protected function _toHtml()
    {
    	// do this so adminhtml specific callback is not called in the frontend
        return '<div class="ewfwidget ewfformcontainer">' . Mage_Core_Block_Template::_toHtml() . '</div>';
    }
}
