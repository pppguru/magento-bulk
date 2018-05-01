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


class AW_Marketsuite_Model_Rule_Condition_Product_Producthistory extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_product_producthistory')->setValue(null);
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
                'value' => 'marketsuite/rule_condition_product_producthistory_conditions|' . $code,
                'label' => $label,
            );
        }

        /* Add category as attribute to product attributes */
        $conditions[] = array(
            'value' => 'marketsuite/rule_condition_product_producthistory_conditions|category',
            'label' => Mage::helper('marketsuite')->__('Category')
        );
        /* Add order status as attribute to product attributes */
        $conditions[] = array(
            'value' => 'marketsuite/rule_condition_product_producthistory_conditions|order_status',
            'label' => Mage::helper('salesrule')->__('Order status')
        );
        $conditions = Mage::helper('marketsuite')->sortConditionListByLabel($conditions);
        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(
            array(
                 'viewed'  => $hlp->__('viewed'),
                 'ordered' => $hlp->__('ordered'),
            )
        );
        return $this;
    }

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            array(
                 '==' => Mage::helper('rule')->__('for'),
                 '>'  => Mage::helper('rule')->__('more than'),
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

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('salesrule')->__(
                "If Product was %s %s %s times and matches %s of these conditions:",
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
        if ($object instanceof Mage_Customer_Model_Customer && $object->getId()) {
            if ($this->getAttribute() == 'viewed') {
                $totalViewedCount = 0;
                $productList = Mage::helper('marketsuite/customer')->getProductListViewedByCustomer($object);
                foreach ($productList as $product) {
                    if ($this->validateProduct($product)) {
                        $totalViewedCount += $product->getViewsCount();
                    }
                }
                return $this->validateAttribute($totalViewedCount);
            }
            if ($this->getAttribute() == 'ordered') {
                $totalOrderedCount = 0;
                $customersOrders = Mage::helper('marketsuite/customer')->getOrderCollectionByCustomer($object);
                $orderIds = $customersOrders->getAllIds();
                $orderTotals = $this->_getValidatedTotalOrderedCount($orderIds);
                foreach ($orderTotals as $totalOrderedCount) {
                    if ($this->validateAttribute($totalOrderedCount)) {
                        $totalOrderedCount += $totalOrderedCount;
                    }
                }
                return $this->validateAttribute($totalOrderedCount);
            }
        }
        return false;
    }

    public function validateCustomerCollection(Zend_Db_Select $select)
    {
        $validatedCustomers = array();
        $_customerIds = Mage::helper('marketsuite/customer')->getAllIds($select);
        if ($this->getAttribute() == 'viewed') {
            foreach ($_customerIds as $_customerId) {
                $totalViewedCount = 0;
                $customer = Mage::getModel('customer/customer')->load($_customerId);
                $productList = Mage::helper('marketsuite/customer')->getProductListViewedByCustomer($customer);
                foreach ($productList as $product) {
                    if ($this->validateProduct($product)) {
                        $totalViewedCount += $product->getViewsCount();
                    }
                }
                if ($this->validateAttribute($totalViewedCount)) {
                    array_push($validatedCustomers, $_customerId);
                }
            }
        }
        if ($this->getAttribute() == 'ordered') {
            $customersOrders = Mage::helper('marketsuite/customer')->getOrderCollectionByCustomerIds($_customerIds);
            $_arrayOrderIdCustomerId = $customersOrders->getConnection()->fetchPairs($customersOrders->getSelect());
            $_arrayOrderIdTotal = $this->_getValidatedTotalOrderedCount(array_keys($_arrayOrderIdCustomerId));
            $_validatedArrayOrderIdCustomerId = array_intersect_key($_arrayOrderIdCustomerId, $_arrayOrderIdTotal);

            $customersTotalsArray = array();
            foreach ($_validatedArrayOrderIdCustomerId as $orderId => $customerId) {
                if (!array_key_exists($customerId, $customersTotalsArray)) {
                    $customersTotalsArray[$customerId] = 0;
                }
                $customersTotalsArray[$customerId] += $_arrayOrderIdTotal[$orderId];
            }

            foreach ($customersTotalsArray as $customerId => $orderQtyOrderedTotals) {
                if ($this->validateAttribute($orderQtyOrderedTotals)) {
                    array_push($validatedCustomers, $customerId);
                }
            }
        }
        return $validatedCustomers;
    }

    public function validateOrderCollection(Zend_Db_Select $select)
    {
        if ($this->getAttribute() == 'ordered') {
            $_orderIds = Mage::helper('marketsuite/order')->getAllIds($select);
            $orderTotals = $this->_getValidatedTotalOrderedCount($_orderIds);
            $validatedOrders = array();
            foreach ($orderTotals as $orderId => $totalOrderedCount) {
                if ($this->validateAttribute($totalOrderedCount)) {
                    array_push($validatedOrders, $orderId);
                }
            }
            return $validatedOrders;
        }
        return array();
    }

    protected function _getValidatedTotalOrderedCount(array $orderIds)
    {
        $stringConditions = '';
        $attributeStringConditions = '';
        $_categoryCondition = null;
        $_orderStatusCondition = null;
        $attributeCodes = array();
        $_attributeValueCondition = 'IF('
            . 'IF(cpev.value IS NULL,'
            . 'IF(cpei.value IS NULL,'
            . 'IF(cpet.value IS NULL,'
            . 'IF(cped.value IS NULL, NULL, cped.value), cpet.value), cpei.value), cpev.value) IS NULL,'
            . 'IF(cpev_def.value IS NULL,'
            . 'IF(cpei_def.value IS NULL,'
            . 'IF(cpet_def.value IS NULL,'
            . 'IF(cped_def.value IS NULL, NULL, cped_def.value), cped_def.value), cpei_def.value), cpev_def.value),'
            . 'IF(cpev.value IS NULL,'
            . 'IF(cpei.value IS NULL,'
            . 'IF(cpet.value IS NULL,'
            . 'IF(cped.value IS NULL, NULL, cped.value), cpet.value), cpei.value), cpev.value)'
            .')'
        ;

        foreach ($this->getConditions() as $condition) {
            if (in_array($condition->getAttribute(), array('category'))) {
                $_categoryCondition = $condition;
                continue;
            }

            if (in_array($condition->getAttribute(), array('order_status'))) {
                $_orderStatusCondition = $condition;
                continue;
            }

            if ($condition->getAttribute() == 'sku') {
                if (!empty($stringConditions)) {
                    $stringConditions .= ($this->getAggregator() === 'all') ? ' AND ' : ' OR ';
                }
                $stringConditions .= $condition->getSqlCondition('sfoi.sku');
                continue;
            }

            if (!empty($attributeStringConditions)) {
                $attributeStringConditions .= ' OR ';
            }

            array_push($attributeCodes, $condition->getAttribute());
            $attributeStringConditions .= $condition->getSqlCondition($_attributeValueCondition);
        }
        $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readAdapter->select();
        $select
            ->from(array('sfoi' => $this->_getTableName('sales_flat_order_item')),
                array(
                    'item_id'     => 'sfoi.item_id',
                    'order_id'    => 'sfoi.order_id',
                    'product_id'  => 'sfoi.product_id',
                    'qty_ordered' => 'sfoi.qty_ordered',
                    'store_id'    => 'sfoi.store_id',
                )
            )
            ->joinLeft(array('cpe' => $this->_getTableName('catalog_product_entity')),
                'cpe.entity_id = sfoi.product_id',
                array()
            );

        if (!empty($attributeStringConditions)) {
            $select
                ->joinLeft(array('ea' => $this->_getTableName('eav_attribute')),
                    'ea.entity_type_id = cpe.entity_type_id',
                    array('attribute_code' => 'ea.attribute_code')
                )
                ->joinLeft(array('cpev' => $this->_getTableName('catalog_product_entity_varchar')),
                    'cpev.entity_id = sfoi.product_id AND cpev.attribute_id = ea.attribute_id AND cpev.store_id = sfoi.store_id',
                    array()
                )
                ->joinLeft(array('cpei' => $this->_getTableName('catalog_product_entity_int')),
                    'cpei.entity_id = sfoi.product_id AND cpei.attribute_id = ea.attribute_id AND cpei.store_id = sfoi.store_id',
                    array()
                )
                ->joinLeft(array('cpet' => $this->_getTableName('catalog_product_entity_text')),
                    'cpet.entity_id = sfoi.product_id AND cpet.attribute_id = ea.attribute_id AND cpet.store_id = sfoi.store_id',
                    array()
                )
                ->joinLeft(array('cped' => $this->_getTableName('catalog_product_entity_decimal')),
                    'cped.entity_id = sfoi.product_id AND cped.attribute_id = ea.attribute_id AND cped.store_id = sfoi.store_id',
                    array()
                )
                ->joinLeft(array('cpev_def' => $this->_getTableName('catalog_product_entity_varchar')),
                    'cpev_def.entity_id = sfoi.product_id AND cpev_def.attribute_id = ea.attribute_id AND cpev_def.store_id = 0',
                    array()
                )
                ->joinLeft(array('cpei_def' => $this->_getTableName('catalog_product_entity_int')),
                    'cpei_def.entity_id = sfoi.product_id AND cpei_def.attribute_id = ea.attribute_id AND cpei_def.store_id = 0',
                    array()
                )
                ->joinLeft(array('cpet_def' => $this->_getTableName('catalog_product_entity_text')),
                    'cpet_def.entity_id = sfoi.product_id AND cpet_def.attribute_id = ea.attribute_id AND cpet_def.store_id = 0',
                    array('attribute_value' => $_attributeValueCondition, 'validated' => 'SUM(IF(' . $attributeStringConditions . ',1,0))')
                )
                ->joinLeft(array('cped_def' => $this->_getTableName('catalog_product_entity_decimal')),
                    'cped_def.entity_id = sfoi.product_id AND cped_def.attribute_id = ea.attribute_id AND cped_def.store_id = 0',
                    array()
                )
                ->where($_attributeValueCondition . ' IS NOT NULL')
                ->where('attribute_code IN(?)', $attributeCodes)
            ;
        }
        $select
            ->group('sfoi.item_id')
            ->where('sfoi.order_id IN(?)', $orderIds)
        ;
        if (null !== $_orderStatusCondition && ($this->getAggregator() === 'all' || empty($_stringConditions))) {
            $select
                ->join(array('sfo' => $this->_getTableName('sales_flat_order')), $readAdapter->quoteInto('sfo.entity_id = sfoi.order_id', ''))
                ->having($_orderStatusCondition->getSqlCondition('sfo.status'))
            ;
        }
        if (!empty($stringConditions)) {
            $select->where($stringConditions);
        }
        if ($this->getAggregator() === 'all' && count($attributeCodes) > 0) {
            $select->having('SUM(IF(' . $attributeStringConditions . ',1,0)) = ?', count($attributeCodes));
        }
        $orderTotals = array();
        $result = $readAdapter->fetchAll($select);
        foreach ($result as $row) {
            if (null !== $_categoryCondition && ($this->getAggregator() === 'all' || empty($_stringConditions))) {
                $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
                $select = $readAdapter->select();
                $select
                    ->from(array('ccpi' => $this->_getTableName('catalog_category_product_index')))
                    ->where('product_id = ?', $row['product_id'])
                    ->where('store_id = ?', $row['store_id'])
                    ->having($_categoryCondition->getSqlCondition('GROUP_CONCAT(ccpi.category_id)'))
                ;
                $categoryResult = $readAdapter->fetchAll($select);
                if (count($categoryResult) == 0) {
                    continue;
                }
            }
            if (!array_key_exists($row['order_id'], $orderTotals)) {
                $orderTotals[$row['order_id']] = 0;
            }
            $orderTotals[$row['order_id']] += $row['qty_ordered'];
        }
        return $orderTotals;
    }

    protected function _getTableName($tableName)
    {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
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