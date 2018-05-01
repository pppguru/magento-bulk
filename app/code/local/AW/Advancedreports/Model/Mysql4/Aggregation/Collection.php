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


class AW_Advancedreports_Model_Mysql4_Aggregation_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_from = null;
    protected $_to = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('advancedreports/aggregation');
    }

    /**
     * Retrieves helper
     *
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    public function setPeriodFilter($from, $to)
    {
        $this->getSelect()
            ->where("main_table.from <= ?", $to)
            ->where("main_table.to >= ?", $from);

        $this->_from = $from;
        $this->_to = $to;

        return $this;
    }

    public function setTableFilter($table)
    {
        $this->getSelect()
            ->where("main_table.table = ?", $table);
        return $this;
    }

    public function setExpiredFilter($expired = AW_Advancedreports_Model_Aggregation::EXPIRED_TRUE)
    {
        $currentDate = new Zend_Date(time());
        $this->getSelect()->where(
            "DATEDIFF(?, main_table.expired) " . ($expired ? '>' : '<=') . '0', $currentDate->toString('Y-MM-dd')
        );
        return $this;
    }

    public function setTimetypeFilter($tymetype)
    {
        $this->getSelect()->where("main_table.timetype = ?", $tymetype);
        return $this;
    }

    public function setToOrdering()
    {
        $this->getSelect()->order("main_table.to DESC");
        return $this;
    }


    protected function _cutLimitations($periods, $from, $to)
    {
        $last = 0;
        $first = count($periods) - 1;

        if ($periods[$last]['to'] > $to) {
            $periods[$last]['to'] = $to;
        }

        if ($periods[$first]['from'] < $from) {
            $periods[$first]['from'] = $from;
        }

        return $periods;
    }


    protected function _getBetweenIntervals($periods, $from, $to)
    {
        $periods = array_merge(
            array(
                 array(
                     'from' => $this->_helper()->getDate()->incSec($to),
                 )
            ),
            $periods,
            array(
                 array(
                     'to' => $this->_helper()->getDate()->decSec($from),
                 )
            )
        );

        $last = 0;
        $first = count($periods) - 1;

        $intervals = array();

        for ($i = $last; $i < $first; $i++) {
            $_from = $this->_helper()->getDate()->incSec($periods[$i + 1]['to']);
            $_to = $this->_helper()->getDate()->decSec($periods[$i]['from']);
            if ($_to > $_from) {
                $intervals[] = array(
                    'from' => $_from,
                    'to'   => $_to,
                );
            }
        }
        return $intervals;
    }


    /**
     * Retrive array with time intervals to reaggreagte
     *
     * @return array
     */
    public function reagregateRequired()
    {
        $intervals = array();
        if (($from = $this->_from) && ($to = $this->_to)) {
            $exists = array();
            if ($this->getSize()) {
                foreach ($this as $aPeriod) {
                    $exists[] = array(
                        'to'   => $aPeriod->getTo(),
                        'from' => $aPeriod->getFrom(),
                    );
                }
                $exists = $this->_cutLimitations($exists, $from, $to);
                $intervals = $this->_getBetweenIntervals($exists, $from, $to);
            }
        }
        return $intervals;
    }

    protected function _cutAndExpire($period, $timetype = 'created_at')
    {
        if (!$period || !is_array($period)) {
            return $this;
        }

        $period = new Varien_Object($period);
        if (($from = $period->getFrom()) && ($to = $period->getTo())) {
            $collection = Mage::getModel('advancedreports/aggregation')->getCollection();
            $collection->setPeriodFilter($from, $to)->setTimetypeFilter($timetype);

            foreach ($collection as $period) {
                $aggregating = Mage::getModel('advancedreports/aggregation')->load($period->getId());
                $aData = $aggregating->getData();
                unset($aData['entity_id']);
                $aggregating->delete();

                if (($period->getFrom() < $from)) {
                    $aggregating = Mage::getModel('advancedreports/aggregation');
                    $aggregating->addData($aData);
                    $aggregating->setTo($this->_helper()->getDate()->decSec($from));
                    $aggregating->save();
                }

                if (($period->getTo() > $to)) {
                    $aggregating = Mage::getModel('advancedreports/aggregation');
                    $aggregating->addData($aData);
                    $aggregating->setFrom($this->_helper()->getDate()->incSec($to));
                    $aggregating->save();
                }
            }
        }
        return $this;
    }

    public function expirePeriodFor($createdAt = null, $origUpdatedAt = null, $updatedAt = null)
    {
        $periodCreatedAt = $this->_helper()->getDate()->getThisDayPeriod($createdAt);
        $periodOrigUpdatedAt = $this->_helper()->getDate()->getThisDayPeriod($origUpdatedAt);
        $periodUpdatedAt = $this->_helper()->getDate()->getThisDayPeriod($updatedAt);

        $this->_cutAndExpire($periodCreatedAt, 'created_at');
        $this->_cutAndExpire($periodOrigUpdatedAt, 'updated_at');

        $this->_cutAndExpire($periodUpdatedAt, 'created_at');
        $this->_cutAndExpire($periodUpdatedAt, 'updated_at');

        return $this;
    }

    /**
     * Clear all data about cache
     *
     * @return AW_Advancedreports_Model_Mysql4_Aggregation_Collection
     */
    public function clearTable()
    {
        $write = $this->getResource()->cleanTable();
        return $this;
    }

    public function getAllTables()
    {
        $collection = clone $this;
        $select = $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::WHERE)
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET)
            ->columns(array('table'))
            ->group('main_table.table')
        ;

        return $this->getResource()->getReadConnection()->fetchAll($select);
    }
}
