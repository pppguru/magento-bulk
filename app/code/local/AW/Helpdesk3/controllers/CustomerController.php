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

class AW_Helpdesk3_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_checkEnabled();
        $this->_checkAuth();
    }

    protected function _checkAuth()
    {
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        return $this;
    }

    protected function _checkEnabled()
    {
        if (!Mage::helper('aw_hdu3/config')->isEnabled()) {
            $this->getResponse()->setRedirect(Mage::getUrl('no-route'))->sendResponse();
        }
        return $this;
    }

    protected function _initTicket()
    {
        $ticket = Mage::getModel('aw_hdu3/ticket')->load($this->getRequest()->getParam('id'));
        if (null === $ticket->getId()
            || $ticket->getCustomerEmail() != Mage::getSingleton('customer/session')->getCustomer()->getEmail()
            || $ticket->getArchived()==AW_Helpdesk3_Model_Ticket::ARCHIVED
        ) {
            $this->getResponse()->setRedirect(Mage::getUrl('no-route'))->sendResponse();
            return $this;
        }
        Mage::register('current_ticket', $ticket);
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Help Desk'));
        $this->renderLayout();
    }

    public function createTicketPostAction()
    {
        if ($formData = array_filter($this->getRequest()->getPost())) {
            $ticket = Mage::getModel('aw_hdu3/ticket');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $priorityId = AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE;
            $orderId = $this->getRequest()->getParam('order', null);
            $orderIncrementId = null;
            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                $orderIncrementId = $order->getIncrementId();
            }
            if (array_key_exists('priority', $formData) && !empty($formData['priority'])) {
                $priorityId = $formData['priority'];
            }
            $departmentId = null;
            if (Mage::helper('aw_hdu3/config')->getDefaultDepartmentId()) {
                $departmentId = Mage::helper('aw_hdu3/config')->getDefaultDepartmentId();
            }
            if (array_key_exists('department', $formData) && !empty($formData['department'])) {
                $departmentId = $formData['department'];
            }
            try {
                $department = Mage::getModel('aw_hdu3/department')->load($departmentId);
                if (null === $department->getId()) {
                    $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
                    $departmentCollection
                        ->sortByOrder()
                        ->addFilterByStoreId(Mage::app()->getStore()->getId())
                        ->addActiveFilter()
                    ;
                    $department = $departmentCollection->getFirstItem();
                }
                $attachments = $this->_getAttachments();
                $ticket
                    ->setDepartmentId($department->getId())
                    ->setDepartmentAgentId($department->getPrimaryAgentId())
                    ->setCustomerEmail($customer->getEmail())
                    ->setOrderIncrementId($orderIncrementId)
                    ->setCustomerName($customer->getFirstname() . ' ' . $customer->getLastname())
                    ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE)
                    ->setPriority($priorityId)
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setSubject($formData['title'])
                    ->save()
                ;
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                    array(
                        'content'     => $formData['message'],
                        'attachments' => $attachments
                    )
                );
                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Ticket has been successfully saved.')
                );
            } catch(Exception $e) {
                Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
            }
        }
        return $this->_redirectReferer();
    }

    public function viewTicketAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('[%s] %s - Help Desk', $ticket->getUid(), $ticket->getSubject()));
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('aw_hdu3/customer/index');
        }
        $this->renderLayout();
    }

    public function replyPostAction()
    {
        if ($formData = array_filter($this->getRequest()->getPost())) {
            $this->_initTicket();
            $ticket = Mage::registry('current_ticket');
            try {
                $attachments = $this->_getAttachments();
                if ($ticket->getStatus() != AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE) {
                    $ticket->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE);
                }
                $ticket
                    ->setIsReply(true)
                    ->save()
                    ->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                    array(
                        'content'     => $formData['content'],
                        'attachments' => $attachments
                    )
                );
                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Reply has been successfully saved.')
                );
            } catch(Exception $e) {
                Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
            }
        }
        return $this->_redirectReferer();
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getAttachments()
    {
        $attachmentNeeded = $this->getRequest()->getParam('attachment_needed', null);
        $attachments = array();
        if (!array_key_exists('attachments', $_FILES) || empty($_FILES['attachments']['tmp_name'])) {
            return $attachments;
        }
        if (!Mage::helper('aw_hdu3/config')->isAllowCustomerToAttachFilesOnFrontend()) {
            throw new Exception('Attachments are not allowed');
        }
        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
            if (!$attachmentNeeded || !array_key_exists($key, $attachmentNeeded)) {
                continue;
            }
            if (Mage::helper('aw_hdu3')->validateAttach($_FILES['attachments']['name'][$key],
                file_get_contents($_FILES['attachments']['tmp_name'][$key]))
            ) {
                $attach = Mage::getModel('aw_hdu3/ticket_history_attachment');
                $attach->setFile($_FILES['attachments']['name'][$key], file_get_contents($_FILES['attachments']['tmp_name'][$key]));
                $attachments[] = $attach;
            }
        }
        return $attachments;
    }

    public function closeTicketAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        try {
            $ticket
                ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE)
                ->save()
            ;
            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Ticket has been successfully closed.')
            );
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
        }
        return $this->_redirectReferer();
    }

    public function escalateAction()
    {
        if ($formData = array_filter($this->getRequest()->getPost())) {
            $this->_initTicket();
            $ticket = Mage::registry('current_ticket');
            try {
                $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Escalate::TYPE,
                    array(
                        'content'     => $formData['content'],
                        'attachments' => array()
                    )
                );
                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Escalate message has been successfully sent to supervisor.')
                );
            } catch(Exception $e) {
                Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
            }
        }
        return $this->_redirectReferer();
    }

    public function downloadAction()
    {
        $this->_initTicket();
        $attachId = $this->getRequest()->getParam('attachId', null);
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

    protected function _prepareDownloadResponse(
        $fileName, $content, $contentType = 'application/octet-stream', $contentLength = null
    )
    {
        $isFile = false;
        $file   = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                $isFile         = true;
                $file           = $content['value'];
                $contentLength  = filesize($file);
            }
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setHeader('Last-Modified', date('r'));

        if (!is_null($content)) {
            if ($isFile) {
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $ioAdapter = new Varien_Io_File();
                $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
                $ioAdapter->streamOpen($file, 'r');
                while ($buffer = $ioAdapter->streamRead()) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
                if (!empty($content['rm'])) {
                    $ioAdapter->rm($file);
                }
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        return $this;
    }
}
