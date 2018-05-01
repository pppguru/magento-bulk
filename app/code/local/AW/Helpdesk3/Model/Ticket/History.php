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
 * Class AW_Helpdesk3_Model_Ticket_History
 * @method string getId()
 * @method string getTicketId()
 * @method string getInitiatorDepartmentAgentId()
 * @method string getEventType()
 * @method string getEventData()
 * @method string getIsSystem()
 * @method string getCreatedAt()
 */
class AW_Helpdesk3_Model_Ticket_History extends Mage_Core_Model_Abstract
{
    const XML_EVENTS_NODE_NAME = 'aw_hdu3_ticket_history_event_type_models';

    protected $_events     = array();
    protected $_additional = null;
    protected $_message    = null;
    protected $_createdAt  = null; //needed same datetime for all history events

    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/ticket_history');
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_History_Attachment_Collection
     */
    public function getAttachmentCollection()
    {
        $attachmentCollection = Mage::getModel('aw_hdu3/ticket_history_attachment')->getCollection();
        $attachmentCollection->addFilterByHistoryId($this->getId());
        return $attachmentCollection;
    }

    /**
     * @param AW_Helpdesk3_Model_Gateway_Mail_Attachment $attach
     *
     * @return $this
     */
    public function addMailAttach($attach)
    {
        $historyAttach = Mage::getModel('aw_hdu3/ticket_history_attachment');
        $historyAttach
            ->setStoreId($this->getTicket()->getStoreId())
            ->setFile($attach->getFileRealName(), file_get_contents($attach->getFilePath()))
        ;
        $this->addAttachment($historyAttach);
        return $this;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History_Attachment $attach
     *
     * @return $this
     */
    public function addAttachment($attach)
    {
        $attach
            ->setTicketHistoryId($this->getId())
            ->save()
        ;
        return $this;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return $this
     */
    public function ticketAfterSave($ticket)
    {
        foreach ($this->_getEvents() as $event) {
            if ($event->isEventHappened($ticket)) {
                $ticket->addHistory($event->getType(), $event->getEventData());
            }
        }
        return $this;
    }

    /**
     * @param int $type
     * @param $data
     *
     * @return $this
     */
    public function processEvent($type, $data)
    {
        $event = $this->_getEventByType($type);
        $eventData = $data;

        //history event data should be without attachments
        if (array_key_exists('attachments', $eventData)) {
            unset($eventData['attachments']);
        }
        if (null !== $event) {
            $this
                ->setTicketId($this->getTicket()->getId())
                ->setEventType($event->getType())
                ->setEventData($eventData)
                ->setInitiatorDepartmentAgentId(Mage::helper('aw_hdu3/ticket')->getCurrentDepartmentAgent()->getId())
                ->setIsSystem($event->isSystem())
                ->setCreatedAt($this->_getCreatedAt())
                ->save()
            ;
            $event
                ->process($data, $this)
            ;
        }
        return $this;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History_Event_Message $message
     *
     * @return $this
     */
    public function addMessage($message)
    {
        if (null === $this->getMessage()->getId()) {
            $message
                ->setTicketId($this->getTicketId())
                ->setHistoryId($this->getId())
                ->save()
            ;
            $this->_message = $message;
        }
        return $this;
    }

    /**
     * @return null | AW_Helpdesk3_Model_Ticket_History_Event_Message
     */
    public function getMessage()
    {
        if (null === $this->_message) {
            $messageCollection = Mage::getModel('aw_hdu3/ticket_message')->getCollection();
            $messageCollection->addFilterByHistoryId($this->getId());
            $this->_message = $messageCollection->getFirstItem();
        }
        return $this->_message;
    }

    /**
     * @param int $type
     *
     * @return null | AW_Helpdesk3_Model_Ticket_History_Event_Abstract
     */
    protected function _getEventByType($type)
    {
        foreach ($this->_getEvents() as $event) {
            if ($type == $event->getType()) {
                return $event;
            }
        }
        return null;
    }

    /**
     * @return array(0 => AW_Helpdesk3_Model_Ticket_History_Event_Abstract) from config.xml
     */
    protected function _getEvents()
    {
        if (empty($this->_events)) {
            foreach (Mage::getConfig()->getNode(self::XML_EVENTS_NODE_NAME)->asArray() as $moduleName => $nodeValue) {
                foreach (array_keys($nodeValue) as $eventModelName) {
                    $this->_events[] = Mage::getModel($moduleName . '/' . $eventModelName);
                }
            }
        }
        return $this->_events;
    }

    /**
     * @return $this
     */
    protected function _afterSave()
    {
        $_result = parent::_afterSave();
        $this->getResource()->saveAdditionalData($this);
        return $_result;
    }

    /**
     * @return Varien_Object
     */
    public function getAdditionalData()
    {
        if (null === $this->_additional) {
            $this->_additional = $this->getResource()->getAdditionalData($this);
        }
        return $this->_additional;
    }

    /**
     * @return string
     */
    protected function _getCreatedAt()
    {
        if (null === $this->_createdAt) {
            $currentDate = new Zend_Date();
            $this->_createdAt = $currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        return $this->_createdAt;
    }

    public function getTicket()
    {
        if (!$this->getData('ticket') instanceof AW_Helpdesk3_Model_Ticket) {
            $this->setData('ticket', Mage::getModel('aw_hdu3/ticket')->load($this->getTicketId()));
        }
        return $this->getData('ticket');
    }
}