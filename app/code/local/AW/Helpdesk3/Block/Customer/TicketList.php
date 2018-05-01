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


class AW_Helpdesk3_Block_Customer_TicketList extends Mage_Core_Block_Template
{

    protected $_collection;
    /**
     * @return AW_Helpdesk3_Block_Customer_TicketList
     */
    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Pager $toolbar */
        $toolbar = $this->getLayout()->createBlock('page/html_pager', 'customer_review_list.toolbar');
        $toolbar->setCollection($this->getCollection());
        $this->setChild('toolbar', $toolbar);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            $this->_collection = Mage::getModel('aw_hdu3/ticket')->getCollection();
            $this->_collection->addNotArchivedFilter();
            $this->_collection->addFilterByCustomerEmail($customerEmail);
            $this->_collection->setOrderByStatus();
            $this->_collection->setOrderByUpdatedAt();
        }

        return $this->_collection;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getUrlToTicketView(AW_Helpdesk3_Model_Ticket $ticket)
    {
        return $this->getUrl('helpdesk/customer/viewTicket',
            array(
                'id' => $ticket->getId(),
                '_secure' => Mage::app()->getStore(true)->isCurrentlySecure()
            )
        );
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getTicketStatusLabel(AW_Helpdesk3_Model_Ticket $ticket)
    {
        return $ticket->getStatusLabel();
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     * @param int $symbolCount
     *
     * @return string
     */
    public function getTruncatedTicketSubject(AW_Helpdesk3_Model_Ticket $ticket, $symbolCount)
    {
        return Mage::helper('core/string')->truncate($this->escapeHtml($ticket->getSubject()), $symbolCount);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return int
     */
    public function getTicketRepliesCount(AW_Helpdesk3_Model_Ticket $ticket)
    {
        $collection = Mage::getModel('aw_hdu3/ticket_message')->getCollection();
        $collection->addFilterByTicketId($ticket->getId());
        return max($collection->getSize() - 1, 0);
    }

}
