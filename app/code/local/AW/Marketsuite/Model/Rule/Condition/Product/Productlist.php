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


class AW_Marketsuite_Model_Rule_Condition_Product_Productlist extends Mage_Rule_Model_Condition_Combine
{
    protected $_productResource = null;

    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_product_productlist')->setValue(null);
    }

    public function getNewChildSelectOptions()
    {
        $productAttributes = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();
        foreach ($productAttributes as $attribute) {
            if (!$attribute->isAllowedForRuleCondition() || !$attribute->getIsUsedForPromoRules()) {
                continue;
            }

            if (Mage::helper('marketsuite')->checkUselessProductAttributes($attribute->getAttributeCode())) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array(
                'value' => 'marketsuite/rule_condition_product_productlist_conditions|' . $code,
                'label' => $label,
            );
        }

        /* Add category as attribute to product attributes */
        $conditions[] = array(
            'value' => 'marketsuite/rule_condition_product_productlist_conditions|category',
            'label' => Mage::helper('marketsuite')->__('Category'),
        );
        $conditions = Mage::helper('marketsuite')->sortConditionListByLabel($conditions);
        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
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
        $this->setAttributeOption(array('product' => $hlp->__('Product')));
        return $this;
    }

    public function loadValueOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setValueOption(
            array(
                 'wishlist'     => $hlp->__('Wishlist'),
                 'shoppingcart' => $hlp->__('Shopping cart'),
            )
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            array(
                 '==' => Mage::helper('marketsuite')->__('is'),
                 '!=' => Mage::helper('marketsuite')->__('is not'),
            )
        );
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('salesrule')->__(
                "%s %s in the %s with %s of these conditions match:",
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

    public function getProductResource()
    {
        if (null === $this->_productResource) {
            $this->_productResource = Mage::getResourceModel('catalog/product');
        }
        return $this->_productResource;
    }

    public function validate(Varien_Object $object)
    {
        if (!$object instanceof Mage_Customer_Model_Customer) {
            return false;
        }

        if ($this->getValue() == 'shoppingcart') {
            $quote = Mage::helper('marketsuite/customer')->getShoppingCartByCustomer($object);

            foreach ($quote->getAllItems() as $item) {
                $product = $item->getProduct();

                /* Get ids of categories related to product */
                $product->setCategory($this->getProductResource()->getCategoryIds($product));
                /* category ids */

                if ($this->validateProduct($product)) {
                    return $this->validateAttribute($this->getValue());
                }
            }
            return false;
        }

        if ($this->getValue() == 'wishlist') {
            $wishlist = Mage::helper('marketsuite/customer')->getWishlistByCustomer($object);

            foreach ($wishlist->getItemCollection() as $item) {
                $product = $item->getProduct();

                /* Get ids of categories related to product */
                $product->setCategory($this->getProductResource()->getCategoryIds($product));
                /* category ids */

                if ($this->validateProduct($product)) {
                    return $this->validateAttribute($this->getValue());
                }
            }

            if ($this->getOperator() == '!=') {
                return true;
            }
            return false;
        }

        return false;
    }

    public function validateProduct($product)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all    = $this->getAggregator() === 'all';

        foreach ($this->getConditions() as $condition) {
            $validated = $condition->validate($product);

            if ($all && $validated !== true) {
                return false;
            } elseif (!$all && $validated === true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    public function getQuery($query)
    {
        foreach ($this->getConditions() as $cond) {
            $query = $cond->getQuery($query);
        }
        return $query;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}