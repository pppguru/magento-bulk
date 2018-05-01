<?php

class MDN_Scanner_Block_PurchaseOrder_SupplierSelection extends Mage_Adminhtml_Block_Widget_Form
{
	private $_suppliers = null;

	/**
	 * Return suppliers collection
	 *
	 * @return unknown
	 */
	public function getSuppliers()
	{
		if ($this->_suppliers == null)
		{
			$this->_suppliers = mage::getModel('Purchase/Supplier')
									->getCollection()
									->setOrder('sup_name', 'asc');
		}
		return $this->_suppliers;	
	}
	
	public function getFreeDeliveryUrl()
	{
		return $this->getUrl('adminhtml/Scanner_Inventory/FreeDelivery');
	}
	
}