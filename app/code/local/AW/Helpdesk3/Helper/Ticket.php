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


class AW_Helpdesk3_Helper_Ticket extends Mage_Core_Helper_Abstract
{
    /**
     * @return AW_Helpdesk3_Model_Department_Agent
     */
    public function getCurrentDepartmentAgent()
    {
        $userId = null;
        if (Mage::app()->getStore()->isAdmin() && Mage::getModel('admin/session')->getUser()) {
            $userId = Mage::getModel('admin/session')->getUser()->getId();
        }
        return Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId($userId);
    }

    /**
     * Returns url to ticket in customer area
     *
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getExternalViewUrl($ticket)
    {
        $store = Mage::app()->getStore($ticket->getStoreId());
        return Mage::getUrl(
            "aw_hdu3/external/viewTicket",
            array(
                'key'    => base64_encode(
                    Mage::helper('core')->encrypt($ticket->getCustomerEmail() . ',' . $ticket->getId())
                ),
                '_store' => $store,
                '_secure' => (bool)Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_IN_FRONTEND, $ticket->getStoreId())
            )
        );
    }

    public function getExternalRateUrl($ticket, $rate)
    {
        $store = Mage::app()->getStore($ticket->getStoreId());
        return Mage::getUrl(
            "aw_hdu3/external/setRate",
            array(
                'key' => $this->encryptExternalKey($ticket->getCustomerEmail(),$ticket->getId()),
                'rate' => $rate,
                '_store' => $store,
                '_secure' => (bool)Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_IN_FRONTEND, $ticket->getStoreId())
            )
        );
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function decryptExternalKey($key)
    {
        return Mage::helper('core')->decrypt(base64_decode($key));
    }

    public function encryptExternalKey($customerEmail,$ticketId)
    {
        return base64_encode(Mage::helper('core')->encrypt($customerEmail . ',' . $ticketId));
    }
    /**
     * @param string $customerEmail
     *
     * @return AW_Helpdesk3_Model_Resource_Ticket_Collection
     */
    public function getCustomerTicketCollection($customerEmail)
    {
        return Mage::getModel('aw_hdu3/ticket')->getCollection()->addFilterByCustomerEmail($customerEmail);
    }

    /**
     * @param int $storeId
     *
     * @return AW_Helpdesk3_Model_Resource_Template_Collection
     */
    public function getTemplateCollection($storeId)
    {
        return Mage::getModel('aw_hdu3/template')->getCollection()->addFilterByStoreId($storeId);
    }

    /**
     * @param string | Mage_Customer_Model_Customer $customer
     *
     * @return AW_Helpdesk3_Model_Customer_Note
     */
    public function getCustomerNote($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customer = $customer->getEmail();
        }
        return Mage::getModel('aw_hdu3/customer_note')->loadByCustomerEmail($customer);
    }

    /**
     * @param array $emails
     *
     * @return array(customerEmail => customerId)
     */
    public function getCustomerIdsByCustomerEmails($emails)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
            ->from(
                array('customer' => Mage::getSingleton('core/resource')->getTableName('customer/entity')),
                array('email' => 'customer.email', 'entity_id' => 'customer.entity_id')
            )
            ->where('customer.email IN(?)', $emails)
        ;
        $customerEmails = $read->fetchPairs($select->__toString());
        foreach ($emails as $email) {
            if (!array_key_exists($email, $customerEmails)) {
                $customerEmails[$email] = null;
            }
        }
        return $customerEmails;
    }

    /**
    * Returns ticket UID from subject
    *
    * @param string $subject
    *
    * @return string|null
    */
    public function parseTicketUid($subject)
    {
        if (preg_match("/\[#([a-z]{3}-[0-9]{5})\]/i", $subject, $matches)) {
            return strtoupper(@$matches[1]);
        }
        return null;
    }

    /**
     * @param string $text
     * @param string $linkTemplate
     * @param string|null $customerEmailOnly
     *
     * @return array
     */
    public function replaceUidToLink($text, $linkTemplate, $customerEmailOnly = null)
    {
        preg_match_all("/([a-z]{3}-[0-9]{5})/i", $text, $matches);
        if (!array_key_exists(0, $matches)) {
            return $text;
        }
        $uidList = array_unique($matches[0]);
        foreach ($uidList as $uid) {
            /** @var AW_Helpdesk3_Model_Ticket $ticket */
            $ticket = Mage::getModel('aw_hdu3/ticket')->loadByUid($uid);
            if (null === $ticket->getId()) {
                continue;
            }
            if (null !== $customerEmailOnly && $ticket->getCustomerEmail() !== $customerEmailOnly) {
                continue;
            }
            $link = str_replace("{uid}", $uid, $linkTemplate);
            $link = str_replace("{id}", $ticket->getId(), $link);
            $text = str_replace($uid, $link, $text);
        }
        return $text;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket $ticket
     *
     * @return string
     */
    public function getFirstTicketMessage(AW_Helpdesk3_Model_Ticket $ticket)
    {
        $collection = Mage::getModel('aw_hdu3/ticket_message')->getCollection();
        $collection->addFilterByTicketId($ticket->getId());
        $collection->addOrder('id', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        return strip_tags($collection->getFirstItem()->getContent());
    }

    public function getDepartmentsConfigJson()
    {
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter();
        $config = array();
        foreach ($departmentCollection as $department) {
            $result = array();
            /** @var  AW_Helpdesk3_Model_Department $department */
            $agentList = $department->getAgentCollection()->addActiveFilter()->toOptionHash();
            $primaryId = $department->getPrimaryAgent()->getId();
            if (array_key_exists($primaryId, $agentList)) {
                $result[$primaryId] = $this->__('%s (Primary Agent)', $agentList[$primaryId]);
                unset($agentList[$primaryId]);
            }
            $meAgentId = $this->getCurrentDepartmentAgent()->getId();
            if (null !== $meAgentId && array_key_exists($meAgentId, $agentList)) {
                $result[$meAgentId] = $this->__('%s (Assign to me)', $agentList[$meAgentId]);
                unset($agentList[$meAgentId]);
            }
            $result += $agentList;
            $optionArray = array();
            foreach ($result as $agentId => $agentName) {
                $optionArray[] = array('label' => $agentName, 'value' => $agentId);
            }
            $config[$department->getId()] = $optionArray;
        }
        return Zend_Json::encode($config);
    }

    public function getParsedContent($content, $variables = array())
    {
        $filterProcessor = Mage::helper('cms')->getBlockTemplateProcessor();
        $filterProcessor->setVariables($variables);
        return $filterProcessor->filter($content);
    }

    public function getTicketVariables($ticket)
    {
        return Mage::getModel('aw_hdu3/department_notification')->getTicketEmailVariables($ticket);
    }

    /**
     * @return array
     */
    public function getAgentList()
    {
        $agentNameList = array();
        $agentCollection = Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter();
        foreach($agentCollection as $agent) {
            if(count($agent->getDepartmentCollection())) {
                $agentNameList[$agent->getId()] = $agent->getName();
            }
        }
        return $agentNameList;
    }

    public function isUserHasTickets($userId)
    {
        /** @var AW_Helpdesk3_Model_Department_Agent $agent */
        $agent = Mage::getModel('aw_hdu3/department_agent');
        $agent->loadAgentByUserId($userId);
        if (
            $agent->getId()
            && $agent->getStatus() == AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE
            && $agent->getTicketCollection()->addNotArchivedFilter()->getSize()
        ) {
            return true;
        }
        return false;
    }

    public function isUserPrimaryAgent($userId)
    {
        /** @var AW_Helpdesk3_Model_Department_Agent $agent */
        $agent = Mage::getModel('aw_hdu3/department_agent');
        $agent->loadAgentByUserId($userId);
        if (
            $agent->getId()
            && $agent->getStatus() == AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE
        ) {
            /** @var AW_Helpdesk3_Model_Resource_Department_Collection $departmentCollection */
            $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
            if (
                $departmentCollection
                    ->addNotDeletedFilter()
                    ->addFieldToFilter('primary_agent_id', $agent->getId())
                    ->getSize()
            ) {
                return true;
            }
        }
        return false;
    }

}
