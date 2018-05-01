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


class AW_Helpdesk3_Model_Shell_Migration
{
    protected $_migrationData = null; //data from table aw_hdu3_migration_temp_data
    protected $_readAdapter   = null; //Varien_Db_Adapter_Pdo_Mysql type read
    protected $_writeAdapter  = null; //Varien_Db_Adapter_Pdo_Mysql type write

    const STATE_PENDING    = 1; //ready for schedule
    const STATE_PROCESSING = 2; //already started

    const REPLACE_MAILBOX_LIMIT = 100; //limit for entries from table aw_hdu_mailbox for each schedule
    const REPLACE_TICKET_LIMIT  = 100; //limit for entries from table aw_hdu_ticket for each schedule
    const REPLACE_MESSAGE_LIMIT = 100; //limit for entries from table aw_hdu_message for each schedule

    protected $_isCompleted = true; //migration complete flag


    public function __construct()
    {
        //create aw_hdu3_migration_temp_data table if not exists and set $this->_migrationData
        $this->_initMigrationData();
    }

    /**
     *
     * start point for schedule
     * For restart migration do:
     * DELETE FROM `aw_hdu3_ticket_message`;
     * DELETE FROM `aw_hdu3_ticket_history_additional`;
     * DELETE FROM `aw_hdu3_ticket_history_attachment`;
     * DELETE FROM `aw_hdu3_ticket_history`;
     * DELETE FROM `aw_hdu3_ticket`;
     * DELETE FROM `aw_hdu3_template`;
     * DELETE FROM `aw_hdu3_gateway`;
     * DELETE FROM `aw_hdu3_gateway_mail`;
     * DELETE FROM `aw_hdu3_department`;
     * DROP TABLE IF EXISTS `aw_hdu3_migration_temp_data`;
     * DELETE FROM `aw_hdu3_ticket_status` WHERE is_system = 0;
     *
     * @return $this
     */
    public function process()
    {
        //if already started do nothing
        if ($this->_migrationData['state'] == self::STATE_PROCESSING) {
            echo Mage::helper('aw_hdu3')->__('Already started in other process');
            return $this;
        }

        try {
            //set flag already started and start all jobs
            $this
                ->_setMigrationData('state', self::STATE_PROCESSING)
                ->_replaceDepartments()
                ->_replaceTicketStatuses()
                ->_replaceRejectPattern()
                ->_replaceTemplate()
                ->_replaceGateways()
                ->_replaceMailbox()
                ->_replaceTicket()
                ->_replaceMessages()
                ->_setMigrationData('state', self::STATE_PENDING)
            ;
        } catch (Exception $e) {
            //set flag ready for schedule
            $this->_setMigrationData('state', self::STATE_PENDING);
            $this->_log($e->getMessage());
        }

        //show message if complete
        if ($this->_isCompleted) {
            echo Mage::helper('aw_hdu3')->__('Already completed!');
        }
        return $this;
    }

