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


class AW_Advancedreports_Block_Advanced_Usergroups_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_USERGROUPS;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(Mage::helper('advancedreports')->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridUsergroups');
    }

    public function hasRecords()
    {
        return (count($this->_customData))
            && Mage::helper('advancedreports')->getChartParams($this->_routeOption)
            && count(Mage::helper('advancedreports')->getChartParams($this->_routeOption))
        ;
    }

    protected function _setCollectionOrder($column)
    {
        if (method_exists(get_parent_class($this), '_setCollectionOrder')) {
            return parent::_setCollectionOrder($column);
        }
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->setOrder($columnIndex, $column->getDir());
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var AW_Advancedreports_Model_Mysql4_Collection_Usergroups $collection */
        $collection = Mage::getResourceModel('advancedreports/collection_usergroups');

        $this->setCollection($collection);
        $dateFrom = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $dateTo = $this->_getMysqlToFormat($this->getFilter('report_to'));
        $this->getCollection()->setDateFilter($dateFrom, $dateTo)->setState();
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }
        $collection->addCustomerGroups();
        $this->_helper()->setNeedMainTableAlias(true);

        $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
        if (strcmp($columnId, 'usergroups') === 0) {
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $dir = (strtolower($dir) == 'desc') ? 'asc' : 'desc';
            $this->_columns[$columnId]->setDir($dir);
            $this->_setCollectionOrder($this->_columns[$columnId]);
        }

        $this->_prepareData();
        return $this;
    }

    /**
     * Collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Usergroups
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    protected function _addCustomData($row)
    {
        if (count($this->_customData)) {
            foreach ($this->_customData as &$d) {
                if (isset($d['group_id']) && ($d['group_id'] === $row['group_id'])) {
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

    /*
     * Prepare data array for Pie and Grid
     */
    protected function _prepareData()
    {
        foreach ($this->getCollection() as $order) {
            $row = array();

            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $row[$column->getIndex()] = $order->getData($column->getIndex());
                }
            }

            $row['group_id'] = $order->getGroupId();
            $row['title'] = $order->getGroupName();
            $row['qty_ordered'] = $order->getSumQty();
            $row['total'] = $order->getSumTotal();
            $this->_addCustomData($row);
        }

        if (!count($this->_customData)) {
            return $this;
        }

        $key = $this->getFilter('reload_key');
        if ($key === 'qty') {
            # Sort data
//            usort($this->_customData, array(&$this, "_compareQtyElements"));
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
            //Sort data
//            usort($this->_customData, array(&$this, "_compareTotalElements"));
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

    /*
     * Need to sort bestsellers array
     */
    protected function _compareTotalElements($a, $b)
    {
        if ($a['total'] == $b['total']) {
            return 0;
        }
        return ($a['total'] > $b['total']) ? -1 : 1;
    }

    /*
    * Need to sort bestsellers array
    */
    protected function _compareQtyElements($a, $b)
    {
        if ($a['qty_ordered'] == $b['qty_ordered']) {
            return 0;
        }
        return ($a['qty_ordered'] > $b['qty_ordered']) ? -1 : 1;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'usergroups',
            array(
                'header' => Mage::helper('advancedreports')->__('Customer Group'),
                'width'  => '60px',
                'align'  => 'left',
                'index'  => 'group_name',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'percent',
            array(
                'header'                 => Mage::helper('advancedreports')->__('Percent'),
                'width'                  => '60px',
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
        return AW_Advancedreports_Block_Chart::CHART_TYPE_PIE3D;
    }
}
