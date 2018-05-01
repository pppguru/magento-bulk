<?php

class MDN_Scanner_Block_PurchaseOrder_SelectProductDelivery extends Mage_Adminhtml_Block_Widget_Form
{
	private $_order = null;
	
	/**
	 * Return purchase order
	 *
	 */
	public function getOrder()
	{
		if ($this->_order == null)
		{
			$orderId = $this->getRequest()->getParam('po_num');
			$this->_order = mage::getModel('Purchase/Order')->load($orderId);
		}
		return $this->_order;
	}

	/**
	 * Return products
	 *
	 */
	public function getProducts()
	{
		return $this->getOrder()->getProducts();
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
	
	/**
	 * Form submit url
	 *
	 * @return unknown
	 */
	public function getSubmitUrl()
	{
		return $this->getUrl('adminhtml/Scanner_PurchaseOrder/CreateDelivery');
	}
}