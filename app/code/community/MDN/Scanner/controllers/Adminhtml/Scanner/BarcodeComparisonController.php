<?php

class MDN_Scanner_Adminhtml_Scanner_BarcodeComparisonController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * 
	 *
	 */
	public function IndexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/scanner');
    }
}