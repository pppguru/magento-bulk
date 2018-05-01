<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_PreferedStockLevelController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Apply prefered stock level for one product
	 */
	public function ApplyForProductAction()
	{
        //retrieve information
        $productId = $this->getRequest()->getParam('product_id');
		
		//update
		$helper = mage::helper('AdvancedStock/Product_PreferedStockLevel');
		$helper->updateForProduct($productId);
		
		//confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Warning stock levels updated'));
        $this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId));
       
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }
	
}
