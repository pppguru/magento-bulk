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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Model_Rule_Condition_Customer_Conditions extends Mage_Rule_Model_Condition_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_customer_conditions')->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('marketsuite');
        $attributes = array(
            'group_id'                 => $hlp->__('Customer Group'),
            'dob'                      => $hlp->__('Date of Birth'),
            'billing_address'          => $hlp->__('Billing address'),
            'shipping_address'         => $hlp->__('Shipping address'),
            'email'                    => $hlp->__('Email'),
            'gender'                   => $hlp->__('Gender'),
            'firstname'                => $hlp->__('First name'),
            'lastname'                 => $hlp->__('Last name'),
            'newslettersubscription'   => $hlp->__('Newsletter subscription'),
            'annewslettersubscription' => $hlp->__('Advanced newsletter subscription'),
        );
        foreach (Mage::helper('marketsuite')->getCustomerAttributes() as $attribute) {
            $attributes['aw_ca_'.$attribute->getData('code')] = $attribute->getLabel();
        }
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'newslettersubscription':
            case 'gender':
                return 'select';
            case 'dob':
                return 'date';
            case 'email':
                return 'email';
            case 'annewslettersubscription':
            case 'group_id':
                return 'multiselect';
            default:
                foreach (Mage::helper('marketsuite')->getCustomerAttributes() as $attribute) {
                    if ('aw_ca_' . $attribute->getData('code') == $this->getAttribute()) {
                        switch ($attribute->getData('type')) {
                            case 'text':
                            case 'textarea':
                                return 'string';
                            case 'yesno':
                                return 'select';
                            case 'date':
                                return 'date';
                            case 'dropdown':
                            case 'multipleselect':
                                return 'multiselect';
                        }
                    }
                }
                return 'string';
        }
    }

    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorByInputType(
            array(
                 'select'      => array('==', '!='),
                 'date'        => array('==', '>=', '<='),
                 'email'       => array('==', '!=', '{}', '!{}'),
                 'string'      => array('==', '!=', '{}', '!{}'),
                 'multiselect' => array('()', '!()'),
            )
        );
        return $this;
    }

    public function getValue()
    {
        $value = parent::getValue();
        if ($this->getInputType() === 'multiselect') {
            if (is_array($value)) {
                $value = array_filter($value);
                if (count($value) === 0) {
                    $value = '';
                }
                if (count($value) === 1 && isset($value[0])) {
                    $value = explode(',', $value[0]);
                }
            }
        }
        return $value;
    }

    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getInputType()) {
            case 'date':
                $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                break;
        }
        return $element;
    }

    public function getExplicitApply()
    {
        switch ($this->getInputType()) {
            case 'date':
                return true;
        }
        return false;
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'newslettersubscription':
            case 'gender':
                return 'select';
            case 'dob':
                return 'date';
            case 'annewslettersubscription':
            case 'group_id':
                return 'multiselect';
            default:
                foreach (Mage::helper('marketsuite')->getCustomerAttributes() as $attribute) {
                    if ('aw_ca_' . $attribute->getData('code') == $this->getAttribute()) {
                        switch ($attribute->getData('type')) {
                            case 'text':
                            case 'textarea':
                                return 'text';
                            case 'yesno':
                                return 'select';
                            case 'date':
                                return 'date';
                            case 'dropdown':
                            case 'multipleselect':
                                return 'multiselect';
                        }
                    }
                }
                return 'text';
        }
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'annewslettersubscription':
                    $options = Mage::getModel('advancednewsletter/segment')
                        ->getSegmentArray();
                    break;

                case 'newslettersubscription':
                    $options = array(
                        array('value' => '0', 'label' => 'No'),
                        array('value' => '1', 'label' => 'Yes')
                    );
                    break;

                case 'gender':
                    $options = Mage::getModel('marketsuite/source_gender')->toOptionArray();
                    break;

                case 'group_id':
                    $options = Mage::getResourceModel('customer/group_collection')
                        ->addFieldToFilter('customer_group_id', array('gt' => 0))
                        ->load()
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
                    foreach (Mage::helper('marketsuite')->getCustomerAttributes() as $attribute) {
                        if ('aw_ca_' . $attribute->getData('code') == $this->getAttribute()) {
                            switch ($attribute->getData('type')) {
                                case 'yesno':
                                    $options = Mage::helper('marketsuite')->getOptionsForYesnoCustomerAttributeAsArray(false);
                                    break;
                                case 'dropdown':
                                case 'multipleselect':
                                    $options = Mage::helper('marketsuite')->getOptionsForCustomerAttributesAsArray($attribute);
                            }
                        }
                    }
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function asHtml()
    {
        if ($this->getAttribute() == 'annewslettersubscription') {
            $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
            if (!in_array('AW_Advancednewsletter', $modules)) {
                $html = '<a href="http://ecommerce.aheadworks.com/extensions/advanced-newsletter.html">'
                    . 'Advanced Newsletter extension</a> is required for targeted newsletter functionality.'
                ;
                $html .= $this->getRemoveLinkHtml();
                return $html;
            }
        }
        return parent::asHtml();
    }

    public function validate(Varien_Object $object)
    {
        if ($object instanceof Mage_Customer_Model_Customer) {
            $customer = $object;
        } elseif ($object instanceof Mage_Sales_Model_Order) {
            if ($this->getAttribute() == 'email') {
                return $this->validateAttribute($object->getData('customer_email'));
            }
            $customer = Mage::getModel('customer/customer')->load($object->getCustomerId());
        } else {
            return false;
        }

        if ($this->getAttribute() == 'newslettersubscription') {
            Mage::helper('marketsuite/customer')->addNativeNewsletterData($customer);
        }
        if ($this->getAttribute() == 'annewslettersubscription') {
            Mage::helper('marketsuite/customer')->addAdvancedNewsletterData($customer);
        }
        if ($this->getAttribute() == 'dob') {
            $dob = $customer->getData('dob');
            if ($dob) {
                $dob = explode(' ', $dob);
                $customer->setData('dob', $dob[0]);
            }
        }
        foreach (Mage::helper('marketsuite')->getCustomerAttributes() as $attribute) {
            if ('aw_ca_' . $attribute->getData('code') == $this->getAttribute()) {
                $aw_ca = $customer->getData('aw_ca');
                $value = $aw_ca->getData($attribute->getData('code'));
                if (!isset($value)) {
                    $value = $attribute->getData('default_value');
                }
                switch ($attribute->getData('type')) {
                    case 'multipleselect' :
                        $value = explode(',', $value);
                        $customer->setData($this->getAttribute(), $value);
                        break;
                    default :
                        $customer->setData($this->getAttribute(), $value);
                }
            }
        }

        return parent::validate($customer);
    }

    public function validateAttribute($validatedValue)
    {
        if ($this->getValueParsed() == AW_Marketsuite_Model_Source_Gender::NOT_SPECIFIED) {
            if (
                ($validatedValue === null && $this->getOperator() == '==')
                || ($validatedValue !== null && $this->getOperator() == '!=')
            ) {
                return true;
            }
            return false;
        }
        return parent::validateAttribute($validatedValue);
    }

    public function getQuery($query)
    {
        return $query;
    }
}