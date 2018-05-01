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

/**
 * Country Report Grid
 */
class AW_Advancedreports_Block_Advanced_Country_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    /**
     * Route to helper options
     *
     * @var string
     */
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_COUNTRY;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridCountry');
    }

    /**
     * Prepare report collection
     *
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->setCollection(Mage::getResourceModel('advancedreports/collection_country'));
        $this->_prepareAbstractCollection();
        $this->getCollection()->addAddress();
        $this->getCollection()->addOrderItemsCount();
        $this->_helper()->setNeedMainTableAlias(true);
        $this->_prepareData();
        return $this;
    }

    /**
     * Collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Country
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Retrieves Show Chart flag
     *
     * @return boolean
     */
    public function hasRecords()
    {
        return (count($this->_customData))
            && $this->_helper()->getChartParams($this->_routeOption)
            && count($this->_helper()->getChartParams($this->_routeOption))
        ;
    }

    /**
     * Add row to custom data
     *
     * @param array $row
     *
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    protected function _addCustomData($row)
    {
        if (count($this->_customData)) {
            foreach ($this->_customData as &$d) {
                if ($d['country_id'] === $row['country_id']) {
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

    /**
     * Retrive compare result for two arrays by Total
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function _compareTotalElements($a, $b)
    {
        if ($a['total'] == $b['total']) {
            return 0;
        }
        return ($a['total'] > $b['total']) ? -1 : 1;
    }

    /**
     * Retrive compare result for two arrays by Quantity element
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function _compareQtyElements($a, $b)
    {
        if ($a['qty_ordered'] == $b['qty_ordered']) {
            return 0;
        }
        return ($a['qty_ordered'] > $b['qty_ordered']) ? -1 : 1;
    }

    /**
     * Prepare Custom Data to show chart
     *
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
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
            if ($order->getCountryId()) {
                $row['country_id'] = $order->getCountryId();
                $row['qty_ordered'] = $order->getSumQty();
                $row['total'] = $order->getSumTotal();
                $this->_addCustomData($row);
            }
        }

        if (!count($this->_customData)) {
            return $this;
        }

        $key = $this->getFilter('reload_key');
        if ($key === 'qty') {
            //Sort data
            usort($this->_customData, array(&$this, "_compareQtyElements"));

            //All qty
            $qty = 0;
            foreach ($this->_customData as $d) {
                $qty += $d['qty_ordered'];
            }
            foreach ($this->_customData as $i => &$d) {
                $d['order'] = $i + 1;
                $d['percent'] = round($d['qty_ordered'] * 100 / $qty, 1) . ' %';
                $d['percent_data'] = round($d['qty_ordered'] * 100 / $qty, 1);
                //Add title
                $d['country_name'] = Mage::getSingleton('directory/country')->loadByCode($d['country_id'])->getName();;
            }
        } elseif ($key === 'total') {
            //Sort data
            usort($this->_customData, array(&$this, "_compareTotalElements"));

            //All qty
            $total = 0;
            foreach ($this->_customData as $d) {
                $total += $d['total'];
            }
            foreach ($this->_customData as $i => &$d) {
                $d['order'] = $i + 1;
                if ($total != 0) {
                    $d['percent'] = round($d['total'] * 100 / $total, 1) . ' %';
                    $d['percent_data'] = round($d['total'] * 100 / $total, 1);
                } else {
                    $d['percent'] = '0 %';
                    $d['percent_data'] = 0;
                }

                //Add title
                $d['country_name'] = Mage::getSingleton('directory/country')->loadByCode($d['country_id'])->getName();;
            }
        } else {
            return $this;
        }
        $this->_helper()->setChartData($this->_customData, $this->_helper()->getDataKey($this->_routeOption));
        parent::_prepareData();
        return $this;
    }

    /**
     * Retrieves flag to show params selector always
     *
     * @return boolean
     */
    public function getShowAnyway()
    {
        return true;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'order',
            array(
                'header' => Mage::helper('reports')->__('N'),
                'width'  => '60px',
                'align'  => 'right',
                'index'  => 'order',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'country_name',
            array(
                'header' => Mage::helper('reports')->__('Country'),
                'index'  => 'country_name',
            )
        );

        $this->addColumn(
            'percent',
            array(
                'header' => $this->_helper()->__('Percent'),
                'width'  => '60px',
                'align'  => 'right',
                'index'  => 'percent',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'qty_ordered',
            array(
                'header' => $this->_helper()->__('Quantity'),
                'width'  => '120px',
                'align'  => 'right',
                'index'  => 'qty_ordered',
                'total'  => 'sum',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'total',
            array(
                'header'        => Mage::helper('reports')->__('Total'),
                'width'         => '120px',
                'currency_code' => $this->getCurrentCurrencyCode(),
                'index'         => 'total',
                'type'          => 'currency',
            )
        );

        $this->addExportType('*/*/exportOrderedCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', $this->_helper()->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_MAP;
    }
}
