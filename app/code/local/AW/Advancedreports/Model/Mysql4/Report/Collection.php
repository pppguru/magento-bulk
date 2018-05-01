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


class AW_Advancedreports_Model_Mysql4_Report_Collection extends Mage_Reports_Model_Mysql4_Report_Collection
{
    /**
     * Retrieves helper
     *
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    /**
     * Overrides standard periods
     *
     * @return array
     */
    public function getPeriods()
    {
        return array(
            'day'     => Mage::helper('advancedreports')->__('Day'),
            'week'    => Mage::helper('advancedreports')->__('Week'),
            'month'   => Mage::helper('advancedreports')->__('Month'),
            'quarter' => Mage::helper('advancedreports')->__('Quarter'),
            'year'    => Mage::helper('advancedreports')->__('Year'),
        );
    }

    protected function processIntervals()
    {
        foreach ($this->_intervals as &$interval) {
            $interval['start'] = $this->_helper()->timezoneFactory($interval['start']);
            $interval['end'] = $this->_helper()->timezoneFactory($interval['end']);
        }
    }

    /**
     * Overrides standard getIntervals
     *
     * @return array
     */
    public function getIntervals()
    {
        if (!$this->_intervals) {
            $this->_intervals = array();
            if (!$this->_from && !$this->_to) {
                return $this->_intervals;
            }

            $t = array();

            $diff = 0;

            if ($this->_period == 'week') {
                $firstWeekDay = Mage::getStoreConfig('general/locale/firstday');
                $dateStart = new Zend_Date($this->_from);
                $curWeekDay = $dateStart->toString('e');
                if ($curWeekDay > $firstWeekDay) {
                    $firstWeekDay += 7;
                }
                $diff = abs($curWeekDay - $firstWeekDay);
            }

            if ($this->_period == 'week' && ($diff > 0)) {
                $dateStart = new Zend_Date($this->_from);
                $dateStart2 = new Zend_Date($this->_from);
                $dateStart2->addDay($diff);

                $t['title'] = $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                $t['start'] = $dateStart->toString('yyyy-MM-dd 00:00:00');
                $dateStart->addDay($diff)->subDay(1);
                $t['title'] .= ' - ' . $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                $t['end'] = $dateStart->toString('yyyy-MM-dd 23:59:59');
                $dateStart->addDay(1);

                if (isset($t['title'])) {
                    $this->_intervals[$t['title']] = $t;
                }

                $dateStart2 = new Zend_Date($this->_from);
                $dateEnd = new Zend_Date($this->_to);

            } else {
                $dateStart = new Zend_Date($this->_from);
                $dateStart2 = new Zend_Date($this->_from);
                $dateEnd = new Zend_Date($this->_to);
            }

            while ($dateStart->compare($dateEnd) <= 0) {
                switch ($this->_period) {
                    case 'day' :
                        $t['title'] = $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                        $t['start'] = $dateStart->toString('yyyy-MM-dd 00:00:00');
                        $t['end'] = $dateStart->toString('yyyy-MM-dd 23:59:59');
                        $dateStart->addDay(1);
                        break;
                    case 'week':
                        $t['title'] = $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                        $t['start'] = $dateStart->toString('yyyy-MM-dd 00:00:00');
                        $dateStart->addWeek(1)->subDay(1);
                        $t['title'] .= ' - ' . $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                        $t['end'] = $dateStart->toString('yyyy-MM-dd 23:59:59');
                        $dateStart->addDay(1);
                        break;
                    case 'month':
                        $t['title'] = $dateStart->toString('MM/yyyy');
                        $t['start'] = $dateStart->toString('yyyy-MM-01 00:00:00');
                        $t['end'] = $dateStart->toString(
                            'yyyy-MM-' . date('t', $dateStart->getTimestamp()) . ' 23:59:59'
                        );
                        $dateStart->addMonth(1);
                        break;
                    case 'quarter':
                        $month = (integer)$dateStart->toString('MM');
                        $num = round($month / 3) + 1;
                        $t['title'] = Mage::helper('advancedreports')->__('Q') . $num . $dateStart->toString('/yyyy');
                        $t['start'] = $dateStart->toString('yyyy-MM-01 00:00:00');
                        $dateStart->addMonth(2);
                        $t['end'] = $dateStart->toString(
                            'yyyy-MM-' . date('t', $dateStart->getTimestamp()) . ' 23:59:59'
                        );
                        $dateStart->addMonth(1);
                        break;
                    case 'year':
                        $t['title'] = $dateStart->toString('yyyy');
                        $t['start'] = $dateStart->toString('yyyy-01-01 00:00:00');
                        $t['end'] = $dateStart->toString('yyyy-12-31 23:59:59');
                        $dateStart->addYear(1);
                        break;
                    default:
                        Mage::throwException("Report tried to get intervals without a period.");
                }
                if (isset($t['title'])) {
                    $this->_intervals[$t['title']] = $t;
                }
            }

            if ($this->_period != 'day') {
                $titles = array_keys($this->_intervals);
                if (count($titles) > 0) {
                    $this->_intervals[$titles[0]]['start'] = $dateStart2->toString('yyyy-MM-dd 00:00:00');
                    $this->_intervals[$titles[count($titles) - 1]]['end'] = $dateEnd->toString('yyyy-MM-dd 23:59:59');
                    if ($this->_period == 'week') {
                        $t = $this->_intervals[$titles[count($titles) - 1]];
                        unset($this->_intervals[$titles[count($titles) - 1]]);
                        $date = new Zend_Date($t['start'], 'yyyy-MM-dd 00:00:00');
                        $t['title'] = $date->toString(Mage::app()->getLocale()->getDateFormat());
                        unset($date);
                        $date = new Zend_Date($t['end'], 'yyyy-MM-dd 23:59:59');
                        $t['title'] .= ' - ' . $date->toString(Mage::app()->getLocale()->getDateFormat());
                        $this->_intervals[$t['title']] = $t;
                    }
                }
            }
            $this->processIntervals();
        }
        return $this->_intervals;
    }

    public function setPageSize($size)
    {
        return $this;
    }

    public function setCurPage($page)
    {
        return $this;
    }
}
