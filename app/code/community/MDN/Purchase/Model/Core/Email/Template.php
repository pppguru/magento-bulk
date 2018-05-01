<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Model_Core_Email_Template extends Mage_Core_Model_Email_Template {

    /**
     * Surcharge le send Transactional pour g�rer les pieces jointes
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars = array(), $storeId = null, $attachment = null) {
        $this->setSentSuccess(false);
        if ($this->getDesignConfig()) {
            if (($storeId === null) && $this->getDesignConfig()->getStore()) {
                $storeId = $this->getDesignConfig()->getStore();
            }
        }
        else
            $storeId = 0;

        if (is_numeric($templateId)) {
            $this->load($templateId);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $this->loadDefault($templateId, $localeCode);
        }

        if (!$this->getId()) {
            throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid transactional email code: ' . $templateId));
        }

        if (!is_array($sender)) {
            $this->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name', $storeId));
            $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email', $storeId));
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        if (!isset($vars['store'])) {
            $vars['store'] = Mage::app()->getStore($storeId);
        }

        $this->setSentSuccess($this->send($email, $name, $vars, $attachment));
        return $this;
    }

    /**
     * Rewrite method to allow attachment
     * */
    public function send($email, $name = null, array $variables = array(), $attachment = null) {
        //manage depending of magento version
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
            case '1.2':
            case '1.3':
            case '1.4':
            case '1.8':
            case '1.9':
                return $this->send14($email, $name, $variables, $attachment);
                break;
            case '1.5':
            default :
                return $this->send15($email, $name, $variables, $attachment);
                break;
        }
    }

    /**
     * Send email for "old" magento versions
     */
    public function send14($email, $name = null, array $variables = array(), $attachment = null) {

        if (!$this->isValidForSend()) {
            return false;
        }

        if (is_null($name)) {
            $name = substr($email, 0, strpos($email, '@'));
        }

        $variables['email'] = $email;
        $variables['name'] = $name;

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();
        if (is_array($email)) {
            foreach ($email as $emailOne) {
                $mail->addTo($emailOne, $name);
            }
        } else {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        //Ajoute la piece jointe
        if ((is_array($attachment))) {
            foreach ($attachment as $item) {
                $pj = $mail->createAttachment($item['content']);
                $pj->filename = $item['name'];
            }
        }

        try {
            $mail->send(); // Zend_Mail warning..
            $this->_mail = null;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Send email for "new" magento versions
     */
    public function send15($email, $name = null, array $variables = array(), $attachment = null) {

        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array) $email);
        $names = is_array($name) ? $name : (array) $name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        //Ajoute la piece jointe
        if ((is_array($attachment))) {
            foreach ($attachment as $item) {
                $pj = $mail->createAttachment($item['content']);
                $pj->filename = $item['name'];
            }
        }

        try {
            $mail->send();
            $this->_mail = null;
        } catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        return true;
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersion() {
        $version = mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1];
    }

}