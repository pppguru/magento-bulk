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


class AW_Marketsuite_Model_Rule_Condition_Order_Salesamount extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_order_salesamount')->setValue(null);
    }

    public function getNewChildSelectOptions()
    {
        $conditions = Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                 array(
                     'value' => 'marketsuite/rule_condition_order_salesamount_conditions|order_status',
                     'label' => Mage::helper('salesrule')->__('Order status'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_order_salesamount_conditions|order_date',
                     'label' => Mage::helper('salesrule')->__('Order date'),
                 ),
                 array(
                     'value' => 'marketsuite/rule_condition_order_salesamount_conditions|order_store_id',
                     'label' => Mage::helper('salesrule')->__('Store'),
                 ),
            )
        );
        return $conditions;
    }

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);

        parent::loadArray($arr, $key);
        return $this;
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(
            array(
                 'total'   => $hlp->__('Total'),
                 'average' => $hlp->__('Average'),
            )
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            array(
                 '==' => Mage::helper('rule')->__('is'),
                 '!=' => Mage::helper('rule')->__('is not'),
                 '>=' => Mage::helper('rule')->__('equals or greater than'),
                 '<=' => Mage::helper('rule')->__('equals or less than'),
                 '>'  => Mage::helper('rule')->__('greater than'),
                 '<'  => Mage::helper('rule')->__('less than'),
            )
        );
        return $this;
    }

    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('salesrule')->__(
                "%s sales amount&nbsp; %s %s with %s of these conditions match:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function validate(Varien_Object $object)
    {
        if ($object instanceof Mage_Sales_Model_Order) {
            if ($this->_validateConditions($object)) {
                $total = $object->getBaseGrandTotal();
                return $this->validateAttribute($total);
            }
            return false;
        }

        if ($object instanceof Mage_Customer_Model_Customer) {
            $customersOrders = Mage::helper('marketsuite/customer')->getOrderCollectionByCustomer($object);
            $orderCount = 0;
            $price = 0;
            foreach ($customersOrders as $order) {
                if ($this->_validateConditions($order)) {
                    $orderCount++;
                    $price += $order->getBaseGrandTotal();
                }
            }
            if ($this->getAttribute() == 'average' && $orderCount != 0) {
                $total = $price / $orderCount;
            } else {
                $total = $price;
            }
            if ($orderCount == 0) {
                return false;
            }
            return $this->validateAttribute($total);
        }

        return false;
    }

    public function getQuery($query)
    {
        foreach ($this->getConditions() as $cond) {
            $query = $cond->getQuery($query);
        }
        return $query;
    }

    protected function _validateConditions(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return true;
        }
        $all = $this->getAggregator() === 'all';
        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validate($object);
            if ($all && !$validated) {
                return false;
            } elseif (!$all && $validated) {
                return true;
            }
        }
        return $all ? true : false;
    }
}