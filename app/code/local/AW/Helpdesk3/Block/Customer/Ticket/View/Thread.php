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


class AW_Helpdesk3_Block_Customer_Ticket_View_Thread extends Mage_Core_Block_Template
{

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    public function getExternalKey()
    {
        $ticket = $this->getTicket();
        return base64_encode(Mage::helper('core')->encrypt($ticket->getCustomerEmail() . ',' . $ticket->getId()));
    }

    /**
     * result = array(
     * 'color' => string
     * 'author' => string,
     * 'date' => string, //in UTC
     * 'content' => string|null,
     * 'attachment_collection' => AW_Helpdesk3_Model_Resource_Ticket_History_Attachment_Collection
     * 'additional_text' => string|null,
     * )
     *
     * @return array
     */
    public function getTicketHistory()
    {
        $historyCollection = $this->getTicket()->getHistoryCollection();
        if (!Mage::helper('aw_hdu3/config')->isCanShowSystemMessageOnTicketPage()) {
            $historyCollection->addFilterOnNotSystemMessageOnly();
        }
        $historyCollection->addFieldToFilter(
            'event_type',
            array(
                'nin' => array(
                    AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE
                )
            )
        );
        $historyCollection->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_DESC);
        $result = array();
        foreach ($historyCollection as $item) {
            /** @var AW_Helpdesk3_Model_Ticket_History $item */
            $data = array();
            $data['author'] = $this->getTicket()->getCustomerName();
            if ($agentId = $item->getData('initiator_department_agent_id')) {
                $agent = Mage::getModel('aw_hdu3/department_agent')->load($agentId);
                $data['author'] = $agent->getName();
            }
            $data['date'] = $item->getData('created_at');
            $data['color'] = $this->_getColorByEvent($item);
            $data['content'] = $this->_getContentByEvent($item);
            $data['attachment_collection'] = $item->getAttachmentCollection();
            $data['additional_text'] = $this->_getAdditionalTextByEvent($item);
            $result[] = $data;
        }

        for ($i = 1; $i < count($result); $i++) {
            if (!array_key_exists($i - 1, $result)) {
                continue;
            }
            $previousItem = $result[$i - 1];
            $currentItem = $result[$i];
            if (
                $previousItem['date'] !== $currentItem['date']
                || $previousItem['author'] !== $currentItem['author']
            ) {
                continue;
            }
            $masterItemIndex = $i - 1;;
            $slaveItemIndex = $i;
            if (null === $previousItem['content']) {
                $masterItemIndex = $i;
                $slaveItemIndex = $i - 1;
            }
            $result[$masterItemIndex]['additional_text'] = $result[$slaveItemIndex]['additional_text'];
            unset($result[$slaveItemIndex]);
        }
        return $result;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $event
     *
     * @return string
     */
    protected function _getColorByEvent(AW_Helpdesk3_Model_Ticket_History $event)
    {
        $color = "#FFFFFF";
        switch ($event->getEventType()) {
            case AW_Helpdesk3_Model_Ticket_History_Event_Assignee::TYPE:
            case AW_Helpdesk3_Model_Ticket_History_Event_Department::TYPE:
            case AW_Helpdesk3_Model_Ticket_History_Event_Status::TYPE:
            case AW_Helpdesk3_Model_Ticket_History_Event_Priority::TYPE:
                $color = "#F4F3E7";
                break;
            case AW_Helpdesk3_Model_Ticket_History_Event_Escalate::TYPE:
                $color = "#FCDBDB";
                break;
            case AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE:
                $color = "#E8FBD4";
                if (!$event->getData('initiator_department_agent_id')) {
                    $color = "#EDFAFF";
                }
                break;
        }
        return $color;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $event
     *
     * @return string|null
     */
    protected function _getContentByEvent(AW_Helpdesk3_Model_Ticket_History $event)
    {
        $content = null;
        if ($event->getEventType() ==  AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE) {
            $eventData = $event->getEventData();
            $content = $eventData['content'];
            if (null === $content && $event->getAttachmentCollection()->getSize() > 0) {
                $content = '';
            }
            if (!$event->getData('initiator_department_agent_id')) {
                $content = nl2br($this->escapeHtml($content));
            }
            //parse UID to link
            $url = $this->getUrl('helpdesk/customer/viewTicket', array('id' => '{id}'));
            $linkTemplate = "<a href='$url'>{uid}</a>";
            $content = Mage::helper('aw_hdu3/ticket')->replaceUidToLink(
                $content, $linkTemplate, $event->getTicket()->getCustomerEmail()
            );
        }
        return $content;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $event
     *
     * @return string|null
     */
    protected function _getAdditionalTextByEvent(AW_Helpdesk3_Model_Ticket_History $event)
    {
        $eventData = $event->getEventData();
        $text = null;
        switch ($event->getEventType()) {
            case AW_Helpdesk3_Model_Ticket_History_Event_Assignee::TYPE:
                $agent = Mage::getModel('aw_hdu3/department_agent')->load($eventData['to']);
                $agentLabel = $agent->getName();
                $text = $this->__('Assignee changed to %s', "<b>{$agentLabel}</b>");
                break;
            case AW_Helpdesk3_Model_Ticket_History_Event_Department::TYPE:
                $department = Mage::getModel('aw_hdu3/department')->load($eventData['to']);
                $departmentLabel = $department->getTitle();
                $text = $this->__('Department changed to %s', "<b>{$departmentLabel}</b>");
                break;
            case AW_Helpdesk3_Model_Ticket_History_Event_Status::TYPE:
                $status = Mage::getModel('aw_hdu3/ticket_status')->load($eventData['to']);
                $statusLabel = $status->getTitle(Mage::app()->getStore()->getId());
                $text = $this->__('Status changed to %s', "<b>{$statusLabel}</b>");
                break;
            case AW_Helpdesk3_Model_Ticket_History_Event_Priority::TYPE:
                $priority = Mage::getModel('aw_hdu3/ticket_priority')->load($eventData['to']);
                $priorityLabel = $priority->getTitle(Mage::app()->getStore()->getId());
                $text = $this->__('Priority changed to %s', "<b>{$priorityLabel}</b>");
                break;
            case AW_Helpdesk3_Model_Ticket_History_Event_Escalate::TYPE:
                $text = $this->__('Ticket has been escalated');
                break;
        }
        return $text;
    }
}