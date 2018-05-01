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


class AW_Marketsuite_Model_Rule_Condition_Store_List extends Mage_Rule_Model_Condition_Abstract
{
    protected $_productResouce = null;

    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_store_list')->setValue(null);
    }

    public function getValue()
    {
        $value = parent::getValue();
        if ($this->getInputType() === 'multiselect') {
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $value = explode(',', $value[0]);
            }
        }
        return $value;
    }

    public function validate(Varien_Object $object)
    {
        if ($object instanceof Mage_Customer_Model_Customer) {
            $customer = $object;
        } elseif ($object instanceof Mage_Sales_Model_Order) {
            $customer = Mage::getModel('customer/customer')->load($object->getCustomerId());
        }
        return parent::validate($customer);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('marketsuite');
        $this->setAttributeOption(
            array('store_id' => $hlp->__('Store customer registered at '))
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $operatorByType = $this->getOperatorByInputType();
        $operatorByType['multiselect'] = array('()', '!()');
        $this->setOperatorByInputType($operatorByType);
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getValueElementType()
    {
        if ($this->getAttribute() == 'store_id') {
            return 'multiselect';
        }
        return parent::getValueElementType();
    }

    public function getValueSelectOptions()
    {
        if ($this->getAttribute() == 'store_id') {
            return Mage::helper('marketsuite')->getStoresArray(true);
        }
        return parent::getValueSelectOptions();
    }

    public function getDefaultOperatorInputByType()
    {
        $inputByType = parent::getDefaultOperatorInputByType();
        $inputByType['multiselect'] = array('()', '!()');
        return $inputByType;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'store_id':
                return 'multiselect';
        }
        return parent::getInputType();
    }

    public function getQuery($query)
    {
        return $query;
    }
}