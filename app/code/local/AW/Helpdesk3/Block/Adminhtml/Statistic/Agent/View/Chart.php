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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Block_Adminhtml_Statistic_Agent_View_Chart extends Mage_Adminhtml_Block_Template
{
    const PERIOD_TODAY = 1;
    const PERIOD_YESTERDAY = 2;
    const PERIOD_THIS_WEEK = 3;
    const PERIOD_THIS_MONTH = 4;
    const PERIOD_LAST_WEEK = 5;
    const PERIOD_LAST_MONTH = 6;
    const PERIOD_LAST_SIX_MONTHS = 7;
    const PERIOD_CUSTOM = 8;
    const PERIOD_LAST_SEVEN_DAYS = 9;
    const PERIOD_LAST_THIRTY_DAYS = 10;

    const GROUP_BY_DAY = AW_Helpdesk3_Helper_Statistics::PERIOD_DAY_TYPE;
    const GROUP_BY_WEEK = AW_Helpdesk3_Helper_Statistics::PERIOD_WEEK_TYPE;
    const GROUP_BY_MONTH = AW_Helpdesk3_Helper_Statistics::PERIOD_MONTH_TYPE;
    const GROUP_BY_YEAR = AW_Helpdesk3_Helper_Statistics::PERIOD_YEAR_TYPE;

    const REPORT_FIRST_REPLY_AVG_TIME = 1;
    const REPORT_TICKET_CLOSE_AVG_TIME = 2;


    protected function _construct()
    {
        $this->setTemplate('aw_hdu3/statistic/agent/view/chart.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getDataForChart()
    {
        $filter = Mage::registry('current_filter');
        $storeId = Mage::registry('current_store');
        $fromDate = $filter['from_date'];
        $fromDate = $fromDate->toString('Y-MM-dd');
        $toDate = $filter['to_date'];
        $toDate = $toDate->toString('Y-MM-dd');
        $agentIds = $filter['agents[]'];
        $data = array();
        switch($filter['report']) {
            case self::REPORT_FIRST_REPLY_AVG_TIME:
                $data = Mage::helper('aw_hdu3/statistics')->getFirstReplyByAgentsAvgTime(
                    $fromDate, $toDate, $agentIds, $storeId
                );
                break;
            case self::REPORT_TICKET_CLOSE_AVG_TIME:
                $data = Mage::helper('aw_hdu3/statistics')->getCloseTicketByAgentsAvgTime(
                    $fromDate, $toDate, $agentIds, $storeId
                );
                break;
        }

        $result = array();
        $result['0'] = array($this->__('Date'));
        $agentCollectionHash = Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter()->toOptionHash();
        foreach ($data as $agentId => $agentData) {
            $result['0'][] = $agentCollectionHash[$agentId];
            $agentData = Mage::helper('aw_hdu3/statistics')->getGroupedByPeriod($agentData, $filter['group']);
            foreach ($agentData as $date => $value) {
                if (!array_key_exists($date, $result)) {
                    $formattedDate = $this->formatDateForChart((string)$date);
                    $result[$date] = array($formattedDate);
                }
                $result[$date][] = (float)$value;
            }
        }
        ksort ($result);
        return array_values($result);
    }

    /**
     * available formats = (Y, Y-m, Y-m-d, Y-m-d/Y-m-d)
     * @param string $date
     *
     * @return string
     */
    public function formatDateForChart($date)
    {
        $dateArray = explode('/', $date);
        foreach ($dateArray as $key => $value) {
            $datePartList = explode('-', $value);
            if (array_key_exists(2, $datePartList)) {
                $zDate = new Zend_Date($value, 'y-MM-dd', Mage::app()->getLocale()->getLocale());
                $dateArray[$key] = $zDate->toString(Zend_Date::DATE_MEDIUM);
            } else if (array_key_exists(1, $datePartList)) {
                $zDate = new Zend_Date($value, 'y-MM', Mage::app()->getLocale()->getLocale());
                $dateArray[$key] = $zDate->toString(Zend_Date::MONTH_NAME . ', ' . Zend_Date::YEAR);
            } else {
                $zDate = new Zend_Date($value, 'y', Mage::app()->getLocale()->getLocale());
                $dateArray[$key] = $zDate->toString(Zend_Date::YEAR);
            }
        }
        return implode('/', $dateArray);
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('helpdesk_admin/adminhtml_statistic_agent/index');
    }

    /**
     * @return array
     */
    public function getPredefinedPeriodTypeList()
    {
        return array(
            self::PERIOD_TODAY            => $this->__('Today'),
            self::PERIOD_YESTERDAY        => $this->__('Yesterday'),
            self::PERIOD_THIS_WEEK        => $this->__('This Week'),
            self::PERIOD_THIS_MONTH       => $this->__('This Month'),
            self::PERIOD_LAST_WEEK        => $this->__('Last Week'),
            self::PERIOD_LAST_MONTH       => $this->__('Last Month'),
            self::PERIOD_LAST_SIX_MONTHS  => $this->__('Last 6 Months'),
            self::PERIOD_LAST_SEVEN_DAYS  => $this->__('Last 7 Days'),
            self::PERIOD_LAST_THIRTY_DAYS => $this->__('Last 30 Days'),
            self::PERIOD_CUSTOM           => $this->__('Custom')
        );
    }

    /**
     * @return array
     */
    public function getAvailableGroupingList()
    {
        return array(
            self::GROUP_BY_DAY   => $this->__('Day'),
            self::GROUP_BY_WEEK  => $this->__('Week'),
            self::GROUP_BY_MONTH => $this->__('Month'),
            self::GROUP_BY_YEAR  => $this->__('Year'),
        );
    }

    /**
     * @return array
     */
    public function getReportTypeList()
    {
        return array(
            self::REPORT_FIRST_REPLY_AVG_TIME  => $this->__('Avg. First Reply Time (hrs)'),
            self::REPORT_TICKET_CLOSE_AVG_TIME => $this->__('Avg. Closing Ticket Time (hrs)'),
        );
    }

    /**
     * @return array
     */
    public function getAgentList()
    {
        return Mage::helper('aw_hdu3/ticket')->getAgentList();
    }

    /**
     * @param int $periodValue
     *
     * @return bool
     */
    public function isPeriodSelected($periodValue)
    {
        $filter = Mage::registry('current_filter');
        $currentPeriodValue = array_key_exists('period', $filter)?$filter['period']:null;
        if (null === $currentPeriodValue) {
            $currentPeriodValue = self::PERIOD_LAST_THIRTY_DAYS;
        }
        return intval($currentPeriodValue) === intval($periodValue);
    }

    /**
     * @param int $groupValue
     *
     * @return bool
     */
    public function isGroupSelected($groupValue)
    {
        $filter = Mage::registry('current_filter');
        $currentGroupValue = array_key_exists('group', $filter)?$filter['group']:null;
        if (null === $currentGroupValue) {
            $currentGroupValue = self::GROUP_BY_WEEK;
        }
        return intval($currentGroupValue) === intval($groupValue);
    }

    /**
     * @param int $reportTypeValue
     *
     * @return bool
     */
    public function isReportTypeSelected($reportTypeValue)
    {
        $filter = Mage::registry('current_filter');
        $currentReportTypeValue = array_key_exists('report', $filter)?$filter['report']:null;
        if (null === $currentReportTypeValue) {
            $currentReportTypeValue = self::REPORT_FIRST_REPLY_AVG_TIME;
        }
        return intval($currentReportTypeValue) === intval($reportTypeValue);
    }

    /**
     * @param $agentId
     *
     * @return bool
     */
    public function isAgentSelected($agentId)
    {
        $filter = Mage::registry('current_filter');
        $currentAgentList = array_key_exists('agents[]', $filter)?$filter['agents[]']:null;
        if (null === $currentAgentList) {
            return true;
        }
        return in_array($agentId, $currentAgentList);
    }

    /**
     * @return string
     */
    public function getFromDateValue()
    {
        $filter = Mage::registry('current_filter');
        /** @var Zend_Date $date */
        $date = $filter['from_date'];
        return $date->toString('dd/MM/y');
    }

    /**
     * @return string
     */
    public function getToDateValue()
    {
        $filter = Mage::registry('current_filter');
        /** @var Zend_Date $date */
        $date = $filter['to_date'];
        return $date->toString('dd/MM/y');
    }
}
