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


class AW_Marketsuite_Model_Rule_Condition_Shoppingcart_Conditions extends Mage_Rule_Model_Condition_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_shoppingcart_conditions')->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(
            array(
                 'base_grand_total' => $hlp->__('Grand total'),
                 'base_subtotal'    => $hlp->__('Subtotal'),
                 'items_count'      => $hlp->__('Number of Different Products'),
                 'items_qty'        => $hlp->__('Total Items Quantity'),
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

    public function getInputType()
    {
        return 'string';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function validate(Varien_Object $object)
    {
        $validateObject = $object;
        if ($validateObject instanceof Mage_Customer_Model_Customer) {
            $validateObject = Mage::helper('marketsuite/customer')->getShoppingCartByCustomer($validateObject);
        }

        if (!$validateObject instanceof Mage_Sales_Model_Quote) {
            return false;
        }
        return parent::validate($validateObject);
    }

    public function validateAttribute($validatedValue)
    {
        $this->setValue(floatval($this->getValueParsed()));
        return parent::validateAttribute(floatval($validatedValue));
    }

    public function getQuery($query)
    {
        return $query;
    }
}