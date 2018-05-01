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
 * Class AW_Helpdesk3_Model_Gateway
 * @method string getId()
 * @method string getDepartmentId()
 * @method string getTitle()
 * @method string getIsActive()
 * @method string getProtocol()
 * @method string getEmail()
 * @method string getHost()
 * @method string getLogin()
 * @method string getPort()
 * @method string getSecureType()
 * @method string getDeleteEmails()
 */
class AW_Helpdesk3_Model_Gateway extends Mage_Core_Model_Abstract
{
    protected $_connection = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/gateway');
    }

    /**
     * @param string $email
     *
     * @return this
     */
    public function loadByEmail($email)
    {
        return $this->load($email, 'email');
    }

    /**
     * @return $this
     */
    public function process()
    {
        AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Init connection.'));
        $this->_initConnection();
        if (null !== $this->_getConnection()) {
            $this->_prepareAndSaveNewMails();
        }
        return $this;
    }

    /**
     * @return Exception | $this
     */
    public function testConnection()
    {
        $this->_initConnection();
        return $this;
    }

    /**
     * @return null | string
     */
    protected function _getProtocolInstance()
    {
        return Mage::getModel('aw_hdu3/source_gateway_protocol')->getInstanceByProtocol($this->getProtocol());
    }

    /**
     * @return $this
     */
    protected function _initConnection()
    {
        $instanceConstructor = $this->_getProtocolInstance();
        if (null === $this->_connection && null !== $instanceConstructor) {
            $this->_connection = new $instanceConstructor($this->_getConnectionParams());
        }
        return $this;
    }

    /**
     * Returns parameters for connection
     *
     * @return array
     */
    protected function _getConnectionParams()
    {
        $params = array(
            'host'     => $this->getHost(),
            'user'     => $this->getLogin(),
            'password' => $this->getPassword()
        );
        if ($this->getPort()) {
            $params['port'] = $this->getPort();
        }
        $params['ssl'] = Mage::getModel('aw_hdu3/source_gateway_secure')->getTypeCodeByValue($this->getSecureType());
        return $params;
    }

    /**
     * @return null | Zend_Mail_Storage_Imap | Zend_Mail_Storage_Pop3
     */
    protected function _getConnection()
    {
        return $this->_connection;
    }

    /**
     * @return $this
     */
    protected function _prepareAndSaveNewMails()
    {
        try {
            $mailboxUIDs = $this->_getNewMessageUids();
        } catch (Exception $e) {
            $mailboxUIDs = array();
        }
        AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Got (%s) new message(s).', count($mailboxUIDs)));
        if (count($mailboxUIDs) > 0) {
            AW_Lib_Helper_Log::start(Mage::helper('aw_hdu3')->__('Save messages.'));
            foreach ($mailboxUIDs as $messageUid) {
                $message = $this->_getMessageByUid($messageUid);
                $this->_convertMessageToMail($message, $messageUid);
            }
            AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Messages have been successfully saved.'));
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _getNewMessageUids()
    {
        //message uids from email gateway
        $mailboxUIDs = $this->_getConnection()->getUniqueId();

        //already saved message uids
        $unExistUIDs = $this->_getUnExistMessageUIDs($mailboxUIDs);

        //get only unread messages - for IMAP only
        if ($this->getProtocol() == AW_Helpdesk3_Model_Source_Gateway_Protocol::IMAP_VALUE) {
            foreach ($unExistUIDs as $key => $messageUid) {
                $message = $this->_getMessageByUid($messageUid);
                if ($message->hasFlag(Zend_Mail_Storage::FLAG_SEEN) === true) {
                    unset($unExistUIDs[$key]);
                }
            }
        }
        return $unExistUIDs;
    }

    /**
     * @param array $mailboxUIDs
     *
     * @return array
     */
    protected function _getUnExistMessageUIDs($mailboxUIDs)
    {
        $existUIDs = Mage::getResourceModel('aw_hdu3/gateway_mail')->getExistMailUIDs($this->getId());
        return array_diff($mailboxUIDs, $existUIDs);
    }

    /**
     * @param string $messageUid
     *
     * @return bool
     */
    protected function _isMailExistByMessageUid($messageUid)
    {
        $mailUid = $this->getMailUidByMessageUid($messageUid);
        return Mage::getResourceModel('aw_hdu3/gateway_mail')->isMailExistByMailUid($mailUid);
    }

    /**
     * @param string $uid
     *
     * @return Zend_Mail_Message
     */
    protected function _getMessageByUid($uid)
    {
        return $this->_getMessageByNumber($this->_getMessageNumberByUid($uid));
    }

    /**
     * @param $uid
     *
     * @return string
     */
    protected function _getMessageNumberByUid($uid)
    {
        return $this->_getConnection()->getNumberByUniqueId($uid);
    }

    /**
     * @param $number
     *
     * @return Zend_Mail_Message
     */
    protected function _getMessageByNumber($number)
    {
        return $this->_getConnection()->getMessage($number);
    }

    protected function _convertMessageToMail($message, $messageUid)
    {
        $messageNumber = $this->_getMessageNumberByUid($messageUid);
        $currentDate = new Zend_Date();
        $mail = Mage::getModel('aw_hdu3/gateway_mail');
        $mail
            ->setUid($this->getMailUidByMessageUid($messageUid))
            ->setGatewayId($this->getId())
            ->setFrom($this->_getMessageFrom($message))
            ->setTo($this->getEmail())
            ->setStatus(AW_Helpdesk3_Model_Gateway_Mail::STATUS_UNPROCESSED)
            ->setBody($this->_getMessageBody($message))
            ->setHeaders($this->_getMessageHeadersByNumber($messageNumber))
            ->setSubject($this->_getMessageSubject($message))
            ->setContentType(strtok($this->_getMessageContentType($message), ';'))
            ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
        ;
        try {
            $mail->save();
            AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Message saved with UID[%s]', $mail->getUid()));
        } catch(Exception $e) {
            AW_Lib_Helper_Log::log($e->getMessage(), AW_Lib_Helper_Log::SEVERITY_ERROR);
        }
        if ($this->getIsAllowAttachment()) {
            $attachments = $this->_getMessageAttachment($message);
            if (is_array($attachments)) {
                foreach ($attachments as $attach) {
                    if (is_array($attach)) {
                        AW_Lib_Helper_Log::log(Mage::helper('aw_hdu3')->__('Got new attachment (%s)', $attach['filename']));
                        try {
                            $mail->addAttachmentFromArray($attach);
                        } catch(Exception $e) {
                            AW_Lib_Helper_Log::log($e->getMessage(), AW_Lib_Helper_Log::SEVERITY_ERROR);
                        }
                    }
                }
            }
        }

        //remove mail from gateway
        if ($this->getDeleteEmails()) {
            $this->_removeMessageFromServerByNumber($messageNumber);
        }
        return $this;
    }

    /**
     * @param Zend_Mail_Message $message
     *
     * @return string
     */
    protected function _getMessageContentType($message)
    {
        $part = $this->_getMainPart($message);
        try {
            $headers = $part->getHeaders();
            $contentType = @$headers['content-type'] ? $headers['content-type']
                : Zend_Mime::TYPE_TEXT;
        } catch (Exception $e) {
            $contentType = Zend_Mime::TYPE_TEXT;
        }
        return $contentType;
    }

    /**
     * @param Zend_Mail_Message $message
     *
     * @return string
     */
    protected function _getMessageBody($message)
    {
        // Get first flat part
        $part = $this->_getMainPart($message);

        $headers = $part->getHeaders();
        $encodedContent = $part->getContent();

        // Decoding transfer-encoding
        switch (strtolower($transferEncoding = @$headers['content-transfer-encoding'])) {
            case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                $content = quoted_printable_decode($encodedContent);
                break;
            case Zend_Mime::ENCODING_BASE64:
                $content = base64_decode($encodedContent);
                break;
            default:
                $content = $encodedContent;
        }

        $contentType = $this->_getMessageContentType($message);
        foreach (explode(";", $contentType) as $headerPart) {
            $headerPart = strtolower(trim($headerPart));
            if (strpos($headerPart, 'charset=') !== false) {
                $charset = preg_replace('/charset=[^a-z0-9\-_]*([a-z\-_0-9]+)[^a-z0-9\-]*/i', "$1", $headerPart);
                return iconv($charset, 'UTF-8', $content);
            }
        }
        return $content;
    }

    /**
     * @param Zend_Mail_Message $message
     *
     * @return string
     */
    protected function _getMessageSubject($message)
    {
        $subject = iconv_mime_decode($message->subject, 0, "UTF-8");
        if ($subject === FALSE) {
            $subject = Mage::helper('aw_hdu3')->__('No Subject');
        }
        return $subject;
    }

    /**
     * @param Zend_Mail_Message $message
     *
     * @return string
     */
    protected function _getMessageFrom($message)
    {
        try {
            $encoding = iconv_get_encoding();
            $from = iconv($encoding['internal_encoding'], 'UTF-8', $message->from);
        } catch (Exception $e) {
            $from = Mage::helper('aw_hdu3')->__('Unknown');
        }
        if (!$from) {
            $from = $message->from;
        }
        return $from;
    }

    /**
     * @param string $number
     *
     * @return string
     */
    protected function _getMessageHeadersByNumber($number)
    {
        return $this->_getConnection()->getRawHeader($number);
    }

    /**
     * @param Zend_Mail_Message $message
     *
     * @return array('filename' => $filename, 'content' => $content) | false
     */
    protected function _getMessageAttachment($message)
    {
        $data = array();

        // Get first flat part
        if ($message->isMultipart()) {
            $parts = $message;
            foreach (new RecursiveIteratorIterator($parts) as $part) {
                $attach = $this->_getMessageAttachment($part);
                if ($attach) {
                    $data[] = $attach;
                }
            }
        } else {
            $headers = $message->getHeaders();
            $isAttachment = null;
            foreach ($headers as $value) {
                if (is_array($value)) {
                    $value = implode(";", $value);
                }
                if ($isAttachment = preg_match('/(name|filename)="{0,1}([^;\"]*)"{0,1}/si', $value, $matches)) {
                    break;
                }
            }
            if ($isAttachment) {
                $filename = $matches[2];
                $encodedContent = $message->getContent();

                // Decoding transfer-encoding
                switch ($transferEncoding = @$headers['content-transfer-encoding']) {
                    case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                        $content = quoted_printable_decode($encodedContent);
                        break;
                    case Zend_Mime::ENCODING_BASE64:
                        $content = base64_decode($encodedContent);
                        break;
                    default:
                        $content = $encodedContent;
                }

                $filename = iconv_mime_decode(
                    $filename,
                    ICONV_MIME_DECODE_CONTINUE_ON_ERROR,
                    'UTF-8'
                );
                return array('filename' => $filename, 'content' => $content);
            }
            return false;
        }
        return $data;
    }

    /**
     * @param string $messageUid
     *
     * @return string
     */
    public function getMailUidByMessageUid($messageUid)
    {
        return $messageUid . $this->getEmail() . $this->getId();
    }

    /**
     * @param $number
     *
     * @return $this
     */
    protected function _removeMessageFromServerByNumber($number)
    {
        $this->_getConnection()->removeMessage($number);
        return $this;
    }

    /**
     * Returns main mail part
     *
     * @param Zend_Mail_Message $message
     *
     * @return Zend_Mail_Message
     */
    protected function _getMainPart(Zend_Mail_Message $message)
    {
        // Get first flat part
        $part = $message;
        while ($part->isMultipart()) {
            $part = $part->getPart(1);
        }
        return $part;
    }

    /**
     * @param $departmentId
     *
     * @return $this
     */
    public function loadByDepartmentId($departmentId)
    {
        return $this->load($departmentId, 'department_id');
    }
}
