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


class AW_Marketsuite_Model_Rule_Condition_Customer_Address extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_customer_address')->setValue(null);
    }

    public function getNewChildSelectOptions()
    {
        $conditions = Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|city',
                     'label' => Mage::helper('salesrule')->__('City'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|region_id',
                     'label' => Mage::helper('salesrule')->__('State (Dropdown)'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|region',
                     'label' => Mage::helper('salesrule')->__('State (Text Field)'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|country_id',
                     'label' => Mage::helper('salesrule')->__('Country'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|telephone',
                     'label' => Mage::helper('salesrule')->__('Telephone'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|postcode',
                     'label' => Mage::helper('salesrule')->__('Zip'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_customer_address_conditions|company',
                     'label' => Mage::helper('salesrule')->__('Company'),
                 ),
            )
        );
        return $conditions;
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(
            array(
                 'billing'  => $hlp->__('Billing'),
                 'shipping' => $hlp->__('Shipping'),
            )
        );
        return $this;
    }

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            array(
                 '==' => Mage::helper('rule')->__('matches'),
                 '!=' => Mage::helper('rule')->__('does not match'),
            )
        );
        return $this;
    }

    public function loadValueOptions()
    {
        $this->setValueOption(
            array(
                 1 => 'matches',
                 0 => 'does not match',
            )
        );
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('salesrule')->__(
                "Customer %s address %s %s",
                $this->getAttributeElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    protected function getAddressByCustomer($customer)
    {
        if ($this->getAttribute() == 'billing') {
            return $customer->getDefaultBillingAddress();
        }
        if ($this->getAttribute() == 'shipping') {
            return $customer->getDefaultShippingAddress();
        }
        return null;
    }

    protected function getAddressByOrder($order)
    {
        if ($this->getAttribute() == 'billing') {
            return $order->getBillingAddress();
        }
        if ($this->getAttribute() == 'shipping') {
            return $order->getShippingAddress();
        }
        return null;
    }

    public function validate(Varien_Object $object)
    {
        $address = null;

        if ($object instanceof Mage_Customer_Model_Customer) {
            $address = $this->getAddressByCustomer($object);

        } elseif ($object instanceof Mage_Sales_Model_Order) {
            $address = $this->getAddressByOrder($object);
        }

        if ($address === false || is_null($address)) {
            return false;
        }

        return parent::validate($address);
    }

    public function getQuery($query)
    {
        foreach ($this->getConditions() as $cond) {
            $query = $cond->getQuery($query);
        }
        return $query;
    }
}