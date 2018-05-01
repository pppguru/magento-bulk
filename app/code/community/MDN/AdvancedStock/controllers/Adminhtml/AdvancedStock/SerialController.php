<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_SerialController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * display all serials
	 *
	 */
	public function GridAction()
	{
		$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Serials'));

        $this->renderLayout();
	}
	
	/**
	 * delete serial and redirect to product page
	 *
	 */
	public function DeleteSerialAction()
	{
		$ppsId = $this->getRequest()->getParam('pps_id');
		$productId = $this->getRequest()->getParam('product_id');
		
		$obj = mage::getModel('AdvancedStock/ProductSerial')->load($ppsId);
		$obj->delete();
		
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Serial deleted')); 
		$this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }
}