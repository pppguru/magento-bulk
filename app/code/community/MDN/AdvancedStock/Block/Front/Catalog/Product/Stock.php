<?php

class MDN_AdvancedStock_Block_Front_Catalog_Product_Stock extends Mage_Core_Block_Template
{
	protected $_product = null;
	protected $_stocks = null;		
	
	/**
	 *
	 */
	public function getProduct()
	{
		if ($this->_product == null)
		{
			$this->_product = mage::registry('current_product');
		}
		return $this->_product;
	}

		
	/**
	 * Return stocks available for sell for current website
	 *
	 * @return unknown
	 */
	public function getStocksForSale()
	{
		if ($this->_stocks == null)
		{
			$currentWebsiteId = Mage::app()->getStore()->getwebsite_id();
			$this->_stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($currentWebsiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $this->getProduct()->getId());
		}
		return $this->_stocks;
	}
    
	/**
	 * Return available qty for every stocks
	 *
	 * @return unknown
	 */
	public function getAvailableQty()
	{
		$value = 0;
		foreach ($this->getStocksForSale() as $stock)
			$value += $stock->getAvailableQty();
		return $value;
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function manageStock()
	{
		foreach ($this->getStocksForSale() as $stock)
			return $stock->ManageStock();
			
		return false;
	}
}
