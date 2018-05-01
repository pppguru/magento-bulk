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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_CustomerTickets extends Mage_Adminhtml_Block_Template
{
    protected $_statusCacheData = null;
    protected $_priorityCacheData = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_hdu3/ticket/edit/container/customer_tickets.phtml');
    }

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return int
     */
    public function getTicketCount()
    {
        return $this->getTicketCollection()->getSize();
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_Collection
     */
    public function getTicketCollection()
    {
        $collection = Mage::getModel('aw_hdu3/ticket')->getCollection();
        $collection->addNotArchivedFilter();
        $collection->addFilterByCustomerEmail($this->getTicket()->getCustomerEmail());
        $collection->addFieldToFilter('id', array('neq' => $this->getTicket()->getId()));
        $collection->getSelect()->limit(5);
        $collection->addOrder('main_table.id', Varien_Data_Collection_Db::SORT_ORDER_DESC);
        return $collection;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getFirstTicketMessage(AW_Helpdesk3_Model_Ticket $ticket)
    {
        $message = Mage::helper('aw_hdu3/ticket')->getFirstTicketMessage($ticket);
        return $this->escapeHtml($message);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getTicketSubject(AW_Helpdesk3_Model_Ticket $ticket)
    {
        $subject = $ticket->getSubject();
        return $this->escapeHtml($subject);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getStatusLabelByTicket(AW_Helpdesk3_Model_Ticket $ticket)
    {
        return $ticket->getStatusLabel(Mage::app()->getStore()->getId());
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getPriorityLabelByTicket(AW_Helpdesk3_Model_Ticket $ticket)
    {
        return $ticket->getPriorityLabel(Mage::app()->getStore()->getId());
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getStyleForTicketStatus(AW_Helpdesk3_Model_Ticket $ticket)
    {
        if ($this->_statusCacheData === null) {
            /** @var AW_Helpdesk3_Model_Resource_Ticket_Status_Collection $collection */
            $collection = Mage::getModel('aw_hdu3/ticket_status')->getCollection()->addNotDeletedFilter();
            foreach ($collection->getData() as $value) {
                $this->_statusCacheData[$value['id']] = $value;
            }
        }
        $statusId = (int)$ticket->getStatus();
        if (!array_key_exists($statusId, $this->_statusCacheData)) {
            return '';
        }
        $style = '';
        if (!empty($this->_statusCacheData[$statusId]['background_color'])) {
            $bgColor = $this->_statusCacheData[$statusId]['background_color'];
            $bgColor = (strpos($bgColor, '#') === FALSE)?('#' . $bgColor):$bgColor;
            $style .= "background-color:{$bgColor};";
        }
        if (!empty($this->_statusCacheData[$statusId]['font_color'])) {
            $textColor = $this->_statusCacheData[$statusId]['font_color'];
            $textColor = (strpos($textColor, '#') === FALSE)?('#' . $textColor):$textColor;
            $style .= "color:{$textColor};";
        }
        return $style;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getStyleForTicketPriority(AW_Helpdesk3_Model_Ticket $ticket)
    {
        if ($this->_priorityCacheData === null) {
            /** @var AW_Helpdesk3_Model_Resource_Ticket_Priority_Collection $collection */
            $collection = Mage::getModel('aw_hdu3/ticket_priority')->getCollection()->addNotDeletedFilter();
            foreach ($collection->getData() as $value) {
                $this->_priorityCacheData[$value['id']] = $value;
            }
        }
        $priorityId = (int)$ticket->getPriority();
        if (!array_key_exists($priorityId, $this->_priorityCacheData)) {
            return '';
        }
        $style = '';
        if (!empty($this->_priorityCacheData[$priorityId]['background_color'])) {
            $bgColor = $this->_priorityCacheData[$priorityId]['background_color'];
            $bgColor = (strpos($bgColor, '#') === FALSE)?('#' . $bgColor):$bgColor;
            $style .= "background-color:{$bgColor};";
        }
        if (!empty($this->_priorityCacheData[$priorityId]['font_color'])) {
            $textColor = $this->_priorityCacheData[$priorityId]['font_color'];
            $textColor = (strpos($textColor, '#') === FALSE)?('#' . $textColor):$textColor;
            $style .= "color:{$textColor};";
        }
        return $style;
    }

}