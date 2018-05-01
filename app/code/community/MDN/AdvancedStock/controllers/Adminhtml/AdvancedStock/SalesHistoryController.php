<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_SalesHistoryController extends Mage_Adminhtml_Controller_Action
{

	/**
	 * Update stats for all products
	 */
	public function UpdateForAllProductsAction()
	{
		$helper = mage::helper('AdvancedStock/Sales_History');
		$helper->updateForAllProducts();
	}
	
	/**
	 * Update stats for one product
	 */
	public function RefreshForProductAction()
	{
		$productId = $this->getRequest()->getParam('product_id');

		mage::helper('AdvancedStock/Sales_History')->RefreshForOneProduct($productId);
		
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Sales History Updated')); 
		$this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId, 'tab' => 'tab_history'));
	}

	public function RefreshForProductWarehouseAction()
	{
		$productId = $this->getRequest()->getParam('product_id');
		$stockId = $this->getRequest()->getParam('stock_id');

		mage::helper('AdvancedStock/Sales_History')->RefreshForOneProduct($productId,$stockId);

		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Sales History Updated'));
		$this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId, 'tab' => 'tab_history'));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }
	
}