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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_CustomerSummary extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array('id' => 'awhdu3-ticket-edit-customersummary-form')
        );
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'awhdu3-ticket-edit-customersummary-fieldset', array('legend' => $this->__('Customer Summary'))
        );
        $fieldset->addField(
            'customer', 'label',
            array(
                'label'              => $this->__('Customer'),
                'value'              => '',
                'after_element_html' => $this->_getCustomerLabel(),
            )
        );

        foreach ($this->_getCustomerInformation() as $label => $value) {
            $fieldset->addField(
                $label, 'label',
                array(
                    'label'              => $label,
                    'value'              => $value,
                )
            );
        }
        $fieldset->addType(
            'customer_note', 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_CustomerSummary_CustomerNote'
        );
        $customerNote = trim($this->_getCustomerNote());
        $fieldset->addField(
            'customer_note', 'customer_note',
            array(
                'label'          => $this->__('Customer Note'),
                'value'          => empty($customerNote) ? $this->__('No data') : $customerNote,
                'original_value' => $customerNote,
            )
        );
        return parent::_prepareForm();
    }

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return string
     */
    protected function _getCustomerLabel()
    {
        $customerEmail = $this->getTicket()->getCustomerEmail();
        $customerName = $this->getTicket()->getCustomerName();
        $customerId = $this->_getCustomerIdByEmail($customerEmail);
        $label = "";
        if (null !== $customerId) {
            $url = $this->getUrl('adminhtml/customer/edit', array('id' => $customerId));
            $label .= "<a href='{$url}' target='_blank'>{$customerName}</a><br/>";
        }
        $label .= "&lt{$customerEmail}&gt";
        return $label;
    }

    /**
     * @return array
     */
    protected function _getCustomerInformation()
    {
        $customerEmail = $this->getTicket()->getCustomerEmail();
        $websiteId = Mage::app()->getStore($this->getTicket()->getStoreId())->getWebsiteId();
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($customerEmail);
        $address = $customer->getDefaultBillingAddress();
        if (!$address) {
            $address = Mage::getModel('customer/address');
            $addresses = $customer->getAddresses();
            if ($addresses && is_array($addresses)) {
                $address = array_pop($addresses);
            }
        }
        $result = array(
            'Country' => $address->getCountryId(),
            'City'    => $address->getCity(),
            'Phone'   => $address->getTelephone(),
            'Gender'  => $this->_getCustomerGenderLabel($customer)
        );
        foreach ($result as $key => $value) {
            if (empty($value)) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    protected function _getCustomerNote()
    {
        /** @var AW_Helpdesk3_Model_Customer_Note $customerNote */
        $customerNote = Mage::getModel('aw_hdu3/customer_note');
        $customerNote->loadByCustomerEmail($this->getTicket()->getCustomerEmail());
        return $customerNote->getNote();
    }

    /**
     * @param string $email
     *
     * @return int|null
     */
    protected function _getCustomerIdByEmail($email)
    {
        $websiteId = Mage::app()->getStore($this->getTicket()->getStoreId())->getWebsiteId();
        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($email);
        return $customer->getId();
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return string
     */
    protected function _getCustomerGenderLabel(Mage_Customer_Model_Customer $customer)
    {
        return Mage::getResourceSingleton('customer/customer')
            ->getAttribute('gender')
            ->getSource()->getOptionText($customer->getGender())
        ;
    }
}