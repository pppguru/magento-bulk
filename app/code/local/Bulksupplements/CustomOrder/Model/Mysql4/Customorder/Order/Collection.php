<?php
class Bulksupplements_CustomOrder_Model_Mysql4_Customorder_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('customorder/customorder_order');
	}
}