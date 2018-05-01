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


abstract class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Abstract extends Mage_Adminhtml_Block_Template
{
    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_History_Collection
     */
    abstract public function getEventCollection();

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $event
     * @param bool $isShort
     *
     * @return Mage_Adminhtml_Block_Abstract
     * @throws Exception
     */
    public function getRendererByEvent(AW_Helpdesk3_Model_Ticket_History $event, $isShort = false)
    {
        $rendererList = array(
            AW_Helpdesk3_Model_Ticket_History_Event_Assignee::TYPE   => 'Assignee',
            AW_Helpdesk3_Model_Ticket_History_Event_Department::TYPE => 'Department',
            AW_Helpdesk3_Model_Ticket_History_Event_Escalate::TYPE   => 'Escalate',
            AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE    => 'Message',
            AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE       => 'Note',
            AW_Helpdesk3_Model_Ticket_History_Event_Priority::TYPE   => 'Priority',
            AW_Helpdesk3_Model_Ticket_History_Event_Status::TYPE     => 'Status',
        );
        $eventType = (int)$event->getEventType();
        if (!array_key_exists($eventType, $rendererList)) {
            throw new Exception('Unexpected event type');
        }
        $blockName = 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Renderer_' . $rendererList[$eventType];
        $attributes = array(
            'event'    => $event,
            'is_short' => $isShort
        );
        return $this->getLayout()->createBlock($blockName, '', $attributes);
    }
}