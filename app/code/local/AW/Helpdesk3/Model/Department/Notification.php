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
 * Class AW_Helpdesk3_Model_Department_Notification
 * @method string getId()
 * @method string getDepartmentId()
 * @method string getSender()
 * @method string getToAdminNewTicketEmail()
 * @method string getToCustomerNewTicketEmail()
 * @method string getToCustomerNewTicketByAdminEmail()
 * @method string getToAdminNewReplyEmail()
 * @method string getToCustomerNewReplyEmail()
 * @method string getToPrimaryAgentReassignEmail()
 * @method string getToNewAssigneeReassignEmail()
 * @method string getToCustomerReassignEmail()
 */
class AW_Helpdesk3_Model_Department_Notification extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/department_notification');
    }

    /**
     * @param $departmentId
     *
     * @return $this
     */
    public function loadByDepartmentId($departmentId)
    {
        return $this->load($departmentId, 'department_id');
    }

    /**
     * @param string $template
     * @param string $emailList
     * @param string $recipientName
     * @param array(0=>AW_Helpdesk3_Model_Ticket_History_Attachment) $attachment
     * @param AW_Helpdesk3_Model_Ticket $ticket
     * @param array $variables
     *
     * @return $this
     */
    protected function _send($template, $emailList, $recipientName, $attachment = array(), $ticket, $variables = array())
    {
        if (empty($template) || empty($emailList)) {
            return $this;
        }

        //if $recipientEmail = one of gateway email-> return
        $emailList = explode(',', $emailList);
        foreach ($emailList as $recipientEmail) {
            $existGateway = Mage::getModel('aw_hdu3/gateway')->loadByEmail($recipientEmail);
            if (null !== $existGateway->getId()) {
                continue;
            }
            $mailTemplate = Mage::getModel('core/email_template');
            $this->_setCarbonCopy($mailTemplate, $ticket->getStoreId());
            $this->_processAttachments($mailTemplate, $attachment);
            $departmentGatewayEmail = $ticket->getDepartment()->getGateway()->getEmail();
            if (!$departmentGatewayEmail) {
                $defaultDepartmentId = Mage::getStoreConfig('aw_helpdesk3/general/default_department');
                $defaultDepartment = Mage::getModel('aw_hdu3/department')->load($defaultDepartmentId);
                if ($defaultDepartment) {
                    $departmentGatewayEmail = $defaultDepartment->getGateway()->getEmail();
                }
            }

            if ($departmentGatewayEmail) {
                $this->_setReplyTo($mailTemplate, $departmentGatewayEmail);
            }
            $mailTemplate
                ->setDesignConfig(array('area' => 'frontend', 'store' => $ticket->getStoreId()))
                ->sendTransactional(
                    $template,
                    $this->getSender(),
                    $recipientEmail,
                    $recipientName,
                    $variables,
                    $ticket->getStoreId()
                )
            ;
        }
        return $this;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToCustomerNotificationNewTicketByAdmin($history)
    {
        return $this->_sendNotificationToCustomer($this->getToCustomerNewTicketByAdminEmail(), $history);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToAdminNotificationNewTicket($history)
    {
        return $this->_sendNotificationToAdmin($this->getToAdminNewTicketEmail(), $history);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToCustomerNotificationNewTicket($history)
    {
        return $this->_sendNotificationToCustomer($this->getToCustomerNewTicketEmail(), $history);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToAdminNewReply($history)
    {
        return $this->_sendNotificationToAdmin($this->getToAdminNewReplyEmail(), $history);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToCustomerNewReply($history)
    {
        return $this->_sendNotificationToCustomer($this->getToCustomerNewReplyEmail(), $history);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToPrimaryAgentNotificationReassign($history)
    {
        $primaryAgent = $history->getTicket()->getDepartment()->getPrimaryAgent();
        $initiatorAgentName = Mage::helper('aw_hdu3/ticket')->getCurrentDepartmentAgent()->getName();
        $ticket     = $history->getTicket();
        return $this->_send($this->getToPrimaryAgentReassignEmail(),
            $primaryAgent->getEmail(),
            $primaryAgent->getName(),
            array(),
            $history->getTicket(),
            array_merge($this->getTicketEmailVariables($ticket),
                array(
                    'initiator_agent_name' => $initiatorAgentName,
                    'external_admin_url'   => $this->_getExternalAdminUrl($ticket)
                )
            )
        );
    }

    public function getTicketEmailVariables($ticket)
    {
        $storeDate = Mage::getModel('core/locale')->storeDate($ticket->getStoreId(), $ticket->getCreatedAt(), true);
        $department = $ticket->getDepartment();
        $agent      = $ticket->getDepartmentAgent();
        return array(
            'ticket_subject'       => $ticket->getSubject(),
            'ticket_priority'      => $ticket->getPriorityLabel(),
            'ticket_status'        => $ticket->getStatusLabel(),
            'ticket_created_at'    => $storeDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'ticket_first_message' => Mage::helper('aw_hdu3/ticket')->getFirstTicketMessage($ticket),
            'customer_name'        => $ticket->getCustomerName(),
            'ticket_uid'           => $ticket->getUid(),
            'agent_name'           => $agent->getName(),
            'department_title'     => $department->getTitle(),
            'customer_email'       => $ticket->getCustomerEmail(),
            'customer_first_name'  => $ticket->getCustomerFirstName()
        );
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToNewAssigneeNotificationReassign($history)
    {
        return $this->_sendNotificationToAdmin($this->getToNewAssigneeReassignEmail(), $history);
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return $this
     */
    public function sendToCustomerNotificationTicketChanged($ticket)
    {
        if (!$ticket->getIsDepartmentChanged() && !$ticket->getIsAgentChanged()
            && (!$ticket->getIsStatusChanged() || $ticket->getIsReply())
        ) {
            return $this;
        }
        return $this->_send($this->getToCustomerTicketChanged(),
            $ticket->getCustomerEmail(),
            $ticket->getCustomerName(),
            array(),
            $ticket,
            array_merge($this->getTicketEmailVariables($ticket),
                array(
                    'is_department_changed' => $ticket->getIsDepartmentChanged(),
                    'is_agent_changed'      => $ticket->getIsAgentChanged(),
                    'is_status_changed'     => $ticket->getIsStatusChanged(),
                    'external_customer_url' => $this->_getExternalCustomerUrl($ticket)
                )
            )
        );
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return mixed
     */
    protected function _getExternalCustomerUrl($ticket)
    {
        return Mage::getModel('core/url')->getUrl('aw_hdu3/customer/viewTicket',
            array(
                'id'      => $ticket->getId(),
                '_secure' => Mage::app()->getStore($ticket->getStoreId())->isCurrentlySecure()
            )
        );
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function sendToAdminNotificationEscalate($history)
    {
        $ticket = $history->getTicket();
        $storeId = $ticket->getStoreId();
        $escalateMessage = $history->getEventData();
        return $this->_send(Mage::helper('aw_hdu3/config')->getTicketEscalationEmailTemplateToSupervisor($storeId),
            Mage::helper('aw_hdu3/config')->getTicketEscalationSupervisorEmailList($storeId),
            '',
            array(),
            $ticket,
            array_merge($this->getTicketEmailVariables($ticket),
                array(
                    'message'              => isset($escalateMessage['content']) ? $escalateMessage['content'] : '',
                    'external_admin_url'   => $this->_getExternalAdminUrl($ticket)
                )
            )
        );
    }

    /**
     * @param string $template
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    protected function _sendNotificationToAdmin($template, $history)
    {
        $ticket = $history->getTicket();
        $message = $history->getEventData();
        return $this->_send($template,
            $ticket->getDepartmentAgent()->getEmail(),
            $ticket->getDepartmentAgent()->getName(),
            $history->getAttachmentCollection()->getItems(),
            $ticket,
            array_merge($this->getTicketEmailVariables($ticket),
                array(
                    'message'            => isset($message['content']) ? $message['content'] : '',
                    'external_admin_url' => $this->_getExternalAdminUrl($ticket)
                )
            )
        );
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return mixed
     */
    protected function _getExternalAdminUrl($ticket)
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_ticket/edit', array('id' => $ticket->getId()));
    }

    /**
     * @param string $template
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    protected function _sendNotificationToCustomer($template, $history)
    {
        $ticket = $history->getTicket();
        $message = $history->getEventData();

        $storeId = $ticket->getStoreId();
        $storeLocaleCode = Mage::getStoreConfig('general/locale/code', $storeId);
        Mage::app()->getTranslator()->setLocale($storeLocaleCode);
        Mage::app()->getTranslator()->init('frontend', true);
        return $this->_send($template,
            $ticket->getCustomerEmail(),
            $ticket->getCustomerName(),
            $history->getAttachmentCollection()->getItems(),
            $ticket,
            array_merge($this->getTicketEmailVariables($ticket),
                array(
                    'message' => isset($message['content']) ? $message['content'] : '',
                    'external_link_html' => $this->_getCustomerExternalLinkHtml($ticket),
                    'allow_rate' => (bool)Mage::helper('aw_hdu3/config')->isAllowRate(),
                    'external_link_rate_1' => $this->_getRateExternalLinkHtml($ticket, 1),
                    'external_link_rate_2' => $this->_getRateExternalLinkHtml($ticket, 2),
                    'external_link_rate_3' => $this->_getRateExternalLinkHtml($ticket, 3),
                    'external_link_rate_4' => $this->_getRateExternalLinkHtml($ticket, 4),
                    'external_link_rate_5' => $this->_getRateExternalLinkHtml($ticket, 5),
                    'is_status_changed' => (bool)$ticket->getIsStatusChanged()
                )
            )
        );
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    protected function _getCustomerExternalLinkHtml($ticket)
    {
        if (Mage::helper('aw_hdu3/config')->isAllowExternalViewForTickets($ticket->getStoreId())) {
            return Mage::helper('aw_hdu3')->__(
                'You can view the ticket and reply from web interface from %s here %s.',
                '<a href="' . Mage::helper('aw_hdu3/ticket')->getExternalViewUrl($ticket) . '">',
                '</a>'
            );
        }
        return '';
    }
    protected function _getRateExternalLinkHtml($ticket, $rate)
    {
        return Mage::helper('aw_hdu3/ticket')->getExternalRateUrl($ticket,$rate);
    }
    /**
     * @param Mage_Core_Model_Email_Template $mailTemplate
     * @param mixed $store
     *
     * @return $this
     */
    protected function _setCarbonCopy($mailTemplate, $store)
    {
        $copyTo = Mage::helper('aw_hdu3/config')->getCarbonCopyRecipientEmail($store);
        if (!empty($copyTo)) {
            $mailTemplate->addBcc($copyTo);
        }
        return $this;
    }

    /**
     * @param Mage_Core_Model_Email_Template $mailTemplate
     * @param array(0=>AW_Helpdesk3_Model_Ticket_History_Attachment) $attachment
     *
     * @return $this
     */
    protected function _processAttachments($mailTemplate, $attachment)
    {
        foreach ($attachment as $attach) {
            $at = $mailTemplate->getMail()->createAttachment(file_get_contents($attach->getFilePath()));
            $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding = Zend_Mime::ENCODING_BASE64;
            $at->filename = $attach->getFileRealName();
        }
        return $this;
    }

    /**
     * @param Mage_Core_Model_Email_Template $mailTemplate
     * @param string $replyTo
     *
     * @return $this
     */
    protected function _setReplyTo($mailTemplate, $replyTo)
    {
        try {
            $mailTemplate->setReplyTo($replyTo);
        } catch (Exception $e) {
            $mailTemplate->getMail()->setReplyTo($replyTo);
        }
        return $this;
    }
}