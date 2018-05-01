<?php

class MDN_Scanner_Block_OrderPreparation_Picking extends Mage_Adminhtml_Block_Widget_Form
{
	private $_products = null;
	private $_productsArray = null;
	
	/**
	 * Return products list
	 *
	 */
	public function getProducts()
	{
		if ($this->_products == null)
		{			
			$this->_products = mage::helper('Orderpreparation/PickingList')->GetProductsSummary();
										
		}
		return $this->_products;
	}
	
	/**
	 * Return product qty
	 *
	 * @param unknown_type $productId
	 * @return unknown
	 */
	public function getProductQty($productId)
	{
		$array = $this->getProductsArray();
		
		foreach ($array as $product)
		{
			if ($product->getId() == $productId)
				return $product->getqty_to_prepare();
		}
		
		return 0;
	}
	
	/**
	 * Return products array :
	 * Key = product_id
	 * Value = qty
	 *
	 * @return unknown
	 */
	protected function getProductsArray()
	{
		if ($this->_productsArray == null)
			$this->_productsArray = mage::helper('Orderpreparation/PickingList')->GetProductsSummary();
			
		return $this->_productsArray;
	}
	
	/**
	 * Return barcodes for 1 product
	 *
	 * @param unknown_type $productId
	 */
	public function getBarcodes($productId)
	{
		return mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($productId);
	}
	
}