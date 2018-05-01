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


class AW_Helpdesk3_Model_Ticket_Observer
{
    public function questionSaveAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_hdu3')->isModuleOutputEnabled()
            || !Mage::helper('aw_hdu3/config')->isEnabled()
            || !Mage::helper('aw_hdu3/config')->isPQEnabled()
        ) {
            return $this;
        }

        $question = $observer->getEvent()->getQuestion();
        if(!$question->isObjectNew()) {
            return $this;
        }
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
        $departmentCollection
            ->sortByOrder()
            ->addActiveFilter()
            ->addFilterByStoreId(Mage::app()->getStore()->getId())
        ;
        $department = $departmentCollection->getFirstItem();
        if (null === $department->getId()) {
            return $this;
        }
        try {
            $ticket = Mage::getModel('aw_hdu3/ticket');
            $ticket
                ->setDepartmentAgentId($department->getPrimaryAgentId())
                ->setDepartmentId($department->getId())
                ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE)
                ->setPriority(AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE)
                ->setCustomerName(Mage::helper('aw_hdu3')->stripTags($question->getAuthorName()))
                ->setCustomerEmail($question->getAuthorEmail())
                ->setSubject(Mage::helper('core/string')->truncate(
                        Mage::helper('aw_hdu3')->__(
                            "PQ on %s: %s",
                            Mage::helper('aw_hdu3')->stripTags($question->getProduct()->getName()),
                            $question->getContent()
                        ),
                        80
                    )
                )
                ->setStoreId(Mage::app()->getStore()->getId())
                ->save()
            ;
            $content = Mage::helper('aw_hdu3')->__(
                '<a href="%s" target="_blank">Edit Product Question entry</a></br>%s: <a href="%s" target="_blank">frontend</a> | <a href="%s" target="_blank">backend</a>',
                Mage::getSingleton('adminhtml/url')->getUrl('aw_pq2_admin/adminhtml_question/edit',
                    array('id' => $question->getId())
                ),
                Mage::helper('aw_hdu3')->stripTags($question->getProduct()->getName()),
                $question->getProduct()->getProductUrl(),
                Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/catalog_product/edit',
                    array('id' => $question->getProductId())
                )
            );
            $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                array(
                    'content'     => $question->getContent(),
                    'attachments' => array()
                )
            );
            $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Note::TYPE,
                array(
                    'content'     => $content,
                    'attachments' => array()
                )
            );
        } catch (Exception $e) {
            AW_Lib_Helper_Log::log($e->getMessage(), AW_Lib_Helper_Log::SEVERITY_ERROR);
        }
        return $this;
    }

    public function predispatchContactsIndexPost()
    {
        if (!Mage::helper('aw_hdu3')->isModuleOutputEnabled()
            || !Mage::helper('aw_hdu3/config')->isEnabled()
            || !Mage::helper('aw_hdu3/config')->isIntegrationWithContactFormEnabled()
        ) {
            return $this;
        }
        $postData = Mage::app()->getRequest()->getPost();
        try {
            $this->_createTicketFromContactForm($postData);
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('aw_hdu3')->__($e->getMessage()));
            $response = Mage::app()->getResponse();
            $response->setRedirect(Mage::getUrl('*/*/'))->sendResponse();
            exit;
        }
        return $this;
    }

    public function customerSaveBefore(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()->getDataObject();
        $newEmail = $data->getData('email');
        $oldEmail = $data->getOrigData('email');
        $websiteId = $data->getOrigData('website_id');

        Mage::getResourceModel('aw_hdu3/ticket')->updateCustomerEmailByWebsiteId($newEmail, $oldEmail, $websiteId);
    }

    protected function _createTicketFromContactForm($postData)
    {
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        if (!array_key_exists('form_key', $postData) || !$formKey || $postData['form_key'] != $formKey) {
            throw new Exception('Antispam protection triggered');
        }

        $department = Mage::getModel('aw_hdu3/department')->load(
            isset($postData['department']) ? $postData['department'] : null
        );
        if (null === $department->getId()) {
            $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
            $departmentCollection
                ->sortByOrder()
                ->addActiveFilter()
                ->addFilterByStoreId(Mage::app()->getStore()->getId())
            ;
            $department = $departmentCollection->getFirstItem();
        }

        $emailValidator = new Zend_Validate_EmailAddress;
        if (!array_key_exists('email', $postData) || empty($postData['email'])
            || !$emailValidator->isValid($postData['email'])
        ) {
            throw new Exception('Please specify correct email address');
        }

        if (array_key_exists('name', $postData)) {
            $postData['name'] = trim(strip_tags($postData['name']));
        }

        if (!array_key_exists('name', $postData) || empty($postData['name'])) {
            throw new Exception('Please specify name');
        }

        if (!array_key_exists('priority', $postData) || empty($postData['priority'])) {
            $postData['priority'] = AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE;
        }

        if (!array_key_exists('comment', $postData) || empty($postData['comment'])) {
            throw new Exception('Please leave a comment');
        }

        $attachments = $this->_getAttachments();
        $ticket = Mage::getModel('aw_hdu3/ticket');

        $ticket
            ->setDepartmentAgentId($department->getPrimaryAgentId())
            ->setDepartmentId($department->getId())
            ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE)
            ->setPriority($postData['priority'])
            ->setCustomerName($postData['name'])
            ->setCustomerEmail($postData['email'])
            ->setSubject(Mage::helper('aw_hdu3')->__(
                'Contact form %s <%s>', trim($postData['name']), trim($postData['email']))
            )
            ->setStoreId(Mage::app()->getStore()->getId())
            ->save()
        ;
        AW_Lib_Helper_Log::start(Mage::helper('aw_hdu3')->__('Got new ticket from contact form.'));
        AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Ticket UID[%s]', $ticket->getUid()));

        $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
            array(
                'content'     => $postData['comment'],
                'attachments' => $attachments
            )
        );
        return $this;
    }

    public function createFromOrder()
    {
        if (Mage::app()->getRequest()->getParam('create_ticket')) {
            $order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));

            $history = Mage::app()->getRequest()->getPost();
            $history = @$history['history'];

            $body = Mage::app()->getResponse()->getBody();
            if (!array_key_exists('comment', $history) || empty($history['comment'])) {
                $message = Mage::helper('aw_hdu3')->__('Ticket has not been saved. Comment is empty');
                $message = Zend_Json::encode($message);

                $body .= "<script type='text/javascript'>orderTicket.showMessage({$message},'error-msg')</script>";
                Mage::app()->getResponse()->setBody($body);
                return $this;
            }

            $department = null;
            if (
                Mage::helper('aw_hdu3/config')->isPrimaryDepartmentActive(Mage::app()->getStore()->getId())
                && $defaultDepartmentId = Mage::helper('aw_hdu3/config')->getDefaultDepartmentId(Mage::app()->getStore()->getId())
            ) {
                $department = Mage::getModel('aw_hdu3/department')->load($defaultDepartmentId);
            }

            if (!$department || !$department->getId()) {
                $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
                $departmentCollection
                    ->sortByOrder()
                    ->addActiveFilter()
                    ->addFilterByStoreId(Mage::app()->getStore()->getId())
                ;
                $department = $departmentCollection->getFirstItem();
            }

            if (!$department || !$department->getId()) {
                $message = Mage::helper('aw_hdu3')->__('Ticket has not been saved. There are no Primary Department or any Help Desk Departments configured');
                $message = Zend_Json::encode($message);
                $body .= "<script type='text/javascript'>orderTicket.showMessage({$message},'error-msg')</script>";
                Mage::app()->getResponse()->setBody($body);
                return $this;
            }

            $ticket = Mage::getModel('aw_hdu3/ticket');
            $ticket
                ->setDepartmentAgentId($department->getPrimaryAgentId())
                ->setDepartmentId($department->getId())
                ->setStatus(AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE)
                ->setPriority(AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE)
                ->setCustomerName($order->getCustomerName())
                ->setCustomerEmail($order->getCustomerEmail())
                ->setOrderIncrementId($order->getIncrementId())
                ->setSubject(Mage::helper('aw_hdu3')->__('Order #%s', $order->getIncrementId()))
                ->setStoreId($order->getStoreId())
                ->save()
            ;
            AW_Lib_Helper_Log::start(Mage::helper('aw_hdu3')->__('Got new ticket from order page.'));
            AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Ticket UID[%s]', $ticket->getUid()));

            $ticket->addHistory(AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE,
                array(
                    'content'     => trim(@$history['comment'])
                )
            );

            $ticketUrl = Mage::helper('adminhtml')->getUrl('helpdesk_admin/adminhtml_ticket/edit', array('id'  => $ticket->getId()));
            $ticketUrlHtml = Mage::helper('aw_hdu3')->__(
                'Ticket %s has been successfully saved', "<a href='{$ticketUrl}'>" . $ticket->getUid() . "</a>"
            );
            $ticketUrlHtml = Zend_Json::encode($ticketUrlHtml);

            $body .= "<script type='text/javascript'>orderTicket.showMessage({$ticketUrlHtml},'success-msg')</script>";
            Mage::app()->getResponse()->setBody($body);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getAttachments()
    {
        $attachmentNeeded = Mage::app()->getRequest()->getParam('attachment_needed', null);
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
}
