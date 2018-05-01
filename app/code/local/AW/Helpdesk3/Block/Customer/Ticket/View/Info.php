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


class AW_Helpdesk3_Block_Customer_Ticket_View_Info extends Mage_Core_Block_Template
{
    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->getTicket()->getDepartment()->getTitle();
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        $storeId = Mage::app()->getStore()->getId();
        return $this->getTicket()->getStatusLabel($storeId);
    }

    /**
     * @return string
     */
    public function getPriorityLabel()
    {
        $storeId = Mage::app()->getStore()->getId();
        return $this->getTicket()->getPriorityLabel($storeId);
    }

    /**
     * @return string
     */
    public function getOrderLabel()
    {
        $orderLabel = $this->getTicket()->getOrderIncrementId();
        if ($orderLabel) {
            $orderLabel = '#' . $orderLabel;
        }
        return $orderLabel;
    }

    /**
     * @return string
     */
    public function getOrderUrl()
    {
        $orderIncrementId = $this->getTicket()->getOrderIncrementId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        return $this->getUrl('sales/order/view', array('order_id' => $order->getEntityId()));
    }

    /**
     * @return bool
     */
    public function isCanShowDepartment()
    {
        /** @var AW_Helpdesk3_Helper_Config $config */
        $config = Mage::helper('aw_hdu3/config');
        return $config->isCanShowCurrentDepartmentOnTicketPage();
    }

    /**
     * @return bool
     */
    public function isCanShowPriority()
    {
        /** @var AW_Helpdesk3_Helper_Config $config */
        $config = Mage::helper('aw_hdu3/config');
        return $config->isCanShowCurrentPriorityOnTicketPage();
    }

    public function isAllowRate()
    {
        return Mage::helper('aw_hdu3/config')->isAllowRate();
    }

    public function customerCanVote()
    {
        return $this->getTicket()->customerCanVote();
    }

    public function getExternalKey()
    {
        $ticket = $this->getTicket();
        return Mage::helper('aw_hdu3/ticket')->encryptExternalKey($ticket->getCustomerEmail(), $ticket->getId());
    }

    public function getRate()
    {
        return $this->getTicket()->getRate() * 10;
    }
}