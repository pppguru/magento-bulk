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


class AW_Helpdesk3_Block_Adminhtml_Department_Edit_Tab_Gateway
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Email Gateway');
    }

    public function getTabTitle()
    {
        return $this->__('Email Gateway');
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
        $fieldset = $form->addFieldset('aw_hdu3_gateway_form', array('legend' => $this->__('Email Gateway Details')));
        $fieldset->addField(
            'gateway_is_active', 'select',
            array(
                'label'  => $this->__('Enable Email Gateway'),
                'name'   => 'gateway[is_active]',
                'values' => AW_Helpdesk3_Model_Source_Yesno::toOptionArray(),
                'note'   => $this->__('Converting new emails that come to a gateway mailbox to tickets')
            )
        );
        $fieldset->addField(
            'gateway_title', 'text',
            array(
                'label'    => $this->__('Title'),
                'name'     => 'gateway[title]',
                'required' => true,
            )
        );
        $fieldset->addField(
            'gateway_protocol', 'select',
            array(
                'label'  => $this->__('Protocol'),
                'name'   => 'gateway[protocol]',
                'values' => AW_Helpdesk3_Model_Source_Gateway_Protocol::toOptionArray()
            )
        );
        $fieldset->addField(
            'gateway_email', 'text',
            array(
                'label'    => $this->__('Gateway Email'),
                'name'     => 'gateway[email]',
                'required' => true,
                'note'     => $this->__(
                        'An email address for Help Desk to fetch messages from.'
                        . ' This address must NOT be used by any other person or system!'
                    ),
                'class'    => 'validate-email'
            )
        );
        $fieldset->addField(
            'gateway_host', 'text',
            array(
                'label'    => $this->__('Gateway Host'),
                'name'     => 'gateway[host]',
                'required' => true,
            )
        );
        $fieldset->addField(
            'gateway_login', 'text',
            array(
                'label'    => $this->__('Login'),
                'name'     => 'gateway[login]',
                'required' => true,
            )
        );
        $fieldset->addField(
            'gateway_password', 'password',
            array(
                'label'    => $this->__('Password'),
                'name'     => 'gateway[password]',
                'required' => true,
            )
        );
        $fieldset->addField(
            'gateway_port', 'text',
            array(
                'label'    => $this->__('Port'),
                'name'     => 'gateway[port]',
                'required' => false,
                'note'     => $this->__(
                        '110 for POP3, 995 for POP3-SSL, 143 for IMAP-TLS and 993 for IMAP-SSL by default'
                    )
            )
        );
        $fieldset->addField(
            'gateway_secure_type', 'select',
            array(
                'label'  => $this->__('Use SSL/TLS'),
                'name'   => 'gateway[secure_type]',
                'values' => AW_Helpdesk3_Model_Source_Gateway_Secure::toOptionArray()
            )
        );

        $fieldset->addField(
            'gateway_delete_emails', 'select',
            array(
                'label'  => $this->__('Delete Emails From Host'),
                'name'   => 'gateway[delete_emails]',
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            )
        );

        $fieldset->addField(
            'gateway_is_allow_attachment', 'select',
            array(
                'label'  => $this->__('Allow file attachments'),
                'name'   => 'gateway[is_allow_attachment]',
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            )
        );

        $urlToTestConnection = $this->getUrl('helpdesk_admin/adminhtml_department/testConnection');
        $defaultErrorMsg = $this->__('Ooops, something wrong');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id' => 'gateway_test_connection',
                    'label' => $this->__('Test Connection'),
                    'type' => 'button',
                    'onclick' => "awHDU3GatewayTestConnection.run('{$urlToTestConnection}', '{$defaultErrorMsg}')"
                )
            )
        ;
        $fieldset->addField(
            'gateway_test_connection', 'label',
            array(
                'after_element_html' => $button->toHtml()
            )
        );

        $gatewayData = Mage::registry('current_department')->getData('gateway');
        if (null === $gatewayData) {
            $gatewayData = Mage::registry('current_department')->getGateway()->getData();
        }
        $_formData = array();
        foreach ($gatewayData as $key => $value) {
            $_formData['gateway_' . $key] = $value;
        }
        $form->setValues($_formData);
        $this->setForm($form);

        $dependFieldIds = array(
            "gateway_title", "gateway_protocol", "gateway_email", "gateway_host",
            "gateway_login", "gateway_password", "gateway_port", "gateway_secure_type", "gateway_delete_emails",
            "gateway_is_allow_attachment", "gateway_test_connection"
        );
        $masterDependFieldId = "gateway_is_active";
        $dependWidgetBlock = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependWidgetBlock->addFieldMap($masterDependFieldId, $masterDependFieldId);
        foreach ($dependFieldIds as $id) {
            $dependWidgetBlock->addFieldMap($id, $id);
            $dependWidgetBlock->addFieldDependence($id, $masterDependFieldId, '1');
        }
        $this->setChild('form_after', $dependWidgetBlock);
    }
}