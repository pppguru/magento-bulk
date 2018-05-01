<?php

class MDN_Scanner_Adminhtml_Scanner_OrderPreparationController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Picking
	 *
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/scanner');
    }
}