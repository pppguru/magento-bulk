<?php

class MDN_SalesOrderPlanning_Adminhtml_SalesOrderPlanning_LateOrdersController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Late orders'));

		$this->renderLayout();
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/late_orders');
    }
}