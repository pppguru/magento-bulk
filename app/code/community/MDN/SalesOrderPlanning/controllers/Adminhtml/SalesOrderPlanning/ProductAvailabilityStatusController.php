<?php

class MDN_SalesOrderPlanning_Adminhtml_SalesOrderPlanning_ProductAvailabilityStatusController extends Mage_Adminhtml_Controller_Action
{
	
    /**
     * Display grid
     *
     */
	public function GridAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Product Availability Statuses'));

        $this->renderLayout();
    }
    
    /**
     * Refresh the product availibility sttaus of all product
     *
     */
    public function RefreshAllAction()
    {		
    	mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshAll();
    }
    
	/**
     * Refresh the product availibility sttaus of the product that doe snot have any availability status
     *
     */
	public function RefreshOnlyMissingAction()
	{
    	mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshOnlyMissing();	
	}

    /**
     * Refresh the product availibility stataus of the product selected in the grid
     *
     */
    public function RefreshOnlySelectedAction()
	{
        $products = $this->getRequest()->getParam('product');
    	mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshOnlySelected($products);
	}
    
    /**
     * Refresh one product
     *
     */
    public function RefreshProductAction()
    {
    	try 
    	{
    		$productId = $this->getRequest()->getParam('product_id');
    		mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshForOneProduct($productId);
    		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Availability Status Refreshed'));
    	}
    	catch (Exception $ex)
    	{
    		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : ').$ex->getMessage());
    	}
    	$this->_redirect('adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/Grid/');
    }
    
    public function RefreshProductAndGoBackToProductSheetAction()
    {
    	try 
    	{
    		$productId = $this->getRequest()->getParam('product_id');
    		mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshForOneProduct($productId);
    		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Availability Status Refreshed'));
    	}
    	catch (Exception $ex)
    	{
    		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : ').$ex->getMessage());
    	}
    	$this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId, 'tab' => 'tab_availability_status'));
    	
    }

	public function MassDeleteAction()
	{
		$products = $this->getRequest()->getParam('product');

		$collection = Mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')
			->getCollection()
			->addFieldToFilter('pa_id', array('in' => $products));
		foreach($collection as $item)
		{
			$item->delete();
		}

		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Items deleted'));
		$this->_redirect('adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/Grid');
	}

	public function ForceInStockAction()
	{
		$products = $this->getRequest()->getParam('product');

		$collection = Mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')
			->getCollection()
			->addFieldToFilter('pa_id', array('in' => $products));
		foreach($collection as $item)
		{
			$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getpa_product_id());
			$stockItem->setis_in_stock(1)->save();
		}

		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Stock status updated'));
		$this->_redirect('adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/Grid');
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/tools/product_availability_status');
    }

}