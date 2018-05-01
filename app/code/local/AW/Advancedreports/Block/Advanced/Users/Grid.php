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
 * User Activity Report Grid
 */
class AW_Advancedreports_Block_Advanced_Users_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    /**
     * Grid Options Route Key
     *
     * @var string
     */
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_USERS;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridUsers');
    }

    /**
     * Flag to show Show By select
     *
     * @return boolean
     */
    public function getHideShowBy()
    {
        return false;
    }

    /**
     * Add Custom Data row
     *
     * @param array $row
     *
     * @return AW_Advancedreports_Block_Advanced_Users_Grid
     */
    protected function _addCustomData($row)
    {
        $this->_customData[] = $row;
        return $this;
    }

    /**
     * Prepare grid collection
     *
     * @return AW_Advancedreports_Block_Advanced_Users_Grid
     */
    public function _prepareCollection()
    {
        parent::_prepareOlderCollection();
        # This calculate collection of intervals
        $this->getCollection()->initReport('reports/product_ordered_collection');
        $this->_prepareData();
        return $this;
    }

    /**
     * Retrieves orders count for period
     *
     * @param Datetime $from
     * @param Datetime $to
     *
     * @return int
     */
    protected function _getOrdersCount($from, $to)
    {
        $collection = Mage::getModel('sales/order')->getCollection();
        /** @var AW_Advancedreports_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getResourceModel('advancedreports/collection_abstract');
        #set order state filter
        $collection->setState();

        $collection->setDateFilter($from, $to);

        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }
        return $collection->getSize();
    }

    /**
     * Retrieves new accounts count for period
     *
     * @param Datetime $from
     * @param Datetime $to
     *
     * @return int
     */
    protected function _getAccountsCount($from, $to)
    {
        /** @var Mage_Customer_Model_Entity_Customer_Collection $collection */
        $collection = Mage::getModel('customer/customer')->getCollection();
        $filterField = $this->_helper()->confOrderDateFilter();
        $collection->addAttributeToFilter($filterField, array('gteq' => $from));
        $collection->addAttributeToFilter($filterField, array('lteq' => $to));
        #set order state filter
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
        }
        return $collection->getSize();
    }

    /**
     * Retrieves reviews count for period
     *
     * @param Datetime $from
     * @param Datetime $to
     *
     * @return int
     */
    protected function _getReviewsCount($from, $to)
    {
        $collection = Mage::getModel('review/review')->getCollection();
        /** @var AW_Advancedreports_Model_Mysql4_Collection_Users_Reviews $collection */
        $collection->getSelect()
            ->where("main_table.created_at >= ?", $from)
            ->where("main_table.created_at <= ?", $to)
        ;
        # check Store Filter
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->addStoreFilter($this->getStoreIds($storeIds));
        }
        return $collection->getSize();
    }

    /**
     * Prepare data for Chart and Grid
     *
     * @return AW_Advancedreports_Block_Advanced_Users_Grid
     */
    protected function _prepareData()
    {
        # primary analise
        $postition = 0;
        foreach ($this->getCollection()->getIntervals() as $_item) {
            $row['period'] = $_item['title'];
            $row['sort_position'] = $postition++;
            $row['accounts'] = $this->_getAccountsCount($_item['start'], $_item['end']);
            $row['orders'] = $this->_getOrdersCount($_item['start'], $_item['end']);
            $row['reviews'] = $this->_getReviewsCount($_item['start'], $_item['end']);
            $this->_addCustomData($row);
        }

        $chartLabels = array(
            'accounts' => $this->_helper()->__('New Accounts'),
            'orders'   => $this->_helper()->__('Orders'),
            'reviews'  => $this->_helper()->__('Reviews'),
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

    /**
     * Prepare columns for grid
     *
     * @return AW_Advancedreports_Block_Advanced_Users_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            array(
                'header'              => $this->getPeriodText(),
                'width'               => '120px',
                'index'               => 'period',
                'type'                => 'text',
                'is_position_sorting' => 1,
            )
        );

        $this->addColumn(
            'accounts',
            array(
                'header'  => $this->_helper()->__('New Accounts'),
                'width'   => '120px',
                'align'   => 'right',
                'index'   => 'accounts',
                'type'    => 'number',
                'default' => '0',
            )
        );

        $this->addColumn(
            'orders',
            array(
                'header'  => $this->_helper()->__('Orders'),
                'width'   => '120px',
                'align'   => 'right',
                'index'   => 'orders',
                'type'    => 'number',
                'default' => '0',
            )
        );

        $this->addColumn(
            'reviews',
            array(
                'header'  => $this->_helper()->__('Reviews'),
                'width'   => '120px',
                'align'   => 'right',
                'index'   => 'reviews',
                'type'    => 'number',
                'default' => '0',
            )
        );

        $this->addExportType('*/*/exportOrderedCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', $this->_helper()->__('Excel'));

        return $this;
    }

    /**
     * Retrieves Chart type
     *
     * @return string
     */
    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_MULTY_LINE;
    }

    /**
     * Retrieves older periods collection
     *
     * @return Mage_Reports_Model_Mysql4_Report_Collection
     */
    public function getPeriods()
    {
        return parent::_getOlderPeriods();
    }
}
