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


class AW_Helpdesk3_Block_Adminhtml_Statistic_Workload_View_Agent extends Mage_Adminhtml_Block_Template
{
    protected $_agentHash = null;
    protected $_statusHash = null;
    protected $_dataCache = null;


    protected function _construct()
    {
        $this->setTemplate('aw_hdu3/statistic/workload/view/agent.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getAgentOptionHash()
    {
        if (null === $this->_agentHash) {
            $storeId = Mage::app()->getRequest()->getParam('store', 0);
            $this->_agentHash = array();
            foreach (Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter() as $agent) {
                $departmentCollection = $agent->getDepartmentCollection();
                if (!count($departmentCollection)) {
                    continue;
                }
                if ($storeId > 0) {
                    $departmentCollection->addFilterByStoreId($storeId);
                }
                /** @var AW_Helpdesk3_Model_Department_Agent $agent*/
                $this->_agentHash[$agent->getId()] = array(
                    'name' => $agent->getName(),
                    'departmentHash' => $departmentCollection->toOptionHash()
                );
            }
        }
        return $this->_agentHash;
    }

    /**
     * @return array
     */
    public function getStatusOptionHash()
    {
        if (null === $this->_statusHash) {
            $storeId = Mage::app()->getRequest()->getParam('store', 0);
            $this->_statusHash = AW_Helpdesk3_Model_Source_Ticket_Status::toOptionHash($storeId);
        }
        return $this->_statusHash;
    }

    public function getExtraStatusOptionHash()
    {
        $statusOptionHash = $this->getStatusOptionHash();
        unset($statusOptionHash[AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE]);
        unset($statusOptionHash[AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE]);
        unset($statusOptionHash[AW_Helpdesk3_Model_Source_Ticket_Status::WAITING_VALUE]);
        unset($statusOptionHash[AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE]);
        return $statusOptionHash;
    }

    /**
     * @param int $agentId
     * @param null|int $statusId
     * @param null|int $priorityId
     *
     * @return int
     */
    public function getValue($agentId, $statusId = null, $priorityId = null)
    {
        if (null === $this->_dataCache) {
            $storeId = Mage::app()->getRequest()->getParam('store', 0);
            $this->_dataCache = Mage::helper('aw_hdu3/statistics')->getWorkloadStatisticsByAgents($storeId);
        }
        if (!array_key_exists($agentId, $this->_dataCache)) {
            return 0;
        }
        $agentData = $this->_dataCache[$agentId];
        $statusData = array();
        if (null !== $statusId) {
            if (!array_key_exists($statusId, $agentData)) {
                return 0;
            }
            $statusData = $agentData[$statusId];
        } else { //if statusId === null
            foreach ($agentData as $item) {
                foreach ($item as $priorityId => $value) {
                    $statusData[$priorityId] += $value;
                }
            }
        }

        if (null === $priorityId) {
            return array_sum(array_values($statusData));
        }
        return intval($statusData[$priorityId]);
    }

    /**
     * @param int $agentId
     *
     * @return int
     */
    public function getNewTicketCountForAgent($agentId)
    {
        return $this->getValue($agentId, AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE);
    }

    /**
     * @param int $agentId
     *
     * @return int
     */
    public function getOpenTicketCountForAgent($agentId)
    {
        return $this->getValue($agentId, AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE);
    }

    /**
     * @param int $agentId
     *
     * @return int
     */
    public function getUrgentTicketCountForAgent($agentId)
    {
        $newUrgentValue = $this->getValue(
            $agentId, AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::URGENT_VALUE
        );
        $openUrgentValue = $this->getValue(
            $agentId, AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::URGENT_VALUE
        );
        return $newUrgentValue + $openUrgentValue;
    }

    /**
     * @param int $agentId
     *
     * @return int
     */
    public function getASAPTicketCountForAgent($agentId)
    {
        $newASAPValue = $this->getValue(
            $agentId, AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::ASAP_VALUE
        );
        $openASAPValue = $this->getValue(
            $agentId, AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::ASAP_VALUE
        );
        return $newASAPValue + $openASAPValue;
    }

    /**
     * @param int $agentId
     *
     * @return int
     */
    public function getWaitingTicketCountForAgent($agentId)
    {
        return $this->getValue($agentId, AW_Helpdesk3_Model_Source_Ticket_Status::WAITING_VALUE);
    }

    public function getTicketGridFilterUrl($agentId, $statusId, $priority = null)
    {
        $params = array(
            'department_agent_id' => 'department_agent_id=' . $agentId,
            'status'              => 'status=' . $statusId
        );
        if ($priority) {
            $params['priority'] = 'priority=' . $priority;
        }
        return $this->getUrl('aw_hdu3_admin/adminhtml_ticket/list',
            array(
                'filter' => base64_encode(implode('&', $params))
            )
        );
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
            $this->__('New and Open Tickets'),
            $this->__('New Tickets'),
            $this->__('Open Tickets'),
            $this->__('Urgent Tickets'),
            $this->__('ASAP Tickets'),
            $this->__('Waiting for Customer Tickets')
        );
        foreach ($this->getExtraStatusOptionHash() as $statusLabel) {
            $data['headers'][] = $this->__('%s Tickets', $statusLabel);
        }

        $data['items'] = array();
        foreach ($this->getAgentOptionHash() as $agentId => $agentData) {
            $data['items'][$agentId] = array();
            $data['items'][$agentId][] = $agentData['name'];
            $data['items'][$agentId][] = implode(',', $agentData['departmentHash']);
            $data['items'][$agentId][] = $this->getNewTicketCountForAgent($agentId) + $this->getOpenTicketCountForAgent($agentId);
            $data['items'][$agentId][] = $this->getNewTicketCountForAgent($agentId);
            $data['items'][$agentId][] = $this->getOpenTicketCountForAgent($agentId);
            $data['items'][$agentId][] = $this->getUrgentTicketCountForAgent($agentId);
            $data['items'][$agentId][] = $this->getASAPTicketCountForAgent($agentId);
            $data['items'][$agentId][] = $this->getWaitingTicketCountForAgent($agentId);

            foreach ($this->getExtraStatusOptionHash() as $statusId => $statusLabel) {
                $data['items'][$agentId][] = $this->getValue($agentId, $statusId);
            }
        }
        return $data;
    }

    public function getExportCsvUrl()
    {
        return $this->getUrl('*/*/exportAgentCsv');
    }

    public function getExportExcelUrl()
    {
        return $this->getUrl('*/*/exportAgentExcelXml');
    }
}
