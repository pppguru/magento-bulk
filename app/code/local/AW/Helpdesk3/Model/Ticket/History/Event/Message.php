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


class AW_Helpdesk3_Model_Ticket_History_Event_Message extends AW_Helpdesk3_Model_Ticket_History_Event_Abstract
{
    const TYPE = 4;

    public function getType()
    {
        return self::TYPE;
    }

    public function setEventData($data)
    {
        return $this;
    }

    public function getEventData()
    {
        return array();
    }

    public function isSystem()
    {
        return false;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return false
     */
    public function isEventHappened($ticket)
    {
        return false;
    }

    /**
     * @param string | array ('content', array('attachments')) $data
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function process($data, $history)
    {
        $this->_createMessage($data, $history);
        $this->_sendNotification($history);
        return $this;
    }

    protected function _createMessage($data, $history)
    {
        $content = '';
        if (is_string($data)) {
            $content = $data;
        }
        if (is_array($data)) {
            if (array_key_exists('attachments', $data)) {
                foreach ($data['attachments'] as $attach) {
                    if ($attach instanceof AW_Helpdesk3_Model_Gateway_Mail_Attachment) {
                        $history->addMailAttach($attach);
                    }
                    if ($attach instanceof AW_Helpdesk3_Model_Ticket_History_Attachment) {
                        $history->addAttachment($attach);
                    }
                }
            }
            if (array_key_exists('content', $data)) {
                $content = $data['content'];
            }
        }
        $message = Mage::getModel('aw_hdu3/ticket_message');
        $message->setContent($content);
        $history->addMessage($message);
        return $this;
    }

    protected function _sendNotification($history)
    {
        $notification = $history->getTicket()->getDepartment()->getEmailNotification();
        if ($history->getTicket()->isObjectNew()) {
            if ($history->getInitiatorDepartmentAgentId()) {
                //created by admin
                $notification->sendToCustomerNotificationNewTicketByAdmin($history);
            } else {
                //created by customer
                $notification->sendToAdminNotificationNewTicket($history);
                $notification->sendToCustomerNotificationNewTicket($history);
            }
        } else {
            if ($history->getInitiatorDepartmentAgentId()) {
                //reply from admin
                $notification->sendToCustomerNewReply($history);
            } else {
                //reply from customer
                $notification->sendToAdminNewReply($history);
            }
        }
        return $this;
    }
}