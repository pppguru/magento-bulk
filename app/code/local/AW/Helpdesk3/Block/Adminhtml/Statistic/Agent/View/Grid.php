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


class AW_Helpdesk3_Block_Adminhtml_Statistic_Agent_View_Grid extends Mage_Adminhtml_Block_Template
{
    protected $_agentHash = null;
    protected $_firstReplyAverageCache = null;
    protected $_closingTimeAverageCache = null;


    protected function _construct()
    {
        $this->setTemplate('aw_hdu3/statistic/agent/view/grid.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getAgentOptionHash()
    {
        if (null === $this->_agentHash) {
            $this->_agentHash = array();
            foreach (Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter() as $agent) {
                $departmentCollection = $agent->getDepartmentCollection();
                if (!count($departmentCollection)) {
                    continue;
                }
                /** @var AW_Helpdesk3_Model_Department_Agent $agent*/
                $this->_agentHash[$agent->getId()] = array(
                    'name' => $agent->getName(),
                    'departmentHash' => $agent->getDepartmentCollection()->toOptionHash()
                );
            }
        }
        return $this->_agentHash;
    }

    /**
     * @param int|null $agentId
     *
     * @return int
     */
    public function getAverageTimeOfFirstReply($agentId = null)
    {
        if (null === $this->_firstReplyAverageCache) {
            $filter = Mage::registry('current_filter');
            /** @var Zend_Date $from */
            $from = $filter['from_date'];
            $from = $from->toString('Y-MM-dd');
            /** @var Zend_Date $to */
            $to = $filter['to_date'];
            $to = $to->toString('Y-MM-dd');
            $agentIds = $filter['agents[]'];
            $storeId = Mage::registry('current_store');
            $this->_firstReplyAverageCache = Mage::helper('aw_hdu3/statistics')->getFirstReplyByAgentsAvgTime(
                $from, $to, $agentIds, $storeId
            );
        }
        if (null !== $agentId && !array_key_exists($agentId, $this->_firstReplyAverageCache)) {
            return 0;
        }
        $data = array();
        $firstReplyAverageData = $this->_firstReplyAverageCache;
        if (null != $agentId) {
            $firstReplyAverageData = array();
            $firstReplyAverageData[$agentId] = $this->_firstReplyAverageCache[$agentId];
        }
        foreach ($firstReplyAverageData as $agentId => $agentData) {
            $data[$agentId] = array();
            foreach ($agentData as $value) {
                if ($value > 0) {
                    $data[$agentId][] = $value;
                }
            }
            if (array_sum($data[$agentId]) == 0) {
                $data[$agentId][] = 0;
            }
        }
        $total = array();
        foreach ($data as $agentData) {
            $total[] = Mage::helper('aw_hdu3/statistics')->calculateMedianFromArray($agentData);
        }
        return Mage::helper('aw_hdu3/statistics')->calculateMedianFromArray($total);
    }

    /**
     * @param int|null $agentId
     *
     * @return int
     */
    public function getAverageTimeOfClosingTicket($agentId = null)
    {
        if (null === $this->_closingTimeAverageCache) {
            $filter = Mage::registry('current_filter');
            /** @var Zend_Date $from */
            $from = $filter['from_date'];
            $from = $from->toString('Y-MM-dd');
            /** @var Zend_Date $to */
            $to = $filter['to_date'];
            $to = $to->toString('Y-MM-dd');
            $agentIds = $filter['agents[]'];
            $storeId = Mage::registry('current_store');
            $this->_closingTimeAverageCache = Mage::helper('aw_hdu3/statistics')->getCloseTicketByAgentsAvgTime(
                $from, $to, $agentIds, $storeId
            );
        }
        if (null !== $agentId && !array_key_exists($agentId, $this->_closingTimeAverageCache)) {
            return 0;
        }
        $data = array();
        $closingTimeAverageData = $this->_closingTimeAverageCache;
        if (null != $agentId) {
            $closingTimeAverageData = array();
            $closingTimeAverageData[$agentId] = $this->_closingTimeAverageCache[$agentId];
        }
        foreach ($closingTimeAverageData as $agentId => $agentData) {
            $data[$agentId] = array();
            foreach ($agentData as $value) {
                if ($value > 0) {
                    $data[$agentId][] = $value;
                }
            }
            if (array_sum($data[$agentId]) == 0) {
                $data[$agentId][] = 0;
            }
        }
        $total = array();
        foreach ($data as $agentData) {
            $total[] = Mage::helper('aw_hdu3/statistics')->calculateMedianFromArray($agentData);
        }
        return Mage::helper('aw_hdu3/statistics')->calculateMedianFromArray($total);
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
        if (!$currentAgentList) {
            return true;
        }
        return in_array($agentId, $currentAgentList);
    }

    public function getCsv()
    {
        $data = $this->_prepareExportData();
        return Mage::helper('aw_hdu3/export')->getCsv($data);
    }

    public function getExcel($filename = '')
    {
        $data = $this->_prepareExportData();
        return Mage::helper('aw_hdu3/export')->getExcel($data, $filename);
    }

    protected function _prepareExportData() {
        $data = array();
        $data['headers'] = array(
            $this->__('Agent Name'),
            $this->__('Department(s)'),
            $this->__('Average Time of First Reply (hrs)'),
            $this->__('Average Time of Closing Tickets (hrs)')
        );

        $data['items'] = array();

        foreach ($this->getAgentOptionHash() as $agentId => $agentData) {
            if($this->isAgentSelected($agentId)) {
                $data['items'][$agentId] = array();
                $data['items'][$agentId][] = $agentData['name'];
                $data['items'][$agentId][] = implode(',', $agentData['departmentHash']);
                $data['items'][$agentId][] = round($this->getAverageTimeOfFirstReply($agentId), 1);
                $data['items'][$agentId][] = round($this->getAverageTimeOfClosingTicket($agentId), 1);
            }
        }
        $data['totals'] = array();
        $data['totals'][] = $this->__('Median');
        $data['totals'][] = '';
        $data['totals'][] = round($this->getAverageTimeOfFirstReply(), 1);
        $data['totals'][] = round($this->getAverageTimeOfClosingTicket(), 1);
        return $data;
    }

    public function getExportCsvUrl()
    {
        return $this->getUrl('*/*/exportCsv', array('filter' => Mage::app()->getRequest()->getParam('filter')));
    }

    public function getExportExcelUrl()
    {
        return $this->getUrl('*/*/exportExcelXml', array('filter' => Mage::app()->getRequest()->getParam('filter')));
    }
}
