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
 * Class AW_Helpdesk3_Model_Gateway_Mail
 * @method string getId()
 * @method string getUid()
 * @method string getGatewayId()
 * @method string getFrom()
 * @method string getTo()
 * @method string getStatus()
 * @method string getSubject()
 * @method string getBody()
 * @method string getHeaders()
 * @method string getContentType()
 * @method string getRejectPatternId()
 * @method string getCreatedAt()
 */
class AW_Helpdesk3_Model_Gateway_Mail extends Mage_Core_Model_Abstract
{
    const STATUS_PROCESSED   = 1;
    const STATUS_UNPROCESSED = 2;
    const STATUS_REJECTED    = 3;

    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/gateway_mail');
    }

    /**
     * @param AW_Helpdesk3_Model_Gateway_Mail_Attachment
     *
     * @return $this
     */
    public function addAttachment($attachment)
    {
        $attachment
            ->setMailboxId($this->getId())
            ->save()
        ;
        return $this;
    }

    /**
     * @param array('filename' => $filename, 'content' => $content) $data
     *
     * @return $this
     */
    public function addAttachmentFromArray($data)
    {
        if (!array_key_exists('filename', $data) || !array_key_exists('filename', $data)) {
            return $this;
        }
        if (Mage::helper('aw_hdu3')->validateAttach($data['filename'], $data['content'], $this->getDefaultStoreId())) {
            $attachment = Mage::getModel('aw_hdu3/gateway_mail_attachment');
            $attachment
                ->setStoreId($this->getDefaultStoreId())
                ->setFile($data['filename'], $data['content'])
            ;
            $this->addAttachment($attachment);
        }
        return $this;
    }

    /**
     * @param AW_Helpdesk3_Model_Gateway_Mail_Attachment $attachment
     *
     * @return $this
     */
    public function removeAttachment($attachment)
    {
        $attachment->delete();
        return $this;
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Gateway_Mail_Attachment_Collection
     */
    public function getAttachmentCollection()
    {
        $attachmentCollection = Mage::getModel('aw_hdu3/gateway_mail_attachment')->getCollection();
        $attachmentCollection->addFilterByMailId($this->getId());
        return $attachmentCollection;
    }

    /**
     * @return null | int
     */
    public function getDepartmentId()
    {
        $gateway = Mage::getModel('aw_hdu3/gateway')->load($this->getGatewayId());
        return $gateway->getDepartmentId();
    }

    /**
     * @return bool
     */
    public function isCanConvertToTicket()
    {
        if ($this->getStatus() == self::STATUS_UNPROCESSED) {
            $patternId = $this->_getRejectPatternId();
            if (null === $patternId) {
                return true;
            }
            $this
                ->setRejectPatternId($patternId)
                ->setStatus(self::STATUS_REJECTED)
                ->save()
            ;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getDefaultStoreId()
    {
        $department = Mage::getModel('aw_hdu3/department')->load($this->getDepartmentId());
        return array_shift($department->getStoreIds());
    }

    /**
     * @return null | int
     */
    protected function _getRejectPatternId()
    {
        $patternCollection = Mage::getModel('aw_hdu3/gateway_mail_rejectPattern')->getCollection();
        $patternCollection->addActiveFilter();
        foreach ($patternCollection as $pattern) {
            $pattern->load($pattern->getId());
            if ($pattern->match($this)) {
                return $pattern->getId();
            }
        }
        return null;
    }
}