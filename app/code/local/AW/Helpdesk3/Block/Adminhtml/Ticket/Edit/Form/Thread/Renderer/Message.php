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


/**
 * Class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Renderer_Message
 * @method AW_Helpdesk3_Model_Ticket_History getEvent()
 * @method bool getIsShort()
 */
class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Renderer_Message extends Mage_Adminhtml_Block_Template
{
    protected function _toHtml()
    {
        $this->setTemplate('aw_hdu3/ticket/edit/container/thread/message/full.phtml');
        if ($this->getIsShort()) {
            $this->setTemplate('aw_hdu3/ticket/edit/container/thread/message/short.phtml');
        }
        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getEventInitiatorName()
    {
        $agentId = $this->getEvent()->getData('initiator_department_agent_id');
        if ($agentId) {
            /** @var AW_Helpdesk3_Model_Department_Agent $agent */
            $agent = Mage::getModel('aw_hdu3/department_agent')->load($agentId);
            return $agent->getName();
        }
        return $this->getEvent()->getTicket()->getCustomerName();
    }

    /**
     * @return string
     */
    public function getMessageText()
    {
        $message = $this->getEvent()->getEventData('content');
        if (!$this->isAdminMessage()) {
            $message = nl2br($this->escapeHtml($message));
        }
        $url = $this->getUrl('helpdesk_admin/adminhtml_ticket/edit', array('id' => '{id}'));
        $linkTemplate = "<a href='$url'>{uid}</a>";
        $message = Mage::helper('aw_hdu3/ticket')->replaceUidToLink($message, $linkTemplate);
        return $message;
    }

    /**
     * @return bool
     */
    public function isAdminMessage()
    {
        $agentId = $this->getEvent()->getData('initiator_department_agent_id');
        return !!$agentId;
    }
}