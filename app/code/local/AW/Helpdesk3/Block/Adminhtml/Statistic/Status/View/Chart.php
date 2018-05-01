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


class AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart extends Mage_Adminhtml_Block_Template
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

    protected function _construct()
    {
        $this->setTemplate('aw_hdu3/statistic/status/view/chart.phtml');
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
        $departmentIds = $filter['departments[]'];
        $statusIds = $filter['status[]'];
        $data = Mage::helper('aw_hdu3/statistics')->getTicketStatusStatisticsByDepartment(
            $fromDate, $toDate, $statusIds, $departmentIds, $storeId
        );

        $result = array();
        $result['0'] = array($this->__('Date'));
        $departmentCollectionHash = Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter()->toOptionHash();
        if (count($departmentCollectionHash) > 0) {
            foreach ($data as $departmentId => $departmentData) {
                $result['0'][] = $departmentCollectionHash[$departmentId];
                $departmentData = Mage::helper('aw_hdu3/statistics')->getGroupedByPeriod($departmentData, $filter['group']);
                foreach ($departmentData as $date => $value) {
                    if (!array_key_exists($date, $result)) {
                        $formattedDate = $this->formatDateForChart((string)$date);
                        $result[$date] = array($formattedDate);
                    }
                    $result[$date][] = (float)$value;
                }
            }
            ksort ($result);
        }
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
        return $this->getUrl('helpdesk_admin/adminhtml_statistic_status/index');
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
    public function getStatusList()
    {
        $storeId = Mage::registry('current_store');
        return AW_Helpdesk3_Model_Source_Ticket_Status::toOptionHash($storeId);
    }

    /**
     * @return array
     */
    public function getDepartmentList()
    {
        return Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter()->addActiveFilter()->toOptionHash();
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
     * @param $departmentId
     *
     * @return bool
     */
    public function isStatusSelected($statusId)
    {
        $filter = Mage::registry('current_filter');
        $currentStatusList = array_key_exists('status[]', $filter)?$filter['status[]']:null;
        if (null === $currentStatusList) {
            return true;
        }
        return in_array($statusId, $currentStatusList);
    }

    /**
     * @param $departmentId
     *
     * @return bool
     */
    public function isDepartmentSelected($departmentId)
    {
        $filter = Mage::registry('current_filter');
        $currentDepartmentList = array_key_exists('departments[]', $filter)?$filter['departments[]']:null;
        if (null === $currentDepartmentList) {
            return true;
        }
        return in_array($departmentId, $currentDepartmentList);
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