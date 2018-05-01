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


class AW_Advancedreports_Block_Advanced_Dayofweek_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_DAYOFWEEK;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(Mage::helper('advancedreports')->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridDayofweek');
    }

    protected function _addCustomData($row)
    {
        if (count($this->_customData)) {
            foreach ($this->_customData as &$d) {
                if ($d['weekday'] == $row['weekday']) {
                    $qty = $d['qty_ordered'];
                    $total = $d['total'];
                    unset($d['total']);
                    unset($d['qty_ordered']);
                    $d['total'] = $row['total'] + $total;
                    $d['qty_ordered'] = $row['qty_ordered'] + $qty;
                    return $this;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->setCollection(Mage::getResourceModel('advancedreports/collection_dayofweek'));
        $this->_prepareAbstractCollection();
        $this->getCollection()->setDayOfWeekFilter();
        $this->_helper()->setNeedMainTableAlias(true);
        $this->_prepareData();
        return $this;
    }

    /**
     * Collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Dayofweek
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    protected function _getWeekday($value)
    {
        return date('w', strtotime($value) + $this->getTimeDiff() * 3600);
    }

    /*
    * Prepare data array for Pie and Grid
    */
    protected function _prepareData()
    {
        foreach ($this->_helper()->getWeekChain() as $i) {
            $row['weekday'] = $i;
            $row['title'] = $this->_helper()->getWeekday($row['weekday']);
            $row['qty_ordered'] = 0;
            $row['total'] = 0;
            $this->_addCustomData($row);
        }
        foreach ($this->getCollection() as $order) {
            $row = array();

            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $row[$column->getIndex()] = $order->getData($column->getIndex());
                }
            }
            $row['weekday'] = $order->getDayOfWeek() - 1;
            $row['qty_ordered'] = $order->getSumQty();
            $row['total'] = $order->getSumTotal();
            $this->_addCustomData($row);
        }

        if (!count($this->_customData)) {
            return $this;
        }

        $key = $this->getFilter('reload_key');
        if ($key === 'qty') {
            //All qty
            $qty = 0;
            foreach ($this->_customData as $d) {
                $qty += $d['qty_ordered'];
            }
            foreach ($this->_customData as $i => &$d) {
                $d['order'] = $i + 1;
                if ($qty) {
                    $d['percent'] = round($d['qty_ordered'] * 100 / $qty) . ' %';
                    $d['percent_data'] = round($d['qty_ordered'] * 100 / $qty);
                    $d['data_for_chart'] = $d['qty_ordered'];
                } else {
                    $d['percent'] = '0 %';
                    $d['percent_data'] = 0;
                    $d['data_for_chart'] = $d['qty_ordered'];
                }
            }
        } elseif ($key === 'total') {
            //All qty
            $total = 0;
            foreach ($this->_customData as $d) {
                $total += $d['total'];
            }
            foreach ($this->_customData as $i => &$d) {
                $d['order'] = $i + 1;
                if ($total) {
                    $d['percent'] = round($d['total'] * 100 / $total) . ' %';
                    $d['percent_data'] = round($d['total'] * 100 / $total);
                    $d['data_for_chart'] = $d['total'];
                } else {
                    $d['percent'] = '0 %';
                    $d['percent_data'] = 0;
                    $d['data_for_chart'] = $d['total'];
                }
            }
        } else {
            return $this;
        }

        Mage::helper('advancedreports')->setChartData(
            $this->_customData, Mage::helper('advancedreports')->getDataKey($this->_routeOption)
        );
        parent::_prepareData();
        return $this;
    }

    public function hasRecords()
    {
        return ($this->_customData)
        && Mage::helper('advancedreports')->getChartParams($this->_routeOption)
        && count(Mage::helper('advancedreports')->getChartParams($this->_routeOption));
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            array(
                'header'   => Mage::helper('reports')->__('Day of Week'),
                'width'    => '120px',
                'index'    => 'title',
                'type'     => 'text',
                'sortable' => false,
            )
        );

        $this->addColumn(
            'percent',
            array(
                'header'                 => Mage::helper('advancedreports')->__('Percent'),
                'width'                  => '120px',
                'align'                  => 'right',
                'index'                  => 'percent',
                'type'                   => 'text',
                'custom_sorting_percent' => 1,
            )
        );

        $this->addColumn(
            'qty_ordered',
            array(
                'header' => Mage::helper('advancedreports')->__('Quantity'),
                'width'  => '120px',
                'align'  => 'right',
                'index'  => 'qty_ordered',
                'total'  => 'sum',
                'type'   => 'number',
            )
        );

        $defValue = sprintf("%f", 0);
        $defValue = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($defValue);
        $this->addColumn(
            'total',
            array(
                'header'        => Mage::helper('reports')->__('Total'),
                'width'         => '120px',
                'currency_code' => $this->getCurrentCurrencyCode(),
                'index'         => 'total',
                'type'          => 'currency',
                'default'       => $defValue,
            )
        );

        $this->addExportType('*/*/exportOrderedCsv', Mage::helper('advancedreports')->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', Mage::helper('advancedreports')->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_BARS;
    }
}
