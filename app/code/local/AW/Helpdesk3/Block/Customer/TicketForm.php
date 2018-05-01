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


class AW_Helpdesk3_Block_Customer_TicketForm extends Mage_Core_Block_Template
{
    protected $_cachedOrderOptionList = null;

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('*/*/createTicketPost', array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
    }

    /**
     * @return array
     */
    public function getDepartmentOptionList()
    {
        return AW_Helpdesk3_Model_Source_Department::toOptionArrayForStoreId();
    }

    /**
     * @return array
     */
    public function getPriorityOptionList()
    {
        return AW_Helpdesk3_Model_Source_Ticket_Priority::toOptionArray(Mage::app()->getStore()->getId());
    }

    /**
     * @return array
     */
    public function getOrderOptionList()
    {
        if (null === $this->_cachedOrderOptionList) {
            $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            $collection = Mage::getModel('sales/order')->getCollection();
            $collection->addFieldToFilter('customer_email', $customerEmail);
            $this->_cachedOrderOptionList = array();
            $this->_cachedOrderOptionList[] = array('value' => '', 'label' => $this->__('Unassigned'));
            foreach ($collection as $order) {
                /** @var Mage_Sales_Model_Order $order */
                $this->_cachedOrderOptionList[] = array(
                    'value' => $order->getId(),
                    'label' => $order->getIncrementId() . ', '
                               . $this->formatDate($order->getCreatedAtStoreDate()) . ', '
                               . $order->formatPrice($order->getGrandTotal())
                );
            }
        }
        return $this->_cachedOrderOptionList;
    }

    /**
     * @param int|string $priorityId
     *
     * @return bool
     */
    public function isPriorityDefault($priorityId)
    {
        return intval($priorityId) == AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE;
    }

    /**
     * @param int|string $departmentId
     *
     * @return bool
     */
    public function isDepartmentDefault($departmentId)
    {
        return intval($departmentId) == AW_Helpdesk3_Helper_Config::getDefaultDepartmentId();
    }

    /**
     * @return bool
     */
    public function isCanShowDepartment()
    {
        return AW_Helpdesk3_Helper_Config::isCanShowDepartmentSelectorOnTicketCreate();
    }

    /**
     * @return bool
     */
    public function isCanShowPriority()
    {
        return AW_Helpdesk3_Helper_Config::isCanShowPrioritySelectorOnTicketCreate();
    }

    /**
     * @return bool
     */
    public function isCanShowOrderAssign()
    {
        return AW_Helpdesk3_Helper_Config::isCanShowOrderSelectorOnTicketCreate()
            && count($this->getOrderOptionList()) > 0
        ;
    }

    /**
     * @return bool
     */
    public function isCanShowAttachment()
    {
        return AW_Helpdesk3_Helper_Config::isAllowCustomerToAttachFilesOnFrontend();
    }

    /**
     * file size in Mb
     *
     * @return null|int
     */
    public function getMaxAvailableFileSize()
    {
        $fileSizeInMb = AW_Helpdesk3_Helper_Config::getMaxUploadFileSizeOnFrontend();
        if ($fileSizeInMb <= 0) {
            return null;
        }
        return $fileSizeInMb;
    }

    /**
     * @return array
     */
    public function getAvailableFileExtensionList()
    {
        return explode(',', AW_Helpdesk3_Helper_Config::getAllowFileExtension());
    }
}
