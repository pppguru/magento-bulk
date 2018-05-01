<?php
class Bulksupplements_CustomReports_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getSkuByCustomerCollection($sku, $fromDate, $toDate)
	{
		$collection = null;
		if($sku != ''){
			/* Format our dates */
			$fDate = date('Y-m-d H:i:s', strtotime($fromDate));
			$tDate = date('Y-m-d H:i:s', strtotime($toDate.' 23:59:59'));
			$collection = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('main_table.created_at', array('from'=>$fDate, 'to'=>$tDate));
			$collection->getSelect()
				->reset('columns')
				->columns('entity_id as order_id')
				->columns('customer_id')
				->columns('customer_firstname as first_name')
				->columns('customer_middlename as middle_name')
				->columns('customer_lastname as last_name')
				->columns('customer_email as email')
				->columns('created_at');
			$collection->getSelect()
				->joinInner(array('sfoi'=>'sales_flat_order_item'), 'main_table.entity_id=sfoi.order_id', array('sfoi.sku', 'FORMAT(sfoi.qty_ordered,0) as qty',))
				->joinInner(array('sfoa' => 'sales_flat_order_address'), 'main_table.billing_address_id=sfoa.entity_id', array('sfoa.company', 'sfoa.street', 'sfoa.city', 'sfoa.region', 'sfoa.postcode as zip', 'sfoa.telephone'))
				->where('sfoi.sku = ?', $sku);

			//$query = $collection->getSelect()->__toString();
		}
		return $collection;
	}

	public function getStoreId() {
		$website_id = Mage::app()->getRequest()->getParam('website');
		$group_id = Mage::app()->getRequest()->getParam('group');
		$store_id = Mage::app()->getRequest()->getParam('store');
		if ($website_id) {
			$store_ids = Mage::getModel('core/website')->load($website_id)->getStoreIds();
		} elseif ($group_id) {
			$store_ids = array();
			$collection = Mage::getModel('core/store')->getCollection()->addFieldToFilter('group_id', $group_id);
			foreach ($collection as $store) {
				$store_ids[] = $store->getId();
			}
		} elseif ($store_id) {
			$store_ids = $store_id;
		} else {
			return 'all';
		}

		return $store_ids;
	}
}
