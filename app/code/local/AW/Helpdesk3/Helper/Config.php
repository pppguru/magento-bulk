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


class AW_Helpdesk3_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_IS_ENABLED = 'aw_helpdesk3/general/is_enabled';
    const GENERAL_DEFAULT_DEPARTMENT = 'aw_helpdesk3/general/default_department';
    const GENERAL_TICKET_AUTOEXPIRATION = 'aw_helpdesk3/general/ticket_autoexpiration';
    const GENERAL_NEW_TICKET_FROM_INCOMING_EMAIL = 'aw_helpdesk3/general/gateway_new_ticket_from_incoming_email';
    const GENERAL_HIDE_CUSTOMER_ORDERS = 'aw_helpdesk3/general/hide_customer_orders';
    const GENERAL_SEND_CARBON_COPY_TO = 'aw_helpdesk3/general/send_carbon_copy_to';
    const GENERAL_IS_PQ_ENABLED = 'aw_helpdesk3/general/is_pq_enabled';
    const GENERAL_DISPLAY_COLUMNS = 'aw_helpdesk3/general/display_columns';
    const GENERAL_ALLOW_RATE = 'aw_helpdesk3/general/allow_rate';

    const FRONTEND_ALLOW_CUSTOMER_TO_ATTACH_FILES = 'aw_helpdesk3/frontend/allow_customer_to_attach_files';
    const FRONTEND_MAX_UPLOAD_FILE_SIZE = 'aw_helpdesk3/frontend/max_upload_file_size';
    const FRONTEND_ALLOW_FILE_EXTENSION = 'aw_helpdesk3/frontend/allow_file_extension';
    const FRONTEND_ALLOW_EXTERNAL_VIEW_FOR_TICKETS = 'aw_helpdesk3/frontend/allow_external_view_for_tickets';
    const FRONTEND_SHOW_SELECTORS = 'aw_helpdesk3/frontend/show_selectors';
    const FRONTEND_CONTACT_FORM = 'aw_helpdesk3/frontend/contact_form';
    const FRONTEND_SHOW_TICKET_PAGE_ENTITLES = 'aw_helpdesk3/frontend/show_ticket_page_entities';

    const TICKET_ESCALATION_ALLOW_TICKET_ESCALATION = 'aw_helpdesk3/ticket_escalation/allow_ticket_escalation';
    const TICKET_ESCALATION_SUPERVISOR_EMAILS = 'aw_helpdesk3/ticket_escalation/supervisor_emails';
    const TICKET_ESCALATION_EMAIL_TEMPLATE_TO_SUPERVISOR
        = 'aw_helpdesk3/ticket_escalation/email_template_to_supervisor';

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isPQEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_IS_PQ_ENABLED, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public static function getDefaultDepartmentId($store = null)
    {
        return (int)Mage::getStoreConfig(self::GENERAL_DEFAULT_DEPARTMENT, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int|null
     */
    public static function getTicketAutoExpiration($store = null)
    {
        $value = (int)Mage::getStoreConfig(self::GENERAL_TICKET_AUTOEXPIRATION, $store);
        if ($value <= 0) {
            return null;
        }
        return $value;
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isAllowCreateNewTicketsFromIncomingEmails($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_NEW_TICKET_FROM_INCOMING_EMAIL, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowCustomerOrdersForAllAgents($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_HIDE_CUSTOMER_ORDERS, $store)
            == AW_Helpdesk3_Model_Source_HideCustomerOrders::EXCEPT_ALL_VALUE
        ;
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowCustomerOrdersForPrimaryAgentOnly($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_HIDE_CUSTOMER_ORDERS, $store)
            == AW_Helpdesk3_Model_Source_HideCustomerOrders::EXCEPT_PRIMARY_AGENT_VALUE
        ;
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getCarbonCopyRecipientEmail($store = null)
    {
        return (string)trim(Mage::getStoreConfig(self::GENERAL_SEND_CARBON_COPY_TO, $store));
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isAllowCustomerToAttachFilesOnFrontend($store = null)
    {
        return (bool)Mage::getStoreConfig(self::FRONTEND_ALLOW_CUSTOMER_TO_ATTACH_FILES, $store);
    }

    /**
     * In MB
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public static function getMaxUploadFileSizeOnFrontend($store = null)
    {
        $value = (int)Mage::getStoreConfig(self::FRONTEND_MAX_UPLOAD_FILE_SIZE, $store);
        if ($value <= 0) {
            return 0;
        }
        return $value;
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getAllowFileExtension($store = null)
    {
        return (string)Mage::getStoreConfig(self::FRONTEND_ALLOW_FILE_EXTENSION, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isAllowExternalViewForTickets($store = null)
    {
        return (bool)Mage::getStoreConfig(self::FRONTEND_ALLOW_EXTERNAL_VIEW_FOR_TICKETS, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isAllowTicketEscalation($store = null)
    {
        return (bool)Mage::getStoreConfig(self::TICKET_ESCALATION_ALLOW_TICKET_ESCALATION, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return array
     */
    public static function getTicketEscalationSupervisorEmailList($store = null)
    {
        return (string)Mage::getStoreConfig(self::TICKET_ESCALATION_SUPERVISOR_EMAILS, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getTicketEscalationEmailTemplateToSupervisor($store = null)
    {
        return (string)Mage::getStoreConfig(self::TICKET_ESCALATION_EMAIL_TEMPLATE_TO_SUPERVISOR, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isIntegrationWithContactFormEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::FRONTEND_CONTACT_FORM, $store);
    }

    /*
    * @return bool
    */
    public static function isCanShowPrioritySelectorOnTicketCreate($store = null)
    {
        $selectors = Mage::getStoreConfig(self::FRONTEND_SHOW_SELECTORS, $store);
        if (!empty($selectors)) {
            $selectors = explode(',', $selectors);
        }
        return in_array(AW_Helpdesk3_Model_Source_Ticket_Frontend_Selectors::PRIORITY_VALUE, (array)$selectors);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowDepartmentSelectorOnTicketCreate($store = null)
    {
        $selectors = Mage::getStoreConfig(self::FRONTEND_SHOW_SELECTORS, $store);
        if (!empty($selectors)) {
            $selectors = explode(',', $selectors);
        }
        return in_array(AW_Helpdesk3_Model_Source_Ticket_Frontend_Selectors::DEPARTMENT_VALUE, (array)$selectors);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowOrderSelectorOnTicketCreate($store = null)
    {
        $selectors = Mage::getStoreConfig(self::FRONTEND_SHOW_SELECTORS, $store);
        if (!empty($selectors)) {
            $selectors = explode(',', $selectors);
        }
        return in_array(AW_Helpdesk3_Model_Source_Ticket_Frontend_Selectors::ORDER_VALUE, (array)$selectors);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowCurrentDepartmentOnTicketPage($store = null)
    {
        $selectors = Mage::getStoreConfig(self::FRONTEND_SHOW_TICKET_PAGE_ENTITLES, $store);
        if (!empty($selectors)) {
            $selectors = explode(',', $selectors);
        }
        return in_array(AW_Helpdesk3_Model_Source_Ticket_Page_Entities::DEPARTMENT_VALUE, (array)$selectors);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowCurrentPriorityOnTicketPage($store = null)
    {
        $selectors = Mage::getStoreConfig(self::FRONTEND_SHOW_TICKET_PAGE_ENTITLES, $store);
        if (!empty($selectors)) {
            $selectors = explode(',', $selectors);
        }
        return in_array(AW_Helpdesk3_Model_Source_Ticket_Page_Entities::PRIORITY_VALUE, (array)$selectors);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanShowSystemMessageOnTicketPage($store = null)
    {
        $selectors = Mage::getStoreConfig(self::FRONTEND_SHOW_TICKET_PAGE_ENTITLES, $store);
        if (!empty($selectors)) {
            $selectors = explode(',', $selectors);
        }
        return in_array(AW_Helpdesk3_Model_Source_Ticket_Page_Entities::SYSTEM_MESSAGES_VALUE, (array)$selectors);
    }

    /**
     *
     * @return bool
     */
    public static function getTicketDisplayColumn()
    {
        return Mage::getStoreConfig(self::GENERAL_DISPLAY_COLUMNS);
    }

    public static function isAllowRate()
    {
        return Mage::getStoreConfig(self::GENERAL_ALLOW_RATE);
    }

    public function isPrimaryDepartmentActive($storeId = null) {
        $depId = $this->getDefaultDepartmentId($storeId);
        /** @var AW_Helpdesk3_Model_Department $depModel */
        $depModel = Mage::getModel('aw_hdu3/department');
        $depModel->load($depId);
        return ($depModel->getId() && $depModel->isEnabled());
    }

    public function isActiveDepartments($storeId = null) {
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
        $departmentCollection->addActiveFilter();
        if ($storeId) {
            $departmentCollection->addFilterByStoreId($storeId);
        }
        if ($departmentCollection->getSize() > 0) {
            return true;
        }
        return false;
    }

}