    /**
     * Sync table aw_hdu_gateway with aw_hdu3_gateway.
     * @return $this
     */
    protected function _replaceGateways()
    {
        //get all old gatewayIds
        $needToReplaceGatewayIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM ' . $this->_getTableName('aw_hdu_gateway'));
        $_gateways = array();
        if (array_key_exists('gateways', $this->_migrationData)) {
            //check already migrated gateways
            $oldGatewayIds = $needToReplaceGatewayIds;
            $_gateways = $this->_migrationData['gateways'];
            foreach ($oldGatewayIds as $key => $oldGatewayId) {
                if (array_key_exists($oldGatewayId, $_gateways)) {
                    unset($needToReplaceGatewayIds[$key]);
                }
            }
        }
        if (count($needToReplaceGatewayIds) > 0) {
            //not all gateways migrated
            $this->_isCompleted = false;
            foreach ($needToReplaceGatewayIds as $oldGatewayId) {
                //get old department id for current gateway
                $oldDepartmentId = $this->_getReadAdapter()->fetchOne('SELECT id FROM '
                    . $this->_getTableName('aw_hdu_department')
                    . ' WHERE FIND_IN_SET("' . $oldGatewayId
                    . '", gateways) OR FIND_IN_SET("0", gateways) LIMIT 1'
                );

                //process if $oldDepartmentId exist and already migrated
                if (!$oldDepartmentId || !array_key_exists($oldDepartmentId, $this->_migrationData['departments'])) {
                    //message impossible replace gateway id $oldGatewayId - no department
                    $this->_log(Mage::helper('aw_hdu3')->__('Impossible replace gateway id %d - no department', $oldGatewayId));
                    continue;
                }

                //already migrated new department id
                $newDepartmentId = $this->_migrationData['departments'][$oldDepartmentId];

                //get old gateway password for encrypt
                $oldGatewayPassword = $this->_getReadAdapter()->fetchOne('SELECT password FROM '
                    . $this->_getTableName('aw_hdu_gateway')
                    . ' WHERE id = ' . $oldGatewayId
                );

                //encrypt password
                $oldGatewayPassword = Mage::helper('core')->encrypt($oldGatewayPassword);

                //load new department for get store_ids
                $_newDepartmentModel = Mage::getModel('aw_hdu3/department')->load($newDepartmentId);

                //is allow attachment for current gateway
                $isAllowAttachment = (int)Mage::helper('helpdeskultimate/config')->isAllowedManageFiles(
                    (int)$_newDepartmentModel->getStoreIds()
                );

                //aw_hdu3_gateway
                $this->_getWriteAdapter()->exec('INSERT INTO '
                    . $this->_getTableName('aw_hdu3_gateway')
                    . ' (department_id, title, is_active, protocol, email, host, login, password, port'
                    . ', secure_type, delete_emails, is_allow_attachment)'
                    . ' SELECT ' . $newDepartmentId . ', title, is_active, IF(protocol = "pop3", '
                    . AW_Helpdesk3_Model_Source_Gateway_Protocol::POP3_VALUE . ', '
                    . AW_Helpdesk3_Model_Source_Gateway_Protocol::IMAP_VALUE . '), email, host, login, '
                    . $this->_getWriteAdapter()->quote($oldGatewayPassword) . ', port, IF(secure = "ssl", '
                    . AW_Helpdesk3_Model_Source_Gateway_Secure::TYPE_SSL_VALUE . ', IF(secure = "tls", '
                    . AW_Helpdesk3_Model_Source_Gateway_Secure::TYPE_TLS_VALUE . ', '
                    . AW_Helpdesk3_Model_Source_Gateway_Secure::TYPE_NONE_VALUE . ')), delete_message, '
                    . $isAllowAttachment . ' FROM '
                    . $this->_getTableName('aw_hdu_gateway') . ' WHERE id = ' . $oldGatewayId
                );
                $_newGatewayId = $this->_getWriteAdapter()->lastInsertId();

                //save $oldGatewayId => $_newGatewayId
                $_gateways[$oldGatewayId] = $_newGatewayId;
            }
            $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  gateway(s) has been replaced', count($_gateways), count($_gateways)));
            $this->_setMigrationData('gateways', $_gateways);
            $this->_isCompleted = true;
        }
        return $this;
    }

    /**
     * Sync table aw_hdu_message with aw_hdu3_message.
     * @return $this
     */
    protected function _replaceMessages()
    {
        //last step
        //get last message id
        $lastMessageId = $this->_getReadAdapter()->fetchOne('SELECT MAX(id) FROM ' . $this->_getTableName('aw_hdu_message'));

        //get messages from
        $startFromId = 0;
        if (array_key_exists('last_message_id', $this->_migrationData)) {
            $startFromId = $this->_migrationData['last_message_id'];
        }
        if ($this->_isCompleted) {
            //process if saved id != last id from table aw_hdu_message
            if ($lastMessageId != $startFromId) {
                $this->_isCompleted = false;

                $needToReplaceMessageIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM '
                    . $this->_getTableName('aw_hdu_message') . ' WHERE id > ' . $startFromId
                    . ' LIMIT ' . self::REPLACE_MESSAGE_LIMIT
                );
                foreach ($needToReplaceMessageIds as $oldMessageId) {
                    //remember migrated old ticket id
                    $startFromId = $oldMessageId;

                    //get old ticket model
                    $oldMessageModel = Mage::getModel('helpdeskultimate/message')->load($oldMessageId);
                    try {
                        $oldTicketModel = $oldMessageModel->getTicket();
                    } catch (Mage_Core_Exception $e) {
                        $this->_log(Mage::helper('aw_hdu3')->__('Error: message id %d skipped - %s', $oldMessageModel->getData('id'), $e->getMessage()));
                        continue;
                    }

                    //get new ticket model
                    $newTicketModel = Mage::getModel('aw_hdu3/ticket')->loadByUid($oldTicketModel->getData('uid'));
                    if (null === $newTicketModel->getId()) {
                        $this->_log(Mage::helper('aw_hdu3')->__('Error: message id %d skipped - no exist ticket', $oldMessageModel->getData('id')));
                        continue;
                    }

                    //get initiator agent id
                    $initiatorDepartmentAgentId = null;
                    if ($oldMessageModel->getDepartmentId()) {
                        //if new department doesn't exist by old department id -> skip message
                        if (!array_key_exists($oldMessageModel->getData('department_id'), $this->_migrationData['departments'])) {
                            $this->_log(Mage::helper('aw_hdu3')->__('Error: message id %d skipped - no exist department', $oldMessageModel->getData('id')));
                            continue;
                        }
                        //get new department info
                        $_newDepartmentId = $this->_migrationData['departments'][$oldMessageModel->getData('department_id')];
                        $newDepartmentModel = Mage::getModel('aw_hdu3/department')->load($_newDepartmentId);
                        $initiatorDepartmentAgentId = $newDepartmentModel->getPrimaryAgentId();
                    }

                    $eventData = array(
                        'content'     => $oldMessageModel->getData('content'),
                        'attachments' => $oldMessageModel->getFilename()
                    );

                    try {
                        $this->_addTicketHistory($oldMessageModel, $newTicketModel, $initiatorDepartmentAgentId, $eventData);
                    } catch (Exception $e){
                        $this->_log(Mage::helper('aw_hdu3')->__('Warning: can\'t save attachment for ticket UID: %s - Reason: %s', $oldTicketModel->getData('uid'), $e->getMessage()));
                    }

                    //if saved old ticket id = last old ticket id -> complete
                    if ($startFromId == $lastMessageId) {
                        $this->_isCompleted = true;
                        break;
                    }
                }
            }
        }
        $this->_setMigrationData('last_message_id', $startFromId);

