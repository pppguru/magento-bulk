<?php
class Extendware_EWCore_Block_Frontend_Widget_Grid_Massaction extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Massaction
{
	public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setTemplate('extendware/ewcore/frontend/widget/grid/massaction.phtml');
        $this->setUseSelectAll(false);
    }
    
	protected function _toHtml()
    {
    	// do this so adminhtml specific callback is not called in the frontend
        return '<div class="ewfwidget ewfgridmassaction">' . Mage_Core_Block_Template::_toHtml() . '</div>';
    }
}
