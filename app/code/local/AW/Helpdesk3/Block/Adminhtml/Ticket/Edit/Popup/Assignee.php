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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Popup_Assignee extends Mage_Adminhtml_Block_Template
{
    protected $_ticket = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_hdu3/ticket/edit/popup/assignee.phtml');
    }

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        if (null === $this->_ticket) {
            $this->_ticket = Mage::registry('current_ticket');
        }
        return $this->_ticket;
    }

    public function setTicket($ticket)
    {
        $this->_ticket = $ticket;
        return $this;
    }

    /**
     * @return array
     */
    public function getDepartmentCollectionOptionHash()
    {
        $collection = Mage::getModel('aw_hdu3/department')->getCollection()->addActiveFilter();
        return $collection->toOptionHash();
    }

    /**
     * @return array
     */
    public function getAgentCollectionOptionHash()
    {
        $collection = Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter();
        return $collection->toOptionHash();
    }

    /**
     * @return array
     */
    public function getStatusOptionArray()
    {
        $statusList = AW_Helpdesk3_Model_Source_Ticket_Status::toOptionArray(Mage::app()->getStore()->getId());
        foreach ($statusList as $key => $status) {
            if ($status['value'] == AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE) {
                unset($statusList[$key]);
                break;
            }
        }
        return $statusList;
    }

    /**
     * @return array
     */
    public function getPriorityOptionArray()
    {
        return AW_Helpdesk3_Model_Source_Ticket_Priority::toOptionArray(Mage::app()->getStore()->getId());
    }

    /**
     * @return array
     */
    public function getOrderOptionHash()
    {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFieldToFilter('customer_email', $this->getTicket()->getCustomerEmail());
        $result = array('' => $this->__('Unassigned'));
        foreach ($collection as $order) {
            /** @var Mage_Sales_Model_Order $order */
            $result[$order->getId()] = $order->getIncrementId();
            $result[$order->getId()] .= ', ' . $this->formatDate($order->getCreatedAtStoreDate());
            $result[$order->getId()] .= ', ' . $order->formatPrice($order->getGrandTotal());
        }
        return $result;
    }
}