        //get count messages
        $messagesCount = $this->_getReadAdapter()->fetchOne('SELECT COUNT(id) FROM ' . $this->_getTableName('aw_hdu_message'));

        //get processed old tickets
        $migratedMessagesCount = $this->_getReadAdapter()->fetchOne('SELECT COUNT(id) FROM '
            . $this->_getTableName('aw_hdu_message') . ' WHERE id <= ' . $this->_migrationData['last_message_id']
        );
        $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  message(s) has been replaced', $migratedMessagesCount, $messagesCount));
        return $this;
    }

    /**
     * Sync table aw_hdu_ticket with aw_hdu3_ticket.
     * @return $this
     */
    protected function _replaceTicket()
    {
        //get last ticket id
        $lastTicketId = $this->_getReadAdapter()->fetchOne('SELECT MAX(id) FROM ' . $this->_getTableName('aw_hdu_ticket'));

        //get tickets from
        $startFromId = 0;
        if (array_key_exists('last_ticket_id', $this->_migrationData)) {
            $startFromId = $this->_migrationData['last_ticket_id'];
        }

        //process if saved id != last id from table aw_hdu_ticket
        if ($lastTicketId != $startFromId) {
            $this->_isCompleted = false;

            $needToReplaceTicketIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM '
                . $this->_getTableName('aw_hdu_ticket') . ' WHERE id > ' . $startFromId
                . ' LIMIT ' . self::REPLACE_TICKET_LIMIT
            );
            foreach ($needToReplaceTicketIds as $oldTicketId) {
                //remember migrated old ticket id
                $startFromId = $oldTicketId;
                $oldTicketModel = Mage::getModel('helpdeskultimate/ticket')->load($oldTicketId);

                //if new department doesn't exist by old department id -> skip ticket
                if (!array_key_exists($oldTicketModel->getData('department_id'), $this->_migrationData['departments'])) {
                    $this->_log(Mage::helper('aw_hdu3')->__('Error: ticket uid %s skipped - no exist department', $oldTicketModel->getData('uid')));
                    continue;
                }



                //get new department info
                $_newDepartmentId = $this->_migrationData['departments'][$oldTicketModel->getData('department_id')];
                $newDepartmentModel = Mage::getModel('aw_hdu3/department')->load($_newDepartmentId);

                //prepare lock info
                $_isLocked = ($oldTicketModel->getData('locked_by') == 0) ? 0 : 1;
                $_lockedBy = 'null';
                $_lockedAt = 'null';
                if ($_isLocked) {
                    $_departmentAgent = Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId(
                        $oldTicketModel->getData('locked_by')
                    );
                    $_lockedBy = $_departmentAgent->getId();
                    $_lockedAt = $oldTicketModel->getData('locked_at');
                }

                //create aw_hdu3_ticket
                //ticket.updated_at will be set in $this->_replaceMessages() method
                //set status as New for HDU statistics
                $data=array(
                        'department_agent_id'=>$newDepartmentModel->getPrimaryAgentId(),
                        'department_id'=>$_newDepartmentId,
                        'uid'=>$oldTicketModel->getData('uid'),
                        'status'=> AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE,
                        'priority'=>$this->_getPriority($oldTicketModel->getData('priority')),
                        'customer_name'=>$oldTicketModel->getData('customer_name'),
                        'customer_email'=>$oldTicketModel->getData('customer_email'),
                        'subject'=>$oldTicketModel->getData('title'),
                        'order_increment_id'=>$oldTicketModel->getData('order_incremental_id'),
                        'is_locked'=>$_isLocked,
                        'locked_by_department_agent_id'=>$_lockedBy,
                        'locket_at'=>$_lockedAt,
                        'store_id'=>$oldTicketModel->getData('store_id'),
                        'created_at'=>$oldTicketModel->getData('created_time'),
                        'updated_at'=>$oldTicketModel->getData('created_time'));

                $this->_getWriteAdapter()->insert($this->_getTableName('aw_hdu3_ticket'),$data);

                //new ticket info
                $newTicketId = $this->_getWriteAdapter()->lastInsertId();
                $newTicketModel = Mage::getModel('aw_hdu3/ticket')->load($newTicketId);

                //ticket first message and attachments
                $eventData = array(
                    'content'     => $oldTicketModel->getData('content'),
                    'attachments' => $oldTicketModel->getFilename()
                );

                //initiator agent id
                $initiatorDepartmentAgentId = ($oldTicketModel->getData('created_by') == AW_Helpdeskultimate_Model_Ticket::CREATED_BY_ADMIN ? $newDepartmentModel->getPrimaryAgentId() : null);

                //add first message into new ticket history
                try {
                    $this->_addTicketHistory($oldTicketModel, $newTicketModel, $initiatorDepartmentAgentId, $eventData);
                } catch (Exception $e){
                    $this->_log(Mage::helper('aw_hdu3')->__('Warning: can\'t save attachment for ticket UID:%s - Reason: %s', $oldTicketModel->getData('uid'), $e->getMessage()));
                }

                //update ticket status
                $_newTicketStatusId = $this->_getStatus($oldTicketModel->getData('status'));
                if (null === $_newTicketStatusId) {
                    //default ticket status if old status (custom) not exist
                    $_newTicketStatusId = AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE;
                    $this->_log(Mage::helper('aw_hdu3')->__('Warning: ticket status #%s doesn\'t exist for ticket UID:%s marked as OPEN', $oldTicketModel->getData('status'), $oldTicketModel->getData('uid')));
                }

                $this->_getWriteAdapter()->exec('UPDATE ' . $this->_getTableName('aw_hdu3_ticket')
                    . ' SET status = ' . $_newTicketStatusId . ' WHERE id = ' . $newTicketModel->getId()
                );

                //for Avg. Closing Ticket Time (hrs) statistics need ticket last and initiator
                $ticketLastReplyId = $this->_getReadAdapter()->fetchOne('SELECT id, created_time FROM '
                    . $this->_getTableName('aw_hdu_message') . ' WHERE ticket_id = ' . $oldTicketModel->getId() . ' GROUP BY created_time HAVING MAX(created_time)'
                );
                $oldMessageModel = Mage::getModel('helpdeskultimate/message')->load($ticketLastReplyId);

                if (null !== $oldMessageModel->getId()) {
                    //get initiator agent id
                    $initiatorDepartmentAgentId = null;
                    if ($oldMessageModel->getDepartmentId()
                        && array_key_exists($oldMessageModel->getData('department_id'), $this->_migrationData['departments'])
                    ) {
                        //get new department info
                        $_newDepartmentId = $this->_migrationData['departments'][$oldMessageModel->getData('department_id')];
                        $newDepartmentModel = Mage::getModel('aw_hdu3/department')->load($_newDepartmentId);
                        $initiatorDepartmentAgentId = $newDepartmentModel->getPrimaryAgentId();
                    }

                    $eventData = array(
                        'from' => $newTicketModel->getStatus(),
                        'to'   => $_newTicketStatusId,
                    );
                    $ticketHistoryModel = Mage::getModel('aw_hdu3/ticket_history');
                    $ticketHistoryModel
                        ->setTicket($newTicketModel)
                        ->setEventType(AW_Helpdesk3_Model_Ticket_History_Event_Status::TYPE)
                        ->setTicketId($newTicketModel->getId())
                        ->setEventData($eventData)
                        ->setCreatedAt($oldMessageModel->getData('created_time'))
                    ;
                    if (null !== $initiatorDepartmentAgentId) {
                        $ticketHistoryModel->setInitiatorDepartmentAgentId($initiatorDepartmentAgentId);
                    }
                    $ticketHistoryModel->save();
                }

                //if saved old ticket id = last old ticket id -> complete
                if ($startFromId == $lastTicketId) {
                    $this->_isCompleted = true;
                    break;
                }
            }
            $this->_setMigrationData('last_ticket_id', $startFromId);

            //get count tickets
            $ticketsCount = $this->_getReadAdapter()->fetchOne('SELECT COUNT(id) FROM ' . $this->_getTableName('aw_hdu_ticket'));

            //get processed old tickets
            $migratedTicketsCount = $this->_getReadAdapter()->fetchOne('SELECT COUNT(id) FROM '
                . $this->_getTableName('aw_hdu_ticket') . ' WHERE id <= ' . $this->_migrationData['last_ticket_id']
            );
            $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  ticket(s) has been replaced', $migratedTicketsCount, $ticketsCount));
        }
        return $this;
    }

    /**
     * Add history for new ticket with event type Message
     *
     * @param AW_Helpdeskultimate_Model_Ticket | AW_Helpdeskultimate_Model_Message $messageSource
     * @param AW_Helpdeskultimate_Model_Ticket $newTicketModel
     * @param int $initiatorDepartmentAgentId
     * @param array (content => string, attachments => array) $data
     *
     * @return $this
     */
    protected function _addTicketHistory($messageSource, $newTicketModel, $initiatorDepartmentAgentId = null, $data)
    {
        //history event data should be without attachments
        $eventData = $data;
        if (array_key_exists('attachments', $eventData)) {
            unset($eventData['attachments']);
        }

        //create new history
        $ticketHistoryModel = Mage::getModel('aw_hdu3/ticket_history');
        $ticketHistoryModel
            ->setTicket($newTicketModel)
            ->setEventType(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE)
            ->setTicketId($newTicketModel->getId())
            ->setEventData($eventData)
            ->setCreatedAt($messageSource->getData('created_time'))
        ;
        if (null !== $initiatorDepartmentAgentId) {
            $ticketHistoryModel->setInitiatorDepartmentAgentId($initiatorDepartmentAgentId);
        }
        $ticketHistoryModel->save();

        //if exist content -> create entry in aw_hdu3_ticket_message
        if (array_key_exists('content', $data)) {
            $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_ticket_message')
                . ' VALUES (null, '
                . $newTicketModel->getId() . ', ' . $ticketHistoryModel->getId() . ', '
                . $this->_getWriteAdapter()->quote($data['content']). ')'
            );
        }

        //if exist attachments ->create AW_Helpdesk3_Model_Ticket_History_Attachment
        if (array_key_exists('attachments', $data) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $_fileName) {
                //get file content
                $fileContent = file_get_contents(
                    $messageSource->getFolderName() . Mage::helper('helpdeskultimate')->getEncodedFileName($_fileName)
                );

                //get new AW_Helpdesk3_Model_Ticket_History_Attachment
                $historyAttach = Mage::getModel('aw_hdu3/ticket_history_attachment');

                //set file
                $historyAttach->setFile($_fileName, $fileContent);

                //save AW_Helpdesk3_Model_Ticket_History_Attachment
                $ticketHistoryModel->addAttachment($historyAttach);
            }
        }
        return $this;
    }

    /**
     * Sync table aw_hdu_mailbox with aw_hdu3_gateway_mail.
     * @return $this
     */
    protected function _replaceMailbox()
    {
        //get last mail id
        $lastMailId = $this->_getReadAdapter()->fetchOne('SELECT MAX(id) FROM ' . $this->_getTableName('aw_hdu_mailbox'));

        //get mails from
        $startFromId = 0;
        if (array_key_exists('last_mail_id', $this->_migrationData)) {
            $startFromId = $this->_migrationData['last_mail_id'];
        }

        //process if saved id != last id from table aw_hdu_mailbox
        if ($lastMailId != $startFromId) {
            $this->_isCompleted = false;

            $needToReplaceMailIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM '
                . $this->_getTableName('aw_hdu_mailbox') . ' WHERE id > ' . $startFromId
                . ' LIMIT ' . self::REPLACE_MAILBOX_LIMIT
            );
            foreach ($needToReplaceMailIds as $oldMailId) {
                //load old mail model
                $oldMailModel = Mage::getModel('helpdeskultimate/popmessage')->load($oldMailId);

                //if new gateway doesn't exist by old gateway id -> skip mail
                if (!array_key_exists($oldMailModel->getData('gateway_id'), $this->_migrationData['gateways'])) {
                    $this->_log(Mage::helper('aw_hdu3')->__('Error: mail uid %s skipped - no exist gateway', $oldMailModel->getUid()));
                    continue;
                }

                //load new gateway model for generate mail uid
                $newGateway = Mage::getModel('aw_hdu3/gateway')->load($this->_migrationData['gateways'][$oldMailModel->getData('gateway_id')]);
                $_rejectPatternId = 'null';

                //if new reject pattern doesn't exist by old reject id -> set null
                if (0 != $oldMailModel->getData('rej_pid')) {
                    if (array_key_exists($oldMailModel->getData('rej_pid'), $this->_migrationData['patterns'])) {
                        $_rejectPatternId = $oldMailModel->getData('rej_pid');
                    } else {
                        $this->_log(Mage::helper('aw_hdu3')->__('Warning:Mail uid:%s Reject pattern with id #%d doesn\'t exist',
                                $oldMailModel->getData('uid'), $oldMailModel->getData('rej_pid')
                            )
                        );
                    }
                }

                //create aw_hdu3_gateway_mail
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_gateway_mail')
                    . ' VALUES (null, '
                    . $this->_getWriteAdapter()->quote($newGateway->getMailUidByMessageUid($oldMailModel->getData('uid')))
                    . ', ' . $newGateway->getId() . ', '
                    . $this->_getWriteAdapter()->quote($oldMailModel->getData('from')) . ', '
                    . $this->_getWriteAdapter()->quote($oldMailModel->getData('to')) . ', '
                    . $oldMailModel->getData('status')
                    . ',' . $this->_getWriteAdapter()->quote($oldMailModel->getData('subject')) . ','
                    . $this->_getWriteAdapter()->quote($oldMailModel->getData('body')) . ','
                    . $this->_getWriteAdapter()->quote($oldMailModel->getData('headers')) . ','
                    . $this->_getWriteAdapter()->quote($oldMailModel->getData('content_type')) . ', '
                    . $_rejectPatternId . ',"' . $oldMailModel->getData('date') . '")'
                );

                $_newMailId = $this->_getWriteAdapter()->lastInsertId();

                // Save attachment if exists
                if ($oldMailModel->getAttachmentName()) {
                    $attachments = Mage::getModel('helpdeskultimate/attachment')->loadByUid($oldMailModel->getUid());
                    if ($attachments->getData('attachments')) {
                        $newMailModel = Mage::getModel('aw_hdu3/gateway_mail')->load($_newMailId);
                        foreach ($attachments->getData('attachments') as $attach) {
                            $newMailModel->addAttachmentFromArray(
                                array(
                                    'filename' => $attach['filename'],
                                    'content'  => @base64_decode($attach['content'])
                                )
                            );
                        }
                    }
                }

                //remember migrated old mail id
                $startFromId = $oldMailId;

                //if saved old mail id = last old mail id -> complete
                if ($startFromId == $lastMailId) {
                    $this->_isCompleted = true;
                    break;
                }
            }
            $this->_setMigrationData('last_mail_id', $startFromId);
        }
        if ( isset($this->_migrationData['last_mail_id'])) {
            //get processed old mails
            $migratedMailsCount = $this->_getReadAdapter()->fetchOne('SELECT COUNT(id) FROM '
                . $this->_getTableName('aw_hdu_mailbox') . ' WHERE id <= ' . $this->_migrationData['last_mail_id']
            );

            //get count mails
            $mailsCount = $this->_getReadAdapter()->fetchOne('SELECT COUNT(id) FROM ' . $this->_getTableName('aw_hdu_mailbox'));
            $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  mail(s) has been replaced', $migratedMailsCount, $mailsCount));
        }

        return $this;
    }

    /**
     * Return new priority id by old priority
     *
     * @param string $oldPriority
     *
     * @return int
     */
    protected function _getPriority($oldPriority)
    {
        //by default set TO DO
        $newPriority = AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE;
        switch ($oldPriority) {
            case 'urgent' :
                $newPriority = AW_Helpdesk3_Model_Source_Ticket_Priority::URGENT_VALUE;
                break;
            case 'asap' :
                $newPriority = AW_Helpdesk3_Model_Source_Ticket_Priority::ASAP_VALUE;
                break;
            case 'iftime' :
                $newPriority = AW_Helpdesk3_Model_Source_Ticket_Priority::IF_TIME_VALUE;
                break;
        }
        return $newPriority;
    }

    protected function _getStatus($oldStatus)
    {
        $newStatus = null;
        switch ($oldStatus) {
            case 1 :
                $newStatus = AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE;
                break;
            case 2 :
                $newStatus = AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE;
                break;
            case 3 :
                $newStatus = AW_Helpdesk3_Model_Source_Ticket_Status::WAITING_VALUE;
                break;
        }
        if (null === $newStatus) {
            if (array_key_exists($oldStatus, $this->_migrationData['statuses'])) {
                $newStatus = $this->_migrationData['statuses'][$oldStatus];
            }
        }
        return $newStatus;
    }

    /**
     * Sync table aw_hdu_templates with aw_hdu3_template.
     * @return $this
     */
    protected function _replaceTemplate()
    {
        //get all old templates ids
        $needToReplaceTemplateIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM ' . $this->_getTableName('aw_hdu_templates'));
        $_templates = array();
        if (array_key_exists('templates', $this->_migrationData)) {
            //check already migrated templates
            $oldTemplateIds = $needToReplaceTemplateIds;
            $_templates = $this->_migrationData['templates'];
            foreach ($oldTemplateIds as $key => $oldTemplateId) {
                if (array_key_exists($oldTemplateId, $_templates)) {
                    unset($needToReplaceTemplateIds[$key]);
                }
            }
        }
        if (count($needToReplaceTemplateIds) > 0) {
            //not all templates migrated
            $this->_isCompleted = false;
            foreach ($needToReplaceTemplateIds as $oldTemplateId) {
                //aw_hdu3_ticket_status
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_template')
                    . ' (title, is_active, store_ids, content)'
                    . ' SELECT name, enabled, 0, content FROM '
                    . $this->_getTableName('aw_hdu_templates') . ' WHERE id = ' . $oldTemplateId
                );
                $_newTemplateId = $this->_getWriteAdapter()->lastInsertId();

                //save oldTemplateId => newTemplateId
                $_templates[$oldTemplateId] = $_newTemplateId;
            }
            $this->_setMigrationData('templates', $_templates);
            $this->_isCompleted = true;
        }
        $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  template(s) has been replaced', count($_templates), count($_templates)));
        return $this;
    }

    /**
     * Sync table aw_hdu_rpattern with aw_hdu3_gateway_mail_reject_pattern.
     * @return $this
     */
    protected function _replaceRejectPattern()
    {
        //get all old pattern ids
        $needToReplacePatternIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM ' . $this->_getTableName('aw_hdu_rpattern'));
        $_patterns = array();
        if (array_key_exists('patterns', $this->_migrationData)) {
            //check already migrated patterns
            $oldPatternIds = $needToReplacePatternIds;
            $_patterns = $this->_migrationData['patterns'];
            foreach ($oldPatternIds as $key => $oldPatternId) {
                if (array_key_exists($oldPatternId, $_patterns)) {
                    unset($needToReplacePatternIds[$key]);
                }
            }
        }
        if (count($needToReplacePatternIds) > 0) {
            //not all patterns migrated
            $this->_isCompleted = false;
            foreach ($needToReplacePatternIds as $oldPatternId) {
                //aw_hdu3_gateway_mail_reject_pattern
                $this->_getWriteAdapter()->exec('INSERT INTO '
                    . $this->_getTableName('aw_hdu3_gateway_mail_reject_pattern')
                    . ' (id, title, is_active, types, pattern)'
                    . ' SELECT id, name, is_active, scope, pattern FROM '
                    . $this->_getTableName('aw_hdu_rpattern') . ' as old_table WHERE old_table.id = ' . $oldPatternId
                    . ' on duplicate key update title = old_table.name'
                    . ', is_active = old_table.is_active, types = old_table.scope, pattern = old_table.pattern'
                );
                $_newPatternId = $this->_getWriteAdapter()->lastInsertId();

                //save oldPatternId => newPatternId
                $_patterns[$oldPatternId] = $_newPatternId;
            }
            $this->_setMigrationData('patterns', $_patterns);
            $this->_isCompleted = true;
        }
        $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  pattern(s) has been replaced', count($_patterns), count($_patterns)));
        return $this;
    }

    /**
     * Sync table aw_hdu_status with aw_hdu3_ticket_status.
     * @return $this
     */
    protected function _replaceTicketStatuses()
    {
        //get all old ticket status ids
        $needToReplaceStatusIds = $this->_getReadAdapter()->fetchCol('SELECT status_id FROM ' . $this->_getTableName('aw_hdu_status'));
        $_statuses = array();
        if (array_key_exists('statuses', $this->_migrationData)) {
            //check already migrated statuses
            $oldStatusIds = $needToReplaceStatusIds;
            $_statuses = $this->_migrationData['statuses'];
            foreach ($oldStatusIds as $key => $oldStatusId) {
                if (array_key_exists($oldStatusId, $_statuses)) {
                    unset($needToReplaceStatusIds[$key]);
                }
            }
        }
        if (count($needToReplaceStatusIds) > 0) {
            //not all statuses migrated
            $this->_isCompleted = false;
            foreach ($needToReplaceStatusIds as $oldStatusId) {
                //aw_hdu3_ticket_status
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_ticket_status')
                    . ' VALUES (null, 1, "000000", "", 0)'
                );
                $_newStatusId = $this->_getWriteAdapter()->lastInsertId();

                //save oldStatusId => newStatusId
                $_statuses[$oldStatusId] = $_newStatusId;

                //aw_hdu3_ticket_status_label
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_ticket_status_label')
                    . ' (status_id, value, store_id)'
                    . ' SELECT ' . $_newStatusId . ',label,0 FROM '
                    . $this->_getTableName('aw_hdu_status') . ' WHERE status_id = ' . $oldStatusId
                );
            }
            $this->_setMigrationData('statuses', $_statuses);
            $this->_isCompleted = true;
        }
        $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  status(es) has been replaced', count($_statuses), count($_statuses)));
        return $this;
    }

    /**
     * Sync table aw_hdu_department with aw_hdu3_department.
     * @return $this
     */
    protected function _replaceDepartments()
    {
        //get all old department ids
        $needToReplaceDepartmentIds = $this->_getReadAdapter()->fetchCol('SELECT id FROM ' . $this->_getTableName('aw_hdu_department'));
        $_departments = array();
        if (array_key_exists('departments', $this->_migrationData)) {
            //check already migrated departments
            $oldDepartmentIds = $needToReplaceDepartmentIds;
            $_departments = $this->_migrationData['departments'];
            foreach ($oldDepartmentIds as $key => $oldDepartmentId) {
                if (array_key_exists($oldDepartmentId, $_departments)) {
                    unset($needToReplaceDepartmentIds[$key]);
                }
            }
        }

        if (count($needToReplaceDepartmentIds) > 0) {
            //not all departments migrated
            $this->_isCompleted = false;
            $_firstAgentId = $this->_getReadAdapter()->fetchOne(
                'SELECT id FROM ' . $this->_getTableName('aw_hdu3_department_agent')
            );
            foreach ($needToReplaceDepartmentIds as $oldDepartmentId) {
                //aw_hdu3_department
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_department')
                    . ' (primary_agent_id, title, store_ids, created_at, status, sort_order)'
                    . ' SELECT ' . $_firstAgentId . ',name,visible_on,NOW(),enabled,display_order FROM '
                    . $this->_getTableName('aw_hdu_department') . ' WHERE id = ' . $oldDepartmentId
                );
                $_newDepartmentId = $this->_getWriteAdapter()->lastInsertId();

                //save oldDepId => newDepId
                $_departments[$oldDepartmentId] = $_newDepartmentId;

                //aw_hdu3_department_notification
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_department_notification')
                    . ' VALUES (null, ' . $_newDepartmentId . ', "general"'
                    . ', "aw_hdu3_to_admin_new_ticket_email", "aw_hdu3_to_customer_new_ticket_email"'
                    . ', "aw_hdu3_to_customer_new_ticket_by_admin_email", "aw_hdu3_to_admin_new_reply_email"'
                    . ', "aw_hdu3_to_customer_new_reply_email", "aw_hdu3_to_primary_agent_reassign_email"'
                    . ', "aw_hdu3_to_customer_ticket_changed", "aw_hdu3_to_customer_ticket_changed")'
                );

                //aw_hdu3_department_permission
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_department_permission')
                    . ' (department_id, department_ids, admin_role_ids)'
                    . ' SELECT ' . $_newDepartmentId . ',"", GROUP_CONCAT(role_id) FROM '
                    . $this->_getTableName('aw_hdu_department_permissions') . ' WHERE value = ' . $oldDepartmentId
                );

                //aw_hdu3_department_agent_link
                $newDepartmentModel = Mage::getModel('aw_hdu3/department')->load($_newDepartmentId);
                $this->_getWriteAdapter()->exec('INSERT INTO ' . $this->_getTableName('aw_hdu3_department_agent_link')
                    . ' VALUES (null, ' . $_newDepartmentId . ', ' . $newDepartmentModel->getPrimaryAgentId() . ')'
                );
            }
            $this->_setMigrationData('departments', $_departments);
            $this->_isCompleted = true;
        }
        $this->_log(Mage::helper('aw_hdu3')->__('(%d) from (%d)  department(s) has been replaced', count($_departments), count($_departments)));
        return $this;
    }

    /**
     * Save data into aw_hdu3_migration_temp_data table and set $this->_migrationData
     *
     * @param string $key
     * @param string|int|array $value
     *
     * @return $this
     */
    protected function _setMigrationData($key, $value)
    {
        $this->_migrationData[$key] = $value;
        $this->_getWriteAdapter()->exec('UPDATE ' . $this->_getTableName('aw_hdu3_migration_temp_data')
            . ' SET data = ' . $this->_getWriteAdapter()->quote(serialize($this->_migrationData)) . ' WHERE id = 1'
        );
        return $this;
    }

    /**
     * Create aw_hdu3_migration_temp_data table if not exists and set $this->_migrationData
     *
     * @return $this
     */
    protected function _initMigrationData()
    {
        if (null === $this->_migrationData) {
            $this->_getWriteAdapter()->query('CREATE TABLE IF NOT EXISTS '
                . $this->_getTableName('aw_hdu3_migration_temp_data')
                . ' (id TINYINT(2) UNSIGNED NOT NULL, data TEXT, PRIMARY KEY (`id`)) ENGINE = InnoDB;'
                . ' INSERT IGNORE INTO ' . $this->_getTableName('aw_hdu3_migration_temp_data')
                . ' VALUES (1,' . $this->_getWriteAdapter()->quote(serialize(array('state' => self::STATE_PENDING))) . ');'
            );
            $_savedData = $this->_getReadAdapter()->fetchOne(
                'SELECT data FROM ' . $this->_getTableName('aw_hdu3_migration_temp_data')
            );
            $this->_migrationData = unserialize($_savedData);
        }
        return $this;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWriteAdapter()
    {
        if (null === $this->_writeAdapter) {
            $this->_writeAdapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        }
        return $this->_writeAdapter;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getReadAdapter()
    {
        if (null === $this->_readAdapter) {
            $this->_readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        }
        return $this->_readAdapter;
    }

    /**
     * Return table name with DB prefix
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function _getTableName($tableName)
    {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    protected function _log($message)
    {
        Mage::log($message, 1, 'hdu3_migration.log', true);
        return $this;
    }
}