<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_ARUnits_Salesstatistics
 * @copyright  Copyright (c) 2009-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

class AW_Advancedreports_Block_Additional_Salesstatistics_Grid extends AW_Advancedreports_Block_Additional_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Additional_Salesstatistics::ROUTE_ADDITIONAL_SALESSTATISTICS;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridSalesstatistics');
    }

    public function getHideShowBy()
    {
        return false;
    }

    protected function _addCustomData($row)
    {
        $this->_customData[] = $row;
        return $this;
    }

    public function _prepareCollection()
    {
        parent::_prepareOlderCollection();
        # This calculate collection of intervals
        $this->getCollection()
            ->initReport('reports/product_ordered_collection');
        $this->_prepareData();
        return $this;
    }


    protected function _getItemStatistics($from, $to)
    {
        $collection = Mage::getModel('sales/order')->getCollection();

        /** $collection @var AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesstatistics */
        $collection = Mage::getResourceModel('advancedreports/collection_additional_salesstatistics');

        $collection->reInitItemSelect();

        $collection->setDateFilter($from, $to)->setState();
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }
        $collection->addItems();

        if (count($collection)) {
            foreach ($collection as $item) {
                return $item;
            }
        }
        return new Varien_Object();
    }

    protected function _getOrderStatistics($from, $to)
    {
        /** @var AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesstatistics $collection  */
        $collection = Mage::getResourceModel('advancedreports/collection_additional_salesstatistics');

        $collection->reInitOrderSelect();

        $collection->setDateFilter($from, $to)->setState();
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }

        if (count($collection)) {
            foreach ($collection as $item) {
                return $item;
            }
        }
        return new Varien_Object();
    }

    protected function _prepareData()
    {
        # primary analise
        foreach ($this->getCollection()->getIntervals() as $_index => $_item) {
            $row = $this->_getOrderStatistics($_item['start'], $_item['end']);
            $item = $this->_getItemStatistics($_item['start'], $_item['end']);
            $row->setData('items_count', max(intval($item->getData('items_count')), 0));
            $row->setData('items_invoiced', max(intval($item->getData('items_invoiced')), 0));

            $row->setPeriod($_item['title']);

            if (!$row->getOrdersCount()) {
                $row->setOrdersCount(0);
            }
            if ($row->getOrdersCount()) {
                $row->setAvgOrderAmount($row->getBaseTotalInvoiced() / $row->getOrdersCount());
            } else {
                $row->setAvgOrderAmount(0);
            }
            if ($row->getData('items_invoiced')) {
                $row->setAvgItemCost($row->getBaseTotalInvoiced() / $row->getData('items_invoiced'));
            } else {
                $row->setAvgItemCost(0);
            }

            $this->_addCustomData($row->getData());
        }

        $chartLabels = array(
            'avg_order_amount' => $this->_helper()->__('Order Amount(Avg)'),
            'avg_item_cost' => $this->_helper()->__('Item Price(Avg)')
        );
        $keys = array();
        foreach ($chartLabels as $key => $value) {
            $keys[] = $key;
        }

        $this->_helper()->setChartData($this->_customData, $this->_helper()->getDataKey($this->_routeOption));
        $this->_helper()->setChartKeys($keys, $this->_helper()->getDataKey($this->_routeOption));
        $this->_helper()->setChartLabels($chartLabels, $this->_helper()->getDataKey($this->_routeOption));
        parent::_prepareData();
        return $this;
    }

    protected function _prepareColumns()
    {
        $def_value = sprintf("%f", 0);
        $def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);

        $this->addColumn('periods', array(
            'header' => $this->getPeriodText(),
            'width' => '120px',
            'index' => 'period',
            'type' => 'text',
            'sortable' => false,
        ));

        $this->addColumn('orders_count', array(
            'header' => $this->_helper()->__('Orders'),
            'width' => '60px',
            'index' => 'orders_count',
            'total' => 'sum',
            'type' => 'number'
        ));

        $this->addColumn('items_count', array(
            'header' => $this->_helper()->__('Items'),
            'width' => '60px',
            'index' => 'items_count',
            'total' => 'sum',
            'type' => 'number'
        ));

        $this->addColumn('base_subtotal', array(
            'header' => $this->_helper()->__('Subtotal'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_subtotal',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
        ));

        $this->addColumn('base_tax_amount', array(
            'header' => $this->_helper()->__('Tax'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_tax_amount',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
        ));

        $this->addColumn('base_discount_amount', array(
            'header' => $this->_helper()->__('Discounts'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_discount_amount',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
        ));

        $this->addColumn('base_grand_total', array(
            'header' => $this->_helper()->__('Total'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_grand_total',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
        ));

        $this->addColumn('base_total_invoiced', array(
            'header' => $this->_helper()->__('Invoiced'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_total_invoiced',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
        ));

        $this->addColumn('base_total_refunded', array(
            'header' => $this->_helper()->__('Refunded'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_total_refunded',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
        ));

        $this->addColumn('avg_order_amount', array(
            'header' => $this->_helper()->__('Order Amount(Avg)'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'avg_order_amount',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'disable_total' => 1,
        ));

        $this->addColumn('avg_item_cost', array(
            'header' => $this->_helper()->__('Item Final Price(Avg)'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'avg_item_cost',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'disable_total' => 1,
        ));

        $this->addExportType('*/*/exportOrderedCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', $this->_helper()->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_MULTY_LINE;
    }

    public function getPeriods()
    {
        return parent::_getOlderPeriods();
    }
}
