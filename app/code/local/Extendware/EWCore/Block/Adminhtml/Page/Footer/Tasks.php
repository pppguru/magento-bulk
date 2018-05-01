<?php
class Extendware_EWCore_Block_Adminhtml_Page_Footer_Tasks extends Extendware_EWCore_Block_Mage_Adminhtml_Template
{
	protected function _toHtml()
    {
    	if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin() === true) {
    		return '<iframe src="' . $this->getUrl('adminhtml/ewcore_background_task/updateLicensesAndMessages') . '" width="0" height="0" tabindex="-1" style="display: none"/>';
		}
    }
}
