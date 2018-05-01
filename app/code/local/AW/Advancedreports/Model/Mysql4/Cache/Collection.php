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


class AW_Advancedreports_Model_Mysql4_Cache_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_tableName = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('advancedreports/cache');
    }

    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getMainTable()));
        return $this;
    }

    public function setPeriodFilter($from, $to)
    {
        $periodKey = AW_Advancedreports_Helper_Tools_Aggregator::DATE_KEY_FIELD;
        $this->getSelect()
            ->where("main_table.{$periodKey} <= ?", $to)
            ->where("main_table.{$periodKey} >= ?", $from)
        ;
        return $this;
    }

    /**
     * Set up mai table name for collection
     *
     * @param string $table
     *
     * @return AW_Advancedreports_Model_Mysql4_Cache_Collection
     */
    public function setMainTable($table)
    {
        $this->_tableName = $table;
        $this->_select->reset(Zend_Db_Select::FROM);
        $this->_initSelect();
        return $this;
    }

    public function getMainTable()
    {
        return $this->_tableName ? $this->_tableName : $this->getResource()->getMainTable();
    }

    /**
     * Retrieves total from cache DB
     *
     * @param string $field
     *
     * @return integer|float
     */
    public function getTotal($field)
    {
        Varien_Profiler::start("aw::advancedreports::aggregator::fetch_total");
        $this->_renderFilters();
        $sumSelect = clone $this->getSelect();
        $sumSelect->reset(Zend_Db_Select::ORDER);
        $sumSelect->reset(Zend_Db_Select::COLUMNS);
        $sumSelect->columns("SUM({$field})");
        $total = $this->getConnection()->fetchOne($sumSelect, $this->_bindParams);
        Varien_Profiler::stop("aw::advancedreports::aggregator::fetch_total");
        return $total;
    }

    public function  load($printQuery = false, $logQuery = false)
    {
        Varien_Profiler::start("aw::advancedreports::aggregator::load_collection");
        parent::load($printQuery, $logQuery);
        Varien_Profiler::stop("aw::advancedreports::aggregator::load_collection");
        return $this;
    }
}
