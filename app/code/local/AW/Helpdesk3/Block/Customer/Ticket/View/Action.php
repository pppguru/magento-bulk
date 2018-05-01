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


class AW_Helpdesk3_Block_Customer_Ticket_View_Action extends Mage_Core_Block_Template
{

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return bool
     */
    public function isCanShow()
    {
        return $this->getTicket()->getStatus() != AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE;
    }

    /**
     * @return string
     */
    public function getPostAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $params['_secure'] = Mage::app()->getStore(true)->isCurrentlySecure();
        $params['id'] = $this->getTicket()->getId();
        return $this->getUrl('*/*/replyPost', $params);
    }

    /**
     * @return bool
     */
    public function isAttachmentAllowed()
    {
        /** @var AW_Helpdesk3_Helper_Config $config */
        $config = Mage::helper('aw_hdu3/config');
        return $config->isAllowCustomerToAttachFilesOnFrontend();
    }

    /**
     * @return null|int
     */
    public function getAttachmentLimitInMb()
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

    /**
     * @return string
     */
    public function getCloseTicketUrl()
    {
        $params = Mage::app()->getRequest()->getParams();
        $params['_secure'] = Mage::app()->getStore(true)->isCurrentlySecure();
        $params['id'] = $this->getTicket()->getId();
        return $this->getUrl('*/*/closeTicket', $params);
    }
}