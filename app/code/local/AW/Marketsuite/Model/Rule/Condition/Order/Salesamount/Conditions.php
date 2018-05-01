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


class AW_Marketsuite_Model_Rule_Condition_Order_Salesamount_Conditions extends Mage_Rule_Model_Condition_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_order_salesamount_conditions')->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(
            array(
                 'order_status'   => $hlp->__('Order status'),
                 'order_date'     => $hlp->__('Order date'),
                 'order_store_id' => $hlp->__('Store'),
            )
        );
        return $this;
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
            default:
                return false;
        }
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'order_status':
                return 'select';
            case 'order_date':
                return 'date';
            case 'order_store_id':
                return 'multiselect';
            default:
                return 'string';
        }
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

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'order_status':
                return 'select';
            case 'order_date':
                return 'date';
            case 'order_store_id':
                return 'multiselect';
            default:
                return 'text';
        }
    }

    public function getDefaultOperatorInputByType()
    {
        $inputByType = parent::getDefaultOperatorInputByType();
        $inputByType['multiselect'] = array('()', '!()');
        return $inputByType;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $operatorByType = $this->getOperatorByInputType();
        $operatorByType['multiselect'] = array('()', '!()');
        $this->setOperatorByInputType($operatorByType);
        return $this;
    }

    public function getValueSelectOptions()
    {
        switch ($this->getAttribute()) {
            case 'order_status':
                $options = Mage::helper('marketsuite')->getStatusesArray();
                break;
            case 'order_store_id':
                $options = Mage::helper('marketsuite')->getStoresArray();
                break;
            default:
                $options = array();
        }
        $this->setData('value_select_options', $options);
        return $this->getData('value_select_options');
    }

    public function getQuery($query)
    {
        return $query;
    }

    public function validate(Varien_Object $object)
    {
        switch ($this->getAttribute()) {
            case 'order_status':
                $attributeKey = 'status';
                $value = $object->getData($attributeKey);
                break;
            case 'order_date':
                $attributeKey = 'created_at';
                $value = $object->getData($attributeKey);
                if ($value) {
                    $value = explode(' ', $value);
                    $value = $value[0];
                }
                break;
            case 'order_store_id':
                $attributeKey = 'store_id';
                $value = $object->getData($attributeKey);
                break;
            default:
                $value = $object->getData($this->getAttribute());
        }
        return $this->validateAttribute($value);
    }
}