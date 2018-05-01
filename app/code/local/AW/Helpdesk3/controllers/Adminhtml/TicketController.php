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


class AW_Helpdesk3_Adminhtml_TicketController extends Mage_Adminhtml_Controller_Action
{

    protected  $_publicActions=array('edit');

    protected function _isAllowed()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/ticket')) {
            return false;
        }
        return true;
    }

    protected function _initAction()
    {
        $this->_title($this->__('Tickets - Help Desk'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3');
        return $this;
    }

    protected function _initTicket()
    {
        $ticketId = (int)$this->getRequest()->getParam('id', 0);
        if ($ticketId > 0) {
            /** @var AW_Helpdesk3_Model_Resource_Ticket_Collection $collection */
            $collection = Mage::getModel('aw_hdu3/ticket')->getCollection();
            $collection->addNotArchivedFilter();
            $collection->addAdminUserFilter(Mage::getSingleton('admin/session')->getUser());
            $collection->addTicketIdFilter($ticketId);
            if ($collection->getSize() !== 1) {
                Mage::getSingleton('core/session')->addWarning($this->__('The ticket has been assigned to the department you do not have access to. You cannot view this ticket at the moment'));
                session_write_close();
                $this->_redirect('*/*/list');
            }
        }
        /** @var AW_Helpdesk3_Model_Ticket $ticket */
        $ticket = Mage::getModel('aw_hdu3/ticket')->load($ticketId);
        if (null !== Mage::getSingleton('adminhtml/session')->getHDUTicketFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getHDUTicketFormData())
        ) {
            $ticket->addData(Mage::getSingleton('adminhtml/session')->getHDUTicketFormData());
            Mage::getSingleton('adminhtml/session')->setHDUTicketFormData(null);
        }
        if ($ticket->isReadOnly()) {
            $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
            Mage::getSingleton('adminhtml/session')->addNotice($this->__('Ticket is locked by %s on %s',
                    $lockedBy->getName(),
                    Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                )
            );
        }
        Mage::register('current_ticket', $ticket);
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function editAction()
    {
        $this->_initTicket();
        $this->_initAction();
        /** @var AW_Helpdesk3_Model_Ticket $ticket */
        $ticket = Mage::registry('current_ticket');
        if (!$ticket->getId()) {
            Mage::getSingleton('core/session')->addError($this->__('Ticket not found'));
            session_write_close();
            $this->_redirect('*/*/list');
            return;
        }
        $this->_title($ticket->getUid());
        $this->renderLayout();
    }

    public function saveNewPostAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            Mage::getSingleton('adminhtml/session')->setEditorState($formData['awhdu3_content_state']);
            $this->_initTicket();
            $ticket = Mage::registry('current_ticket');
            $agentId = $formData['department_agent_id' . $formData['department_id']];
            if (!$agentId) {
                $department = Mage::getModel('aw_hdu3/department')->load($formData['department_id']);
                $agentId = $department->getPrimaryAgentId();
            }
            try {
                $attachments = $this->_getAttachments();
                $ticket
                    ->setDepartmentId($formData['department_id'])
                    ->setDepartmentAgentId($agentId)
                    ->setCustomerEmail($formData['customer_email'])
                    ->setCustomerName($formData['customer_name'])
                    ->setStatus($formData['status_id'])
                    ->setPriority($formData['priority_id'])
                    ->setStoreId($formData['store_id'])
                    ->setSubject($formData['title'])
                    ->setOrderIncrementId($formData['order_increment_id'])
                    ->save();
                if (isset($formData['content']) || count($attachments) > 0) {
                    $ticketVariables = Mage::helper('aw_hdu3/ticket')->getTicketVariables($ticket);
                    $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                        array(
                            'content' => Mage::helper('aw_hdu3/ticket')->getParsedContent($formData['content'], $ticketVariables),
                            'attachments' => $attachments
                        )
                    );
                }
                $tiketUrl = Mage::helper('adminhtml')->getUrl('*/*/edit', array('id' => $ticket->getId()));
                $tiketUrlHtml = "<a href='{$tiketUrl}'>" . $ticket->getUid() . "</a>";
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket %s has been successfully saved', $tiketUrlHtml));
                Mage::getSingleton('adminhtml/session')->setHDUTicketFormData(null);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
                Mage::getSingleton('adminhtml/session')->setHDUTicketFormData($formData);
                $this->_redirect(
                    '*/*/new',
                    array(
                        'id' => $ticket->getId(),
                        'customer_email' => $ticket->getCustomerEmail(),
                        'customer_name' => $ticket->getCustomerName(),
                        'return_customer_id' => $this->getRequest()->getParam('return_customer_id')
                    )
                );
                return;
            }
        }
        if ($this->getRequest()->getParam('return_customer_id')) {
            $this->_redirect('adminhtml/customer/edit/', array('id' => $this->getRequest()->getParam('return_customer_id'), 'tab' => 'Tickets'));
            return;
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getAttachments()
    {
        $attachmentNeeded = $this->getRequest()->getParam('attachment_needed', null);
        $attachments = array();
        if (!array_key_exists('attach', $_FILES) || empty($_FILES['attach']['tmp_name'])) {
            return $attachments;
        }
        foreach ($_FILES['attach']['tmp_name'] as $key => $tmpName) {
            if (!$attachmentNeeded || !array_key_exists($key, $attachmentNeeded)) {
                continue;
            }
            $attach = Mage::getModel('aw_hdu3/ticket_history_attachment');
            $attach->setFile($_FILES['attach']['name'][$key], file_get_contents($_FILES['attach']['tmp_name'][$key]));
            $attachments[] = $attach;
        }
        return $attachments;
    }

    public function replyPostAction()
    {
        if ($formData = array_filter($this->getRequest()->getPost())) {
            Mage::getSingleton('adminhtml/session')->setEditorState($formData['awhdu3_content_state']);
            $this->_initTicket();
            $ticket = Mage::registry('current_ticket');
            try {
                if ($ticket->isReadOnly()) {
                    $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                    throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                            $lockedBy->getName(),
                            Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                        )
                    );
                }
                $attachments = $this->_getAttachments();
                if ($formData['status'] == $ticket->getStatus()
                    && !isset($formData['content'])
                    && count($attachments) == 0
                ) {
                    throw new Mage_Core_Exception(
                        $this->__(
                            'Error: reply or attachment(s) are not specified, or "%s" is the same as current status',
                            $ticket->getStatusLabel()
                        )
                    );
                }
                if ($formData['status'] == AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE) {
                    $formData['status'] = AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE;
                }
                if (isset($formData['content']) || count($attachments) > 0) {
                    $ticket->setIsReply(true);
                }
                $ticket
                    ->setStatus($formData['status'])
                    ->save();
                if (isset($formData['content']) || count($attachments) > 0) {
                    $ticketVariables = Mage::helper('aw_hdu3/ticket')->getTicketVariables($ticket);
                    $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                        array(
                            'content' => isset($formData['content']) ? Mage::helper('aw_hdu3/ticket')->getParsedContent($formData['content'], $ticketVariables) : '',
                            'attachments' => $attachments
                        )
                    );
                }
                if (count($attachments) > 0) {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Reply with attachment(s) has been added.'));
                } else if (isset($formData['content'])) {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Reply has been added.'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket status has been changed.'));
                }

            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
            }
        }
        $this->_redirect('*/*/list');
    }

    public function exportCsvAction()
    {
        $fileName = 'hdu3_tickets.csv';
        $content = $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'hdu3_tickets.xml';
        $content = $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massAssignToDepartmentAction()
    {
        $ticketIds = $this->getRequest()->getParam('ticketIds', null);
        $departmentId = $this->getRequest()->getParam('department_list', null);
        try {
            if (!is_array($ticketIds)) {
                throw new Mage_Core_Exception($this->__('Invalid ticket id(s)'));
            }

            if (null === $departmentId) {
                throw new Mage_Core_Exception($this->__('Invalid department value'));
            }
            $department = Mage::getModel('aw_hdu3/department')->load($departmentId);
            if (null === $department->getId()) {
                throw new Mage_Core_Exception($this->__('Department with id %s doesn\'t exist', $departmentId));
            }
            $totalTickets = count($ticketIds);
            foreach ($ticketIds as $id) {
                $ticket = Mage::getModel('aw_hdu3/ticket')->load($id);
                if ($ticket->isReadOnly()) {
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Can not assign ticket <a href="%s" target="_blank">[%s]</a> it\'s locked',
                            Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_ticket/edit',
                                array('id' => $ticket->getId())
                            ),
                            $ticket->getUid()
                        )
                    );
                    $totalTickets--;
                    continue;
                }
                $ticket->setDepartmentId($department->getId());
                $departmentCollection=$department->getAgentCollection();
                $departmentCollection->addActiveFilter()
                    ->addFieldToFilter('main_table.id',$ticket->getDepartmentAgentId());

                if (!$departmentCollection->getSize()) {
                    $ticket->setDepartmentAgentId($department->getPrimaryAgent()->getId());
                }
                $ticket->save();
            }
            if ($totalTickets == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d ticket has been successfully updated', $totalTickets)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d tickets have been successfully updated', $totalTickets)
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }


    public function massAssignToAgentAction()
    {
        $ticketIds = $this->getRequest()->getParam('ticketIds', null);
        $agentId = $this->getRequest()->getParam('agent_list', null);
        try {
            if (!is_array($ticketIds)) {
                throw new Mage_Core_Exception($this->__('Invalid ticket id(s)'));
            }
            if (null === $agentId) {
                throw new Mage_Core_Exception($this->__('Invalid agent value'));
            }
            $agent = Mage::getModel('aw_hdu3/department_agent')->load($agentId);
            if (null === $agent->getId()) {
                throw new Mage_Core_Exception($this->__('Agent with id %s doesn\'t exist', $agentId));
            }
            $agentDepartmentIds = $agent->getDepartmentCollection()->getAllIds();
            if (count($agentDepartmentIds) == 0) {
                throw new Mage_Core_Exception(
                    $this->__('Can not assign tickets to selected agent, because this agent'
                        . ' does not belong to any department. To assign agent to department'
                        . ' <a href="%s" target="_blank">[click here]</a>',
                        Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_department')
                    )
                );
            }
            $newDepartment = Mage::getModel('aw_hdu3/department')->load($agentDepartmentIds[0]);
            $totalTickets = count($ticketIds);
            foreach ($ticketIds as $id) {
                $ticket = Mage::getModel('aw_hdu3/ticket')->load($id);
                if ($ticket->isReadOnly()) {
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Can not assign ticket <a href="%s" target="_blank">[%s]</a> it\'s locked',
                            Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_ticket/edit',
                                array('id' => $ticket->getId())
                            ),
                            $ticket->getUid()
                        )
                    );
                    $totalTickets--;
                    continue;
                }
                $ticketDepartmentAgentIds = $ticket->getDepartment()->getAgentCollection()->getAllIds();
                if (!in_array($agent->getId(), $ticketDepartmentAgentIds)) {
                    $ticket->setDepartmentId($newDepartment->getId());
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('<a href="%s" target="_blank">[%s]</a> assigned to "%s" department',
                            Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_ticket/edit',
                                array('id' => $ticket->getId())
                            ),
                            $ticket->getUid(),
                            $newDepartment->getTitle()
                        )
                    );
                }
                $ticket
                    ->setDepartmentAgentId($agent->getId())
                    ->save();
            }
            if ($totalTickets == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d ticket has been successfully updated', $totalTickets)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d tickets have been successfully updated', $totalTickets)
                );
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
        }
        $this->_redirectReferer();
    }

    public function massChangeStatusAction()
    {
        $ticketIds = $this->getRequest()->getParam('ticketIds', null);
        $statusId = $this->getRequest()->getParam('status_list', null);
        try {
            if (!is_array($ticketIds)) {
                throw new Exception('Invalid ticket id(s)');
            }

            if (null === $statusId) {
                throw new Exception('Invalid status value');
            }
            if ($statusId == AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE) {
                throw new Exception('This status can be used only once for ticket. Please choose an other status.');
            }
            $totalTickets = count($ticketIds);
            foreach ($ticketIds as $id) {
                $ticket = Mage::getModel('aw_hdu3/ticket')->load($id);
                if ($ticket->isReadOnly()) {
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Can not change status for ticket <a href="%s" target="_blank">[%s]</a> it\'s locked',
                            Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_ticket/edit',
                                array('id' => $ticket->getId())
                            ),
                            $ticket->getUid()
                        )
                    );
                    $totalTickets--;
                    continue;
                }
                $ticket
                    ->setStatus($statusId)
                    ->save();
            }
            if ($totalTickets == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d ticket has been successfully updated', $totalTickets)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d tickets have been successfully updated', $totalTickets)
                );
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
        }
        $this->_redirectReferer();
    }

    public function massChangePriorityAction()
    {
        $ticketIds = $this->getRequest()->getParam('ticketIds', null);
        $priorityId = $this->getRequest()->getParam('priority_list', null);
        try {
            if (!is_array($ticketIds)) {
                throw new Exception('Invalid ticket id(s)');
            }

            if (null === $priorityId) {
                throw new Exception('Invalid priority value');
            }
            $totalTickets = count($ticketIds);
            foreach ($ticketIds as $id) {
                $ticket = Mage::getModel('aw_hdu3/ticket')->load($id);
                if ($ticket->isReadOnly()) {
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Can not change priority for ticket <a href="%s" target="_blank">[%s]</a> it\'s locked',
                            Mage::getSingleton('adminhtml/url')->getUrl('helpdesk_admin/adminhtml_ticket/edit',
                                array('id' => $ticket->getId())
                            ),
                            $ticket->getUid()
                        )
                    );
                    $totalTickets--;
                    continue;
                }
                $ticket
                    ->setPriority($priorityId)
                    ->save();
            }
            if ($totalTickets == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d ticket has been successfully updated', $totalTickets)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d tickets have been successfully updated', $totalTickets)
                );
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
        }
        $this->_redirectReferer();
    }

    public function massChangeLockStatusAction()
    {
        $ticketIds = $this->getRequest()->getParam('ticketIds', null);
        $lockValue = $this->getRequest()->getParam('lock_list', null);
        try {
            if (!is_array($ticketIds)) {
                throw new Exception('Invalid ticket id(s)');
            }

            if (null === $lockValue) {
                throw new Exception('Invalid lock value');
            }
            foreach ($ticketIds as $id) {
                Mage::getModel('aw_hdu3/ticket')
                    ->load($id)
                    ->setLock($lockValue);
            }
            if (count($ticketIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d ticket has been successfully updated', count($ticketIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d tickets have been successfully updated', count($ticketIds))
                );
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {

        $ticketIds = $this->getRequest()->getParam('ticketIds', null);
        if (!$this->getRequest()->getParam('answer')) {
            $this->_redirectReferer();
            return;
        }
        try {
            if (!is_array($ticketIds)) {
                throw new Exception('Invalid ticket id(s)');
            }
            $count = 0;
            foreach ($ticketIds as $id) {
                /** @var AW_Helpdesk3_Model_Ticket $ticket */
                $ticket = Mage::getModel('aw_hdu3/ticket')->load($id);
                if (!$ticket->isReadOnly() && $ticket->agentCanViewTicket(Mage::getSingleton('admin/session')->getUser())) {
                    $ticket->delete();
                    $count++;
                }
            }
            switch ($count) {
                case '0':
                    throw new Exception($this->__("This ticket can not be removed"));
                    break;
                case '1':
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('Ticket has been successfully deleted'));
                    break;
                default:
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('%d tickets have been successfully deleted', count($ticketIds)));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
        }
        $this->_redirectReferer();
    }

    public function deleteAction()
    {

        try {
            if (!($ticketId = $this->getRequest()->getParam('id'))) {
                throw new Exception($this->__('Ticket id not set'));
            }
            $ticket = Mage::getModel('aw_hdu3/ticket')->load($ticketId);
            if (!$ticket->agentCanViewTicket(Mage::getSingleton('admin/session')->getUser())) {
                throw new Exception($this->__("You not have permission to access"));
            }

            if ($ticket->isReadOnly()) {
                throw new Exception($this->__("This ticket can not be removed because it???s read-only"));
            }
            $ticket->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Ticket has been successfully deleted')
            );

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
            if (isset($ticketId) && $ticketId > 0) {
                $this->_redirect('*/*/edit', array('id' => $ticketId));
                return;
            }
        }
        $this->_redirect('*/*/list');
    }

    public function ajaxChangeStatusAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $statusId = $this->getRequest()->getParam('status', null);
        $note = trim($this->getRequest()->getParam('note', ''));
        $result = array(
            'success' => false,
            'data' => array(),
            'msg' => ''
        );
        try {
            if ($ticket->isReadOnly()) {
                $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                        $lockedBy->getName(),
                        Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                    )
                );
            }
            $status = Mage::getModel('aw_hdu3/ticket_status')->load($statusId);
            if (null === $status->getId()) {
                throw new Mage_Core_Exception($this->__('Invalid status value'));
            }
            $ticket
                ->setStatus($statusId)
                ->save();
            $result['data'] = array(
                'textColor' => $status->getFontColor(),
                'bgColor' => $status->getBackgroundColor(),
                'label' => $ticket->getStatusLabel()
            );
            if (!empty($note)) {
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE,
                    array(
                        'content' => $note,
                        'attachments' => array()
                    )
                );
            }
            $result['msg'] = $this->__('Ticket status has been successfully updated.');
            $result['success'] = true;
        } catch (Mage_Core_Exception $e) {
            $result['msg'] = $e->getMessage();
        } catch (Exception $e) {
            $result['msg'] = $this->__($e->getMessage());
        }
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxChangePriorityAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $priorityId = $this->getRequest()->getParam('priority', null);
        $note = trim($this->getRequest()->getParam('note', ''));
        $result = array(
            'success' => false,
            'data' => array(),
            'msg' => ''
        );
        try {
            if ($ticket->isReadOnly()) {
                $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                        $lockedBy->getName(),
                        Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                    )
                );
            }
            $priority = Mage::getModel('aw_hdu3/ticket_priority')->load($priorityId);
            if (null === $priority->getId()) {
                throw new Mage_Core_Exception($this->__('Invalid priority value'));
            }
            $ticket
                ->setPriority($priorityId)
                ->save();
            $result['data'] = array(
                'textColor' => $priority->getFontColor(),
                'bgColor' => $priority->getBackgroundColor(),
                'label' => $ticket->getPriorityLabel()
            );
            if (!empty($note)) {
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE,
                    array(
                        'content' => $note,
                        'attachments' => array()
                    )
                );
            }
            $result['msg'] = $this->__('Ticket priority has been successfully updated.');
            $result['success'] = true;
        } catch (Mage_Core_Exception $e) {
            $result['msg'] = $e->getMessage();
        } catch (Exception $e) {
            $result['msg'] = $this->__($e->getMessage());
        }
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxChangeAssigneeAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $agentId = $this->getRequest()->getParam('agent', null);
        $departmentId = $this->getRequest()->getParam('department', null);
        //
        $statusId = $this->getRequest()->getParam('status', null);
        $priorityId = $this->getRequest()->getParam('priority', null);
        $orderId = $this->getRequest()->getParam('order', null);
        //
        $lock = $this->getRequest()->getParam('lock', null);
        $note = trim($this->getRequest()->getParam('note', ''));
        try {

            $department = Mage::getModel('aw_hdu3/department')->load($departmentId);
            if (null === $department->getId()) {
                throw new Mage_Core_Exception($this->__('Invalid department value'));
            }
            $agent = Mage::getModel('aw_hdu3/department_agent')->load($agentId);
            if (null === $agent->getId()) {
                throw new Mage_Core_Exception($this->__('Invalid agent value'));
            }
            //status
            $status = Mage::getModel('aw_hdu3/ticket_status')->load($statusId);
            if ($ticket->getStatus() != $statusId && $ticket->isReadOnly()) {
                $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                        $lockedBy->getName(),
                        Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                    )
                );
            } elseif(null === $status->getId()) {
                throw new Mage_Core_Exception($this->__('Invalid status value'));
            }
            //$priority

            $priority = Mage::getModel('aw_hdu3/ticket_priority')->load($priorityId);
            if ($ticket->getPriority()!=$priorityId && $ticket->isReadOnly()) {
                $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                        $lockedBy->getName(),
                        Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                    )
                );
            } elseif (null === $priority->getId()) {
                throw new Mage_Core_Exception($this->__('Invalid priority value'));
            }
            //$order

            $order = Mage::getModel('sales/order')->load($orderId);
            if ($ticket->getOrderIncrementId()!= $order->getIncrementId() && $ticket->isReadOnly()) {
                $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                        $lockedBy->getName(),
                        Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                    )
                );
            }

            $ticket
                ->setDepartmentId($departmentId)
                ->setDepartmentAgentId($agentId)
                ->setStatus($statusId)
                ->setPriority($priorityId)
                ->setOrderIncrementId($order->getIncrementId())
                ->save();
            if (!empty($note)) {
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE,
                    array(
                        'content' => $note,
                        'attachments' => array()
                    )
                );
            }
            if ($ticket->isReadOnly() && null === $lock) {
                $ticket->setLock(AW_Helpdesk3_Model_Source_Ticket_Lock::UNLOCKED_VALUE);
            }
            if (!$ticket->isReadOnly() && null !== $lock) {
                $ticket->setLock(AW_Helpdesk3_Model_Source_Ticket_Lock::LOCKED_VALUE);
            }
            $result['success'] = true;
            $result['msg'] = $this->__('Ticket has been successfully reassigned.');
        } catch (Exception $e) {
            $result['success'] = false;
            $result['msg'] = $this->__($e->getMessage());
        }
        $result['data'] = array(
            'isLocked' => $ticket->isReadOnly(),
            'departmentLabel' => $ticket->getDepartment()->getTitle(),
            'agentLabel' => $ticket->getDepartmentAgent()->getName()
        );
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxChangeOrderAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $orderId = $this->getRequest()->getParam('order', null);
        $note = trim($this->getRequest()->getParam('note', ''));
        try {
            if ($ticket->isReadOnly()) {
                $lockedBy = Mage::getModel('aw_hdu3/department_agent')->load($ticket->getLockedByDepartmentAgentId());
                throw new Mage_Core_Exception($this->__('Ticket is locked by %s on %s',
                        $lockedBy->getName(),
                        Mage::helper('core')->formatDate($ticket->getLocketAt(), 'long', true)
                    )
                );
            }
            $order = Mage::getModel('sales/order')->load($orderId);
            $ticket->setOrderIncrementId($order->getIncrementId());
            $ticket->save();
            if (!empty($note)) {
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE,
                    array(
                        'content' => $note,
                        'attachments' => array()
                    )
                );
            }
            $result['data'] = array(
                'label' => (null !== $order->getIncrementId()) ? $order->getIncrementId() : $this->__('Unassigned'),
                'orderId' => (null !== $order->getId()) ? $order->getId() : 0
            );
            $result['msg'] = $this->__('Ticket has been successfully updated.');
            $result['success'] = true;
        } catch (Mage_Core_Exception $e) {
            $result['msg'] = $e->getMessage();
            $result['data'] = array(
                'label' => $this->__('Unassigned'),
                'orderId' => 0
            );
        } catch (Exception $e) {
            $result['msg'] = $this->__($e->getMessage());
            $result['data'] = array(
                'label' => $this->__('Unassigned'),
                'orderId' => 0
            );
        }
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxAddNoteAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $note = trim($this->getRequest()->getParam('note', ''));
        $result = array(
            'success' => true,
            'data' => array(),
            'msg' => ''
        );
        try {
            if (!empty($note)) {
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE,
                    array(
                        'content' => $note,
                        'attachments' => array()
                    )
                );
            }
            $result['msg'] = $this->__('Ticket has been successfully updated.');
        } catch (Exception $e) {
            $result['success'] = false;
            $result['msg'] = $this->__($e->getMessage());
        }
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxChangeCustomerInfoAction()
    {
        $content = trim($this->getRequest()->getParam('content', ''));
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $customerNote = Mage::getModel('aw_hdu3/customer_note')->loadByCustomerEmail($ticket->getCustomerEmail());
        $currentAgent = Mage::helper('aw_hdu3/ticket')->getCurrentDepartmentAgent();
        $currentDate = new Zend_Date();
        try {
            $customerNote
                ->setNote(trim($content))
                ->setCustomerEmail($ticket->getCustomerEmail())
                ->setInitiatorDepartmentAgentId($currentAgent->getId())
                ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
                ->save();
            $result['msg'] = $this->__('Customer note has been successfully updated.');
            $result['success'] = true;
        } catch (Exception $e) {
            $result['msg'] = $this->__($e->getMessage());
        }
        $text = trim(nl2br($content));
        if (!trim($content)) {
            $text = $this->__('No data');
        }
        $result['data'] = array(
            'text' => $text,
            'originalText' => Zend_Json::encode($content)
        );
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxGetQuickResponseAction()
    {
        $templateId = trim($this->getRequest()->getParam('qr_id', null));
        try {
            $template = Mage::getModel('aw_hdu3/template')->load($templateId);
            if (null === $template->getId()) {
                throw new Exception('Invalid template value');
            }
            $result = array(
                'success' => true,
                'data' => array(
                    'text' => $template->getContent()
                ),
                'msg' => ''
            );
        } catch (Exception $e) {
            $result = array(
                'success' => false,
                'data' => array(
                    'text' => ''
                ),
                'msg' => $this->__($e->getMessage())
            );
        }
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function ajaxFindCustomerAction()
    {
        $search = $this->getRequest()->getParam('search', '');
        $data = array();
        $collection = Mage::helper('aw_hdu3')->getCustomerCollectionByEmail($search, 10);
        foreach ($collection as $customer) {
            $data[] = array(
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname()
            );
        }
        $response = "<ul>";
        foreach ($data as $item) {
            $response .= sprintf(
                "<li data-email='%s' data-name='%s'>%s &lt;%s&gt;</li>",
                $item['email'], $item['name'], $item['name'], $item['email']
            );
        }
        $response .= "</ul>";
        $this->getResponse()->setBody($response);
    }

    public function ajaxGetThreadTabsContentHtmlAction()
    {
        $this->_initTicket();
        $this->loadLayout();
        $thread = $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_thread');
        $result = array(
            'all' => $thread->getChildHtml('tab.content.all'),
            'discussion' => $thread->getChildHtml('tab.content.discussion'),
            'notes' => $thread->getChildHtml('tab.content.notes'),
            'history' => $thread->getChildHtml('tab.content.history')
        );
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    public function downloadAction()
    {
        $attachId = $this->getRequest()->getParam('id', null);
        if (null !== $attachId) {
            $attachment = Mage::getModel('aw_hdu3/ticket_history_attachment')->load($attachId);
            if (null !== $attachment->getId()) {
                $this->_prepareDownloadResponse($attachment->getFileRealName(),
                    file_get_contents($attachment->getFilePath())
                );
                return $this;
            }
        }
        $this->_forward('noRoute');
    }
}
