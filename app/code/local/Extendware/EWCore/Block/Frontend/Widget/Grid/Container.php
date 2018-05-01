<?php
class Extendware_EWCore_Block_Frontend_Widget_Grid_Container extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('extendware/ewcore/frontend/widget/grid/container.phtml');
    }

	public function getIntroductionHtml()
    {
    	return $this->getChildHtml('introduction');
    }
    
	public function getBeforeGridHtml()
    {
    	return $this->getChildHtml('before_grid');
    }
    
	public function getAfterGridHtml()
    {
    	return $this->getChildHtml('after_grid');
    }
    
	protected function _toHtml()
    {
    	// do this so adminhtml specific callback is not called in the frontend
        return '<div class="ewfwidget ewfgridcontainer">' . Mage_Core_Block_Template::_toHtml() . '</div>';
    }
}
