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


class AW_Helpdesk3_Adminhtml_Statistic_StatusController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/statistic/ticket_status');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Statistic - Status - Help Desk'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3')
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_redirect('*/*/view');
    }

    public function viewAction()
    {
        $filter = $this->getRequest()->getParam('filter', array());
        if (!is_array($filter)) {
            $filter = Zend_Json::decode(base64_decode($filter));
        }
        $filter = $this->_processFilter($filter);
        Mage::register('current_filter', $filter);
        Mage::register('current_store', (int)Mage::app()->getRequest()->getParam('store', 0));
        $this->_initAction()
            ->renderLayout()
        ;
    }

    /**
     * @param $filter
     *
     * @return mixed
     */
    protected function _processFilter($filter)
    {
        $period = AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_LAST_THIRTY_DAYS;
        if (array_key_exists('period', $filter)) {
            $period = intval($filter['period']);
        }
        $filter['period'] = $period;
        $fromDate = new Zend_Date();
        $toDate = new Zend_Date();
        $fromDate->setHour(23)->setMinute(59)->setSecond(59);
        $toDate->setHour(23)->setMinute(59)->setSecond(59);
        switch ($period) {
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_TODAY:
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_YESTERDAY:
                $fromDate->subDay(1);
                $toDate->subDay(1);
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_THIS_WEEK:
                $firstDayOfWeek = (int)Mage::getStoreConfig('general/locale/firstday');
                if ($firstDayOfWeek === 0) {
                    $firstDayOfWeek = 7;
                }
                $fromDate->setWeekday($firstDayOfWeek);
                if ($firstDayOfWeek > (int)$fromDate->toString('e')) {
                    $fromDate->subWeek(1);
                }
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_LAST_WEEK:
                $fromDate->subWeek(1);
                $toDate->subWeek(1);
                $firstDayOfWeek = (int)Mage::getStoreConfig('general/locale/firstday');
                if ($firstDayOfWeek === 0) {
                    $firstDayOfWeek = 7;
                }
                $fromDate->setWeekday($firstDayOfWeek);
                if ($firstDayOfWeek > (int)$fromDate->toString('e')) {
                    $fromDate->subWeek(1);
                }
                $lastDayOfWeek = $firstDayOfWeek - 1;
                if ($lastDayOfWeek < 1) {
                    $lastDayOfWeek = 7;
                }
                $toDate->setWeekday($lastDayOfWeek);
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_THIS_MONTH:
                $fromDate->setDay(1);
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_LAST_MONTH:
                $fromDate->subMonth(1)->setDay(1);
                $toDate->setDay(1)->subDay(1);
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_LAST_SIX_MONTHS:
                $fromDate->subMonth(6);
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::PERIOD_CUSTOM:
                $_reversed = false;
                $_now = new Zend_Date(null, 'dd/MM/y');
                try {
                    $_fromDate = new Zend_Date($filter['from_date'], 'dd/MM/y');
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aw_hdu3')->__('Please select correct "From" date value'));
                    $_fromDate = clone $_now;
                }
                try {
                    $_toDate = new Zend_Date($filter['to_date'], 'dd/MM/y');
                    if ($_fromDate->compare($_toDate) > 0) {
                        $_reversed = true;
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aw_hdu3')->__('Please select correct "To" date value'));
                    $_toDate = clone $_now;
                }
                if ($_reversed) {
                    $fromDate = $_toDate;
                    $toDate = $_fromDate;
                }
                else {
                    $fromDate = $_fromDate;
                    $toDate = $_toDate;
                }
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Agent_View_Chart::PERIOD_LAST_SEVEN_DAYS:
                $fromDate->subDay(6);
                break;
            case AW_Helpdesk3_Block_Adminhtml_Statistic_Agent_View_Chart::PERIOD_LAST_THIRTY_DAYS:
                $fromDate->subDay(29);
                break;
        }
        $filter['from_date'] = $fromDate;
        $filter['to_date'] = $toDate;
        if (!array_key_exists('group', $filter)) {
            $filter['group'] = AW_Helpdesk3_Block_Adminhtml_Statistic_Status_View_Chart::GROUP_BY_WEEK;
        }
        if (!array_key_exists('departments[]', $filter)) {
            $filter['departments[]'] = Mage::getModel('aw_hdu3/department')->getCollection()->addActiveFilter()->addNotDeletedFilter()->getAllIds();
        }
        if (!array_key_exists('status[]', $filter)) {
            $filter['status[]'] = Mage::getModel('aw_hdu3/ticket_status')->getCollection()->addActiveFilter()->addNotDeletedFilter()->getAllIds();
        }
        return $filter;
    }
}
