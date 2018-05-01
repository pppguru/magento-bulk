<?php


/**
 * 
 *
 */
class MDN_Orderpreparation_Model_OrderToPreparePending  extends Mage_Core_Model_Abstract
{
	private $_order = null;
	
	/*****************************************************************************************************************************
	* ***************************************************************************************************************************
	* Constructeur
	*
	*/
	public function _construct()
	{
		parent::_construct();
		$this->_init('Orderpreparation/ordertopreparepending');
	}
	
	/**
	 * return associated sales order
	 *
	 * @return unknown
	 */
	public function getOrder()
	{
		if ($this->_order == null)
		{
			$orderId = $this->getopp_order_id();
			$this->_order = mage::getModel('sales/order')->load($orderId);
		}
		return $this->_order;
	}
	
	/**
	 * Return value to sort in list
	 *
	 */
	public function calculateSortValue($salesOrder)
	{
		return strtotime($salesOrder->getcreated_at());
	}
	
}