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


class AW_Helpdesk3_Block_Adminhtml_Statistic_Workload_View_Department extends Mage_Adminhtml_Block_Template
{
    protected $_departmentHash = null;
    protected $_statusHash = null;
    protected $_dataCache = null;

    protected function _construct()
    {
        $this->setTemplate('aw_hdu3/statistic/workload/view/department.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getDepartmentOptionHash()
    {
        if (null === $this->_departmentHash) {
            $collection = Mage::getModel('aw_hdu3/department')->getCollection()->addActiveFilter()->addNotDeletedFilter();
            $storeId = Mage::app()->getRequest()->getParam('store', 0);
            if ($storeId > 0) {
                $collection->addFilterByStoreId($storeId);
            }
            $this->_departmentHash = $collection->toOptionHash();
        }
        return $this->_departmentHash;
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
     * @param int $departmentId
     * @param null|int $statusId
     * @param null|int $priorityId
     *
     * @return int
     */
    public function getValue($departmentId, $statusId = null, $priorityId = null)
    {
        if (null === $this->_dataCache) {
            $storeId = Mage::app()->getRequest()->getParam('store', 0);
            $this->_dataCache = Mage::helper('aw_hdu3/statistics')->getWorkloadStatisticsByDepartments($storeId);
        }
        if (!array_key_exists($departmentId, $this->_dataCache)) {
            return 0;
        }
        $departmentData = $this->_dataCache[$departmentId];
        $statusData = array();
        if (null !== $statusId) {
            if (!array_key_exists($statusId, $departmentData)) {
                return 0;
            }
            $statusData = $departmentData[$statusId];
        } else { //if statusId === null
            foreach ($departmentData as $item) {
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
     * @param int $departmentId
     *
     * @return int
     */
    public function getNewTicketCountForDepartment($departmentId)
    {
        return $this->getValue($departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE);
    }

    /**
     * @param int $departmentId
     *
     * @return int
     */
    public function getOpenTicketCountForDepartment($departmentId)
    {
        return $this->getValue($departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE);
    }

    /**
     * @param int $departmentId
     *
     * @return int
     */
    public function getUrgentTicketCountForDepartment($departmentId)
    {
        $newUrgentValue = $this->getValue(
            $departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::URGENT_VALUE
        );
        $openUrgentValue = $this->getValue(
            $departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::URGENT_VALUE
        );
        return $newUrgentValue + $openUrgentValue;
    }

    /**
     * @param int $departmentId
     *
     * @return int
     */
    public function getASAPTicketCountForDepartment($departmentId)
    {
        $newASAPValue = $this->getValue(
            $departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::ASAP_VALUE
        );
        $openASAPValue = $this->getValue(
            $departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE,
            AW_Helpdesk3_Model_Source_Ticket_Priority::ASAP_VALUE
        );
        return $newASAPValue + $openASAPValue;
    }

    /**
     * @param int $departmentId
     *
     * @return int
     */
    public function getWaitingTicketCountForDepartment($departmentId)
    {
        return $this->getValue($departmentId, AW_Helpdesk3_Model_Source_Ticket_Status::WAITING_VALUE);
    }

    public function getTicketGridFilterUrl($departmentId, $statusId, $priority = null)
    {
        $params = array(
            'department_id' => 'department=' . $departmentId,
            'status'        => 'status=' . $statusId
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
            $this->__('Department Name'),
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
        foreach ($this->getDepartmentOptionHash() as $departmentId => $departmentLabel) {
            $data['items'][$departmentId] = array();
            $data['items'][$departmentId][] = $departmentLabel;
            $data['items'][$departmentId][] = $this->getNewTicketCountForDepartment($departmentId) + $this->getOpenTicketCountForDepartment($departmentId);
            $data['items'][$departmentId][] = $this->getNewTicketCountForDepartment($departmentId);
            $data['items'][$departmentId][] = $this->getOpenTicketCountForDepartment($departmentId);
            $data['items'][$departmentId][] = $this->getUrgentTicketCountForDepartment($departmentId);
            $data['items'][$departmentId][] = $this->getASAPTicketCountForDepartment($departmentId);
            $data['items'][$departmentId][] = $this->getWaitingTicketCountForDepartment($departmentId);;

            foreach ($this->getExtraStatusOptionHash() as $statusId => $statusLabel) {
                $data['items'][$departmentId][] = $this->getValue($departmentId, $statusId);
            }
        }
        return $data;
    }

    public function getExportCsvUrl()
    {
        return $this->getUrl('*/*/exportDepCsv');
    }

    public function getExportExcelUrl()
    {
        return $this->getUrl('*/*/exportDepExcelXml');
    }
}