<?php
class Bulksupplements_CustomReports_Model_Productskgsummary extends Mage_Reports_Model_Mysql4_Product_Sold_Collection
{
	function __construct() {
		parent::__construct();
		$this->_useAnalyticFunction = true;
	}

	public function setDateRange($from, $to)
	{
		$this->_reset()
			->addAttributeToSelect('*')
			->addAttributeToSelect('weight_form')
			->addOrderedQty($from, $to)
//			->addAttributeToFilter('sku', array('like' => 'ASCA-500%'))
			->addAttributeToFilter('entity_type_id', array('eq' => $this->getProductEntityTypeId()))
//			->addAttributeToFilter('weight_form', array('in' => array(1, 14, 70, 52)))
			->setOrder('ordered_qty', self::SORT_ORDER_DESC);
//			->setOrder('name', self::SORT_ORDER_ASC);

		//Mage::log('SQL: '.$this->getSelect()->__toString());

		return $this;
	}

	/**
	 * Add ordered qty's
	 *
	 * @param string $from
	 * @param string $to
	 * @return Mage_Reports_Model_Resource_Product_Collection
	 */
	public function addOrderedQty($from = '', $to = '')
	{
		$adapter              = $this->getConnection();
		$compositeTypeIds     = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
		$orderTableAliasName  = $adapter->quoteIdentifier('order');

		$orderJoinCondition   = array(
			$orderTableAliasName . '.entity_id = order_items.order_id',
			$adapter->quoteInto("{$orderTableAliasName}.state <> ?", Mage_Sales_Model_Order::STATE_CANCELED),

		);

		$productJoinCondition = array(
			$adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
			'e.entity_id = order_items.product_id',
			$adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId()),
		);

		if ($from != '' && $to != '') {
			$fieldName            = $orderTableAliasName . '.created_at';
			$orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
		}

		$this->getSelect()->reset()
			->from(
				array('order_items' => $this->getTable('sales/order_item')),
				array(
					'ordered_qty' => 'SUM(order_items.qty_ordered)',
					'order_items_name' => 'order_items.name',
				))
			->joinInner(
				array('order' => $this->getTable('sales/order')),
				implode(' AND ', $orderJoinCondition),
				array())
			->joinLeft(
				array('e' => $this->getProductEntityTableName()),
				implode(' AND ', $productJoinCondition),
				array(
					'entity_id' => 'order_items.product_id',
					'entity_type_id' => 'e.entity_type_id',
					'attribute_set_id' => 'e.attribute_set_id',
					'type_id' => 'e.type_id',
					'sku' => 'e.sku',
					'has_options' => 'e.has_options',
					'required_options' => 'e.required_options',
					'created_at' => 'e.created_at',
					'updated_at' => 'e.updated_at'
				))
			->where('e.type_id = ?', 'simple')
			->group('order_items.product_id')
			->having('SUM(order_items.qty_ordered) > ?', 0);

		return $this;
	}

	/**
	 * Add ordered qty's
	 *
	 * @param string $from
	 * @param string $to
	 * @return Mage_Reports_Model_Resource_Product_Collection
	 */
	public function addOrderedQty1($from = '', $to = '')
	{
		$adapter              = $this->getConnection();
		$compositeTypeIds     = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
		$orderTableAliasName  = $adapter->quoteIdentifier('order');
		$addressTableAliasName  = 'a';

		$orderJoinCondition   = array(
				$orderTableAliasName . '.entity_id = order_items.order_id',
				$adapter->quoteInto("{$orderTableAliasName}.state = ?", Mage_Sales_Model_Order::STATE_PROCESSING),

		);

		$addressJoinCondition = array(
				$addressTableAliasName . '.entity_id = order.shipping_address_id'
		);

		$productJoinCondition = array(
				//$adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
				'e.entity_id = order_items.product_id',
				$adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
		);

		if ($from != '' && $to != '') {
			$fieldName            = $orderTableAliasName . '.created_at';
			$orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
		}

		$this->getSelect()->reset()
		->from(
				array('order_items' => $this->getTable('sales/order_item')),
				array(
						'qty_ordered' => 'order_items.qty_ordered',
						'order_items_name' => 'order_items.name',
						'order_increment_id' => 'order.increment_id',
						'sku' => 'order_items.sku',
						'type_id' => 'order_items.product_type',
						'shipping_address_id' => 'order.shipping_address_id'
				))
				->joinInner(
						array('order' => $this->getTable('sales/order')),
						implode(' AND ', $orderJoinCondition),
						array())
						->joinLeft(
								array('a' => $this->getTable('sales/order_address')),
								implode(' AND ', $addressJoinCondition),
								array(
										'shipto_name' => "CONCAT(COALESCE(a.firstname, ''), ' ', COALESCE(a.lastname, ''))"
								),
						array())

						->joinLeft(
								array('e' => $this->getProductEntityTableName()),
								implode(' AND ', $productJoinCondition),
								array(
										'created_at' => 'e.created_at',
										'updated_at' => 'e.updated_at'
								))

						->joinLeft(
								array('p' => 'catalog_product_flat_1'),
								'p.sku = order_items.sku',
								array(
										'size' => 'COALESCE(p.size, p.bra_size, p.shoe_size)'
								))

						->joinLeft(
								array('av' => 'eav_attribute_option_value'),
								'p.size = av.option_id',
								array(
										'size_label' => 'av.value'
								))
						->where('parent_item_id IS NULL')
						//->group('order_items.product_id')
						->having('order_items.qty_ordered > ?', 0);
		return $this;
	}

	/**
	 * Adding item to item array
	 *
	 * @param   Varien_Object $item
	 * @return  Varien_Data_Collection
	 */
	public function addItem1(Varien_Object $item)
	{
		$itemId = $this->_getItemId($item);

		if (!is_null($itemId)) {
			if (isset($this->_items[$itemId])) {
				// Unnecessary exception - http://www.magentocommerce.com/boards/viewthread/10634/P0/
				//throw new Exception('Item ('.get_class($item).') with the same id "'.$item->getId().'" already exist');
			}
			$this->_items[$itemId] = $item;
		} else {
			$this->_items[] = $item;
		}
		return $this;
	}
}
