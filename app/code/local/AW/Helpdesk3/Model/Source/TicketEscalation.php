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


class AW_Helpdesk3_Model_Source_TicketEscalation extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        if ($this->getValue() == AW_Helpdesk3_Model_Source_Yesno::YES_VALUE && $this->_isCanEnabled()) {
            return parent::_beforeSave();
        }
        $this->setValue(AW_Helpdesk3_Model_Source_Yesno::NO_VALUE);
        return $this;
    }

    protected function _isCanEnabled()
    {
        $data = Mage::app()->getRequest()->getParam('groups', array());
        $supervisorEmails = $data['ticket_escalation']['fields']['supervisor_emails']['value'];
        if (trim($supervisorEmails) == '' || !$this->_isValidEmail($supervisorEmails)) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('aw_hdu3')->__('Ticket Escalation can\'t be enabled if "Supervisor email(s)" is empty.'
                    .' Please specify ticket escalation email recipient(s).'
                )
            );
            return false;
        }
        return true;
    }

    protected function _isValidEmail($supervisorEmails)
    {
        $emails = explode(',', $supervisorEmails);
        $emailValidator = new Zend_Validate_EmailAddress;
        foreach ($emails as $email) {
            if (!$emailValidator->isValid($email)) {
                return false;
            }
        }
        return true;
    }
}