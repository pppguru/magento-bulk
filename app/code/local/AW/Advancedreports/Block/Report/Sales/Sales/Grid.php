<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Block_Report_Sales_Sales_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_SALES_SALES;
    protected $_reportCollections = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridStandardsales');
    }

    public function getRoute()
    {
        return $this->_routeOption;
    }

    public function hasRecords()
    {
        return (count($this->getCollection()->getIntervals()) > 1)
            && $this->_helper()->getChartParams($this->_routeOption)
            && count($this->_helper()->getChartParams($this->_routeOption))
        ;
    }

    public function getChartParams()
    {
        return $this->_helper()->getChartParams($this->_routeOption);
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        return $html;
    }

    public function getHideShowBy()
    {
        return false;
    }

    public function getHideNativeGrid()
    {
        return false;
    }

    public function getShowCustomGrid()
    {
        return false;
    }

    protected function _addCustomData($row)
    {
        if (!isset($row['items'])) {
            $row['items'] = 0;
        }
        if (!isset($row['orders'])) {
            $row['orders'] = 0;
        }
        $this->_customData[] = $row;
        return $this;
    }

    public function _prepareCollection()
    {
        parent::_prepareOlderCollection();
        # This calculate collection of intervals
        $this->getCollection()->initReport('reports/product_ordered_collection');
        $this->_prepareData();
        return $this;
    }

    protected function _getItemStatistics($from, $to)
    {
        /** @var AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales $collection */
        $collection = Mage::getResourceModel('advancedreports/collection_standard_sales');
        $collection->reInitSelect()
            ->addItems()
            ->setState()
            ->setDateFilter($from, $to)
        ;

        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }

        $items = new Varien_Object(array('items_count' => 0));

        if (count($collection)) {
            foreach ($collection as $item) {
                $items->setItemsCount($items->getItemsCount() + $item->getItemsCount());
            }
        }
        return $items;
    }

    public function getReport($from, $to)
    {
        $key = $from . ' - ' . $to;
        if (isset($this->_reportCollections[$key])) {
            return $this->_reportCollections[$key];
        }

        /** @var AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales $collection */
        $collection = Mage::getResourceModel('advancedreports/collection_standard_sales');
        $collection->reInitSelect()
            ->addSumColumns()
            ->addGroupByIntOne()
            ->setState()
            ->setDateFilter($from, $to)
        ;

        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }

        if (count($collection)) {
            foreach ($collection as $item) {
                if ($items = $this->_getItemStatistics($from, $to)) {
                    $item->setItems($items->getItemsCount());
                }
            }
        }
        $this->_reportCollections[$key] = $collection;
        return $collection;
    }

    protected function _prepareData()
    {
        //Remember available keys
        $keys = array();
        foreach ($this->getChartParams() as $param) {
            $keys[] = $param['value'];
        }

        $dataKeys = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem() && in_array($column->getIndex(), $keys)) {
                $dataKeys[] = $column->getIndex();
            }
        }
        //Get data
        $data = array();
        foreach ($this->getCollection()->getIntervals() as $_index => $_item) {
            $report = $this->getReport($_item['start'], $_item['end']);
            $row = array();
            foreach ($report as $_subIndex => $_subItem) {
                $row = array();
                foreach ($this->_columns as $column) {
                    $row[$column->getIndex()] = $_subItem->getData($column->getIndex());
                }
            }

            $row['period'] = $_index;
            $data[] = $row;
            $this->_addCustomData($row);
        }
        if ($data) {
            $this->_helper()->setChartData($data, $this->_helper()->getDataKey($this->_routeOption));
        }
        parent::_prepareData();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'orders',
            array(
                'header' => Mage::helper('reports')->__('Number of Orders'),
                'index'  => 'orders',
                'total'  => 'sum',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'items',
            array(
                'header' => Mage::helper('reports')->__('Items Ordered'),
                'index'  => 'items',
                'total'  => 'sum',
                'type'   => 'number',
            )
        );

        $currencyCode = $this->getCurrentCurrencyCode();
        $this->addColumn(
            'subtotal',
            array(
                'header'        => Mage::helper('reports')->__('Subtotal'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'subtotal',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addColumn(
            'tax',
            array(
                'header'        => Mage::helper('reports')->__('Tax'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'tax',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addColumn(
            'shipping',
            array(
                'header'        => Mage::helper('reports')->__('Shipping'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'shipping',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addColumn(
            'discount',
            array(
                'header'        => Mage::helper('reports')->__('Discounts'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'discount',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addColumn(
            'total',
            array(
                'header'        => Mage::helper('reports')->__('Total'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'total',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addColumn(
            'invoiced',
            array(
                'header'        => Mage::helper('reports')->__('Invoiced'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'invoiced',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addColumn(
            'refunded',
            array(
                'header'        => Mage::helper('reports')->__('Refunded'),
                'type'          => 'currency',
                'currency_code' => $currencyCode,
                'index'         => 'refunded',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            )
        );

        $this->addExportType('*/*/exportStandardsalesCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportStandardsalesExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_LINE;
    }

    public function getPeriods()
    {
        return parent::_getOlderPeriods();
    }

    protected function _beforeExport()
    {
        $this->addColumn(
            'periods',
            array(
                'header' => $this->getPeriodText(),
                'width'  => '120px',
                'index'  => 'period',
                'type'   => 'text',
            )
        );
    }

    public function getExcel($filename = '')
    {
        $this->_beforeExport();
        return parent::getExcel($filename);
    }

    public function getCsv($filename = '')
    {
        $this->_beforeExport();
        return parent::getCsv($filename);
    }
}
