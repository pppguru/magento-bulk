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


class AW_Helpdesk3_Model_Ticket_Cron
{
    const MAIL_LIMIT = 50;
    const LOCK_CACHE_CREATE_TICKET_ID = 'AW_Helpdesk3_Model_Ticket_Cron::createTicketFromMail';
    const LOCK_CACHE_CLOSE_TICKET_ID  = 'AW_Helpdesk3_Model_Ticket_Cron::closeExpireTickets';
    const LOCK_CACHE_LIFETIME = 1800;

    /**
     * Create tickets from mail
     *
     * @return $this
     */
    public function createTicketFromMail()
    {
        if ($this->_isLocked(self::LOCK_CACHE_CREATE_TICKET_ID) || !Mage::helper('aw_hdu3/config')->isEnabled()) {
            return $this;
        }

        $mailCollection = Mage::getModel('aw_hdu3/gateway_mail')->getCollection();
        //get only unprocessed mail
        $mailCollection->addPendingFilter();
        $mailCollection->setPageSize(self::MAIL_LIMIT);
        AW_Lib_Helper_Log::start(Mage::helper('aw_hdu3')->__('Create tickets from saved email messages.'));
        foreach ($mailCollection as $mail) {
            //check mail on reject pattern
            if ($mail->isCanConvertToTicket()) {
                $gateway = Mage::getModel('aw_hdu3/gateway')->load($mail->getGatewayId());
                $department = Mage::getModel('aw_hdu3/department')->load($gateway->getDepartmentId());
                if ($gateway->getId() && $department->getId()) {
                    //get exist or new ticket by mail subject
                    $ticket = $this->_getTicketByMailSubject($mail->getSubject());
                    if ($ticket->getId()) {
                        //ticket exist
                        if ($ticket->getStatus() != AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE) {
                            $ticket->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE);
                            AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Ticket UID[%s] changed status to "Open"', $ticket->getUid()));
                        }
                        $ticket->setIsReply(true);
                    } else {
                        //ticket new
                        //check option: Create new tickets from incoming emails
                        $isAllowIncomingFromGateway = Mage::helper('aw_hdu3/config')
                            ->isAllowCreateNewTicketsFromIncomingEmails(array_shift($department->getStoreIds()))
                        ;
                        if (!$isAllowIncomingFromGateway) {
                            $mail->setStatus(AW_Helpdesk3_Model_Gateway_Mail::STATUS_PROCESSED)->save();
                            AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Message UID[%s] skipped. Create new tickets from incoming emails denied for this gateway', $mail->getUid()));
                            continue;
                        }
                        $email = $this->_parseEmail($mail->getFrom());
                        $customer = Mage::getModel('customer/customer')
                            ->setStore(Mage::app()->getStore(array_shift($department->getStoreIds())))
                            ->loadByEmail($email);
                        $name = $this->_parseCustomerName($mail->getFrom());
                        if ($customer && $customer->getId()) {
                            $name = $customer->getName();
                        }

                        $ticket
                            ->setDepartmentAgentId($department->getPrimaryAgentId())
                            ->setDepartmentId($department->getId())
                            ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE)
                            ->setPriority(AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE)
                            ->setCustomerName($name)
                            ->setCustomerEmail($email)
                            ->setSubject($mail->getSubject())
                            ->setStoreId(array_shift($department->getStoreIds()))
                        ;
                    }

                    try {
                        //save ticket
                        $ticket->save();
                        if ($ticket->isObjectNew()) {
                            AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('New ticket UID[%s]', $ticket->getUid()));
                        }
                    } catch (Exception $e) {
                        AW_Lib_Helper_Log::log($e->getMessage(), AW_Lib_Helper_Log::SEVERITY_ERROR);
                    }

                    try {
                        //add ticket history - Message
                        $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                            array(
                                'content'     => $mail->getBody(),
                                'attachments' => $mail->getAttachmentCollection()->getItems()
                            )
                        );
                    } catch (Exception $e) {
                        AW_Lib_Helper_Log::log($e->getMessage(), AW_Lib_Helper_Log::SEVERITY_ERROR);
                    }
                }
                //set status processed
                $mail->setStatus(AW_Helpdesk3_Model_Gateway_Mail::STATUS_PROCESSED)->save();
            } else {
                AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Message UID[%s] rejected.', $mail->getUid()), AW_Lib_Helper_Log::SEVERITY_WARNING);
            }
        }
        AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Complete.'));

        //remove lock
        Mage::app()->removeCache(self::LOCK_CACHE_CREATE_TICKET_ID);
        return $this;
    }

    /**
     * Mark tickets as expire
     *
     * @return $this
     */
    public function closeExpireTickets()
    {
        if ($this->_isLocked(self::LOCK_CACHE_CLOSE_TICKET_ID) || !Mage::helper('aw_hdu3/config')->isEnabled()) {
            return $this;
        }

        $ticketCollection = Mage::getModel('aw_hdu3/ticket')->getCollection();
        $ticketCollection->addFilterByStatus(AW_Helpdesk3_Model_Source_Ticket_Status::WAITING_VALUE);
        $currentDate = new Zend_Date();
        $currentDateString = $currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Mark tickets as Expired.'));
        foreach ($ticketCollection as $ticket) {
            $expireValue = Mage::helper('aw_hdu3/config')->getTicketAutoExpiration($ticket->getStoreId());
            if (null === $expireValue) {
                continue;
            }
            $finalDay = new Zend_Date($ticket->getUpdatedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT);
            $finalDayString = $finalDay
                ->addDay($expireValue)
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
            ;
            if ($finalDayString <= $currentDateString) {
                $ticket
                    ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE)
                    ->save()
                ;
                AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Ticket UID[%s] marked as expired.', $ticket->getUid()));
            }
        }
        AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Complete.'));

        //remove lock
        Mage::app()->removeCache(self::LOCK_CACHE_CLOSE_TICKET_ID);
        return $this;
    }

    /**
     * @param Parse customer email from mail subject
     *
     * @return bool|string
     */
    protected function _parseEmail($str)
    {
        if (preg_match("/([a-z0-9.\-_]+@[a-z0-9.\-_]+)/i", $str, $matches)) {
            return strtolower(@$matches[1]);
        }
        return false;
    }

    /**
     * Parse customer name from mail subject
     * @param string $str
     *
     * @return string
     */
    protected function _parseCustomerName($str)
    {
        $email = $this->_parseEmail($str);
        return str_replace('<' . $email . '>', '', $str);
    }

    /**
     * Get Ticket by mail subject (reply)
     * @param string $subject
     *
     * @return AW_Helpdesk3_Model_Ticket
     */
    protected function _getTicketByMailSubject($subject)
    {
        return Mage::getModel('aw_hdu3/ticket')->loadByUid(Mage::helper('aw_hdu3/ticket')->parseTicketUid($subject));
    }

    /**
     * @param $cacheId
     *
     * @return bool
     */
    protected function _isLocked($cacheId)
    {
        if (Mage::app()->loadCache($cacheId)) {
            return true;
        }
        Mage::app()->saveCache(time(), $cacheId, array(), self::LOCK_CACHE_LIFETIME);
        return false;
    }
}
