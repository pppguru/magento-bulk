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


class AW_Helpdesk3_Block_Adminhtml_Department_Edit_Tab_Notification
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Notification Settings');
    }

    public function getTabTitle()
    {
        return $this->__('Notification Settings');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $this->_initForm();
        return parent::_prepareForm();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'aw_hdu3_department_notification_form',
            array('legend' => $this->__('Email Notifications'))
        );
        $fieldset->addField(
            'sender', 'select',
            array(
                'label'  => $this->__('Sender'),
                'name'   => 'notification[sender]',
                'values' => Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray(),
                'note'   => $this->__('This will be used as "From" address in emails to department/customer'),
            )
        );
        $fieldset->addField(
            'to_admin_new_ticket_email', 'select',
            array(
                'label'  => $this->__('New Ticket by customer, notification for support'),
                'name'   => 'notification[to_admin_new_ticket_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_customer_new_ticket_email', 'select',
            array(
                'label'  => $this->__('New Ticket by customer, notification for customer'),
                'name'   => 'notification[to_customer_new_ticket_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_customer_new_ticket_by_admin_email', 'select',
            array(
                'label'  => $this->__('New Ticket by support, notification for customer'),
                'name'   => 'notification[to_customer_new_ticket_by_admin_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_admin_new_reply_email', 'select',
            array(
                'label'  => $this->__('New Reply by customer, notification for support'),
                'name'   => 'notification[to_admin_new_reply_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_customer_new_reply_email', 'select',
            array(
                'label'  => $this->__('New Reply by support, notification for customer'),
                'name'   => 'notification[to_customer_new_reply_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_primary_agent_reassign_email', 'select',
            array(
                'label'  => $this->__('Ticket re-assignation, notification for primary agent'),
                'name'   => 'notification[to_primary_agent_reassign_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_new_assignee_reassign_email', 'select',
            array(
                'label'  => $this->__('Ticket re-assignation, notification for new assignee'),
                'name'   => 'notification[to_new_assignee_reassign_email]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $fieldset->addField(
            'to_customer_ticket_changed', 'select',
            array(
                'label'  => $this->__('Ticket re-assignation or status update, notification for customer'),
                'name'   => 'notification[to_customer_ticket_changed]',
                'values' => Mage::getModel('aw_hdu3/source_email_template')->toOptionArray(),
            )
        );
        $notificationData = Mage::registry('current_department')->getData('notification');
        if (null === $notificationData) {
            $notificationData = Mage::registry('current_department')->getEmailNotification()->getData();
        }
        $form->setValues($notificationData);
        $this->setForm($form);
    }
}