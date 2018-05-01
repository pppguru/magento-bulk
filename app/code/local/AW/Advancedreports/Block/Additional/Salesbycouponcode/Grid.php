<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_ARUnits/Salesbycouponcode
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */?>
<?php
/**
 * Sales by Coupon Code Report Grid
 */
class AW_Advancedreports_Block_Additional_Salesbycouponcode_Grid extends AW_Advancedreports_Block_Additional_Grid
{
	/**
	 * Route to get config from helper
	 * @var string
	 */
	protected $_routeOption = AW_Advancedreports_Helper_Additional_Salesbycouponcode::ROUTE_ADDITIONAL_SALESBYCOUPONCODE;

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate( $this->_helper()->getGridTemplate() );
		$this->setExportVisibility(true);
		$this->setStoreSwitcherVisibility(true);
		$this->setId('gridAdditionalSalesbycouponcode');

		# Set default sorting
		if (!$this->getRequest()->getParam('sort')){
			$this->getRequest()->setParam('sort', 'qty_ordered_count');
			$this->getRequest()->setParam('dir', 'desc');
		}
	}

	public function hasRecords()
	{
		return false;
	}

	public function getHideShowBy()
	{
		return true;
	}

	public function _prepareCollection()
	{
		parent::_prepareCollection();

		/** @var AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbycouponcode $collection  */
		$collection = Mage::getResourceModel('advancedreports/collection_additional_salesbycouponcode');
		$this->setCollection( $collection );
		$this->_prepareAbstractCollection();

		$collection->addOrderItemsCount()
				   ->addCouponCode();

		$this->_prepareData();
	}

	protected function _addCustomData($row)
	{
		if ( count( $this->_customData ) )
		{
			foreach ( $this->_customData as &$d )
			{
				if ( $d['title'] == $row['title'] )
				{
					#Orders
					  $d['orders_count'] ++;

					# Qty
					$qty_ordered_count = $d['qty_ordered_count'] + $row['qty_ordered_count'];
					$d['qty_ordered_count'] = $qty_ordered_count;

					# Subtotal
					$base_row_subtotal = $d['base_row_subtotal'] + $row['base_row_subtotal'];
					$d['base_row_subtotal'] = $base_row_subtotal;

					# Subtotal
					$base_row_subtotal = $d['base_subtotal'] + $row['base_subtotal'];
					$d['base_subtotal'] = $base_row_subtotal;

					# Tax
					$base_tax_amount = $d['base_tax_amount'] + $row['base_tax_amount'];
					$d['base_tax_amount'] = $base_tax_amount;

					# Shipping
					$base_shippng_amount = $d['base_shipping_amount'] + $row['base_shipping_amount'];
					$d['base_shipping_amount'] = $base_shippng_amount;

					# Discounts
					$base_discount_amount = $d['base_discount_amount'] + $row['base_discount_amount'];
					$d['base_discount_amount'] = $base_discount_amount;

					# Total
					$base_row_total = $d['base_grand_total'] + $row['base_grand_total'];
					$d['base_grand_total'] = $base_row_total;

					# Invoiced
					$base_row_invoiced = $d['base_total_invoiced'] + $row['base_total_invoiced'];
					$d['base_total_invoiced'] = $base_row_invoiced;

					# Refunded
					$base_amount_refunded = $d['base_total_refunded'] + $row['base_total_refunded'];
					$d['base_total_refunded'] = $base_amount_refunded;

					$this->_last_o_id = $this->_cur_o_id;
					return ;
				}
			}
		}
		$this->_customData[] = $row;
		return $this;
	}

	protected function _prepareData()
	{
		$isFirst = true;

		// Matthew : start
		$collection = $this->getCollection();
		$collection->setPageSize(1000);

		$pages = $collection->getLastPageNumber();
		$currentPage = 1;

		do {
			$collection->setCurPage($currentPage);
			$collection->load();
		// end
			foreach ($collection as $row)
			{
				$this->_cur_o_id = $row->getOrderId();
				if ($isFirst){
					$this->_last_o_id = $this->_cur_o_id;
					$isFirst = false;
				}

				$row->setBaseRowSubtotal( $row->getBaseRowTotal() );
				$row->setBaseRowTotal( $row->getBaseRowTotal() - $row->getBaseTaxAmount() - $row->getBaseDiscountAmount() );
				if ($row->getBaseRowTotal() < 0){
					$row->setBaseRowTotal(0);
				}
				if ($row->getBaseRowInvoiced() > $row->getBaseRowTotal()){
					$row->setBaseRowInvoiced( $row->getBaseRowTotal() );
				}

				if ($row->getBaseRowRefunded()){
					if ($row->getBaseRowRefunded() > $row->getBaseRowTotal()){
						$row->setBaseRowRefunded( $row->getBaseRowTotal() );
					}
				} else {
					if ($row->getBaseAmountRefunded() > $row->getBaseRowTotal()){
						$row->setBaseRowRefunded( $row->getBaseRowTotal() );
					} else {
						$row->setBaseRowRefunded( $row->getBaseAmountRefunded() );
					}
				}

				$row->setOrdersCount(1);

				if (!$row->getCouponCode())
				{
					$row->setCouponCode($this->_helper()->__('Not set'));
				} else {
					$row->setCouponCode(strtoupper($row->getCouponCode()));
				}

				$row->setTitle( $row->getCouponCode() );
				$this->_addCustomData($row->getData());
			}

			// Matthew : start
			$currentPage++;
			//clear collection and free memory
			$collection->clear();
		} while ($currentPage <= $pages);
		// end;

		parent::_prepareData();
		return $this;
	}

	protected function _prepareColumns()
	{
		$def_value = sprintf("%f", 0);
		$def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);

		$this->addColumn('coupon_code', array(
			'header'    =>$this->_helper()->__('Coupon Code'),
			'index'     =>'coupon_code',
			'type'      =>'text',
			'width'     =>'100px',
		));

		$this->addColumn('orders_count', array(
			'header'    =>$this->_helper()->__('Orders'),
			'width'     =>'60px',
			'index'     =>'orders_count',
			'total'     =>'sum',
			'type'      =>'number'
		));

		$this->addColumn('qty_ordered_count', array(
			'header'    =>$this->_helper()->__('Items'),
			'width'     =>'60px',
			'index'     =>'qty_ordered_count',
			'total'     =>'sum',
			'type'      =>'number'
		));

		$this->addColumn('base_subtotal', array(
			'header'    =>$this->_helper()->__('Subtotal'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_subtotal',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addColumn('base_tax_amount', array(
			'header'    =>$this->_helper()->__('Tax'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_tax_amount',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addColumn('base_shipping_amount', array(
			'header'    =>$this->_helper()->__('Shipping'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_shipping_amount',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addColumn('base_discount_amount', array(
			'header'    =>$this->_helper()->__('Discounts'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_discount_amount',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addColumn('base_grand_total', array(
			'header'    =>$this->_helper()->__('Total'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_grand_total',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addColumn('base_total_invoiced', array(
			'header'    =>$this->_helper()->__('Invoiced'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_total_invoiced',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addColumn('base_total_refunded', array(
			'header'    =>$this->_helper()->__('Refunded'),
			'width'     =>'80px',
			'type'      =>'currency',
			'currency_code' => $this->getCurrentCurrencyCode(),
			'total'     =>'sum',
			'index'     =>'base_total_refunded',
			'column_css_class' => 'nowrap',
			'default'  => $def_value,
		));

		$this->addExportType('*/*/exportOrderedCsv/name/'.$this->_getName(), $this->_helper()->__('CSV'));
		$this->addExportType('*/*/exportOrderedExcel/name/'.$this->_getName(), $this->_helper()->__('Excel'));

		return $this;
	}
}
