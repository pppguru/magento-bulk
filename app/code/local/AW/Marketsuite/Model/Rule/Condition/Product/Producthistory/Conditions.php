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


class AW_Marketsuite_Model_Rule_Condition_Product_Producthistory_Conditions
    extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_product_producthistory_conditions')->setValue(null);
    }

    public function validate(Varien_Object $object)
    {
        $attributeCode = $this->getAttribute();

        if ($attributeCode == 'category') {
            return $this->validateAttribute($object->getAvailableInCategories());
        }

        $attributeModel = Mage::helper('marketsuite/attribute')->getAttributeByCode($attributeCode);

        if ($attributeModel && $attributeModel->getFrontendInput() == 'multiselect') {
            $value = $object->getData($attributeCode);
            $value = strlen($value) ? explode(',', $value) : array();
            return $this->validateAttribute($value);
        }
        if ($attributeModel && $attributeModel->getFrontendInput() == 'date') {
            $value = $object->getData($attributeCode);
            $value = date('Y-m-d', strtotime($value));
            return $this->validateAttribute($value);
        }

        return parent::validate($object);
    }

    public function getValue()
    {
        $value = parent::getValue();
        if ($this->getInputType() === 'multiselect') {
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $value = explode(',', $value[0]);
            }
        }
        if ($this->getInputType() === 'status') {
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $value = $value[0];
            }
        }
        return $value;
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
                 'price'       => array('==', '!=', '>=', '<='),
                 'category'    => array('()', '!()'),
                 'status'      => array('()', '!()'),
            )
        );
        return $this;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'category':
                return 'category';
            case 'order_status':
                return 'status';
            default:
                $attributeModel = Mage::getModel('catalog/resource_eav_attribute')->loadByCode(
                    Mage_Catalog_Model_Product::ENTITY, $this->getAttribute()
                );
                if ($attributeModel->getId() && ($attributeModel->getData('frontend_input') == 'price')) {
                    return 'price';
                }
                return parent::getInputType();
        }
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'order_status':
                return 'multiselect';
            default:
                return 'text';
        }
    }

    public function getValueSelectOptions()
    {
        switch ($this->getAttribute()) {
            case 'order_status':
                $options = Mage::helper('marketsuite')->getStatusesArray();
                break;
            default:
                $options = array();
        }
        $this->setData('value_select_options', $options);

        return $this->getData('value_select_options');
    }

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $options = $this->getAttributeOption();
        $options['category'] = Mage::helper('marketsuite')->__('Category');
        $options['order_status'] = Mage::helper('salesrule')->__('Order status');
        $this->setAttributeOption($options);
        return $this;
    }

    public function getQuery($query)
    {
        return $query;
    }

    /**
     * Collect validated attributes
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
     * @return AW_Marketsuite_Model_Rule_Condition_Product_Producthistory_Conditions
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if ('category' != $attribute && 'order_status' != $attribute) {
            parent::collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function getValueElementChooserUrl()
    {
        switch ($this->getAttribute()) {
            case 'category':
            $url = 'adminhtml/promo_widget/chooser/attribute/category_ids';
            if ($this->getJsFormObject()) {
                $url .= '/form/' . $this->getJsFormObject();
            }
            return Mage::helper('adminhtml')->getUrl($url);
            break;
        }
        return parent::getValueElementChooserUrl();
    }

    public function getValueAfterElementHtml()
    {
        switch ($this->getAttribute()) {
           case 'category':
            $image = Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif');
            return '<a href="javascript:void(0)" class="rule-chooser-trigger">'
                . '<img src="' . $image . '" alt="" class="v-middle rule-chooser-trigger" title="'
                . Mage::helper('rule')->__('Open Chooser') . '" /></a>';
        }
        return parent::getValueAfterElementHtml();
    }

    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'category':
            return true;
        }
        return parent::getExplicitApply();
    }

    public function getSqlCondition($columnName)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY,
            $this->getAttribute()
        );
        $_quote = '"';
        if ($attribute->getBackendType() == 'decimal') {
            $_quote = '';
        }
        $value = addslashes($this->getValue());
        switch ($this->getOperator()) {
            case '=='  :
                $_sqlOperator = $columnName . ' = ' . $_quote . $value . $_quote;
                break;
            case '{}'  :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = $columnName . ' LIKE("%' . trim($value[0]) . '%")';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' OR ' . $columnName . ' LIKE("%' . trim($_conditionValue) . '%")';
                }
                break;
            case '!{}' :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = $columnName . ' NOT LIKE("%' . trim($value[0]) . '%")';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' AND ' . $columnName . ' NOT LIKE("%' . trim($_conditionValue) . '%")';
                }
                break;
            case '()'  :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = 'FIND_IN_SET("' . trim($value[0]) . '",' . $columnName . ')';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' OR FIND_IN_SET(' . $_quote . trim($_conditionValue) . $_quote . ',' . $columnName . ')';
                }
                break;
            case '!()' :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = '!FIND_IN_SET(' . $_quote . trim($value[0]) .  $_quote . ',' . $columnName . ')';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' AND !FIND_IN_SET(' . $_quote . trim($_conditionValue) . $_quote . ',' . $columnName . ')';
                }
                break;
        }
        if (!isset($_sqlOperator)) {
            $_sqlOperator = $columnName . $this->getOperator() . $_quote . $value . $_quote;
        }
        return $_sqlOperator;
    }
}