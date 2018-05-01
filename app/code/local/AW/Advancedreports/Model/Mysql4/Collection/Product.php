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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Model_Mysql4_Collection_Product extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Reinitialize select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product
     */
    public function reInitSelect()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        $this->getSelect()->reset();
        $this->getSelect()->from(array($this->_getSalesCollectionTableAlias() => $orderTable), array());
        return $this;
    }

    public function setSkusFilter($skus = array())
    {
        if ($filter = $this->_getWhereSkusFilter($skus)) {
            $this->getSelect()->where("({$filter})");
        }
        return $this;
    }

    /**
     * Retrieves SQL filter string
     *
     * @param array $skus
     *
     * @return null|string
     */
    protected function _getWhereSkusFilter($skus = array())
    {
        if (count($skus)) {
            $filter = '';
            $isFirst = true;
            foreach ($skus as $sku) {
                if (!$isFirst) {
                    $filter .= ' OR ';
                }
                $filter .= "item.sku = '{$sku}'";
                $isFirst = false;
            }
            return $filter;
        }
        return null;
    }

    /**
     * Add items
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product
     */
    public function addItems($joinParentItem = false)
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $_joinCondition = "{$orderTableAlias}.entity_id = item.order_id AND item.parent_item_id IS NULL";
        if (true === $joinParentItem) {
            $_joinCondition = "{$orderTableAlias}.entity_id = item.order_id";
        }
        $this->getSelect()
            ->join(
                array('item' => $itemTable),
                $_joinCondition,
                array(
                    'sum_qty'         => 'SUM(item.qty_ordered)',
                    'sum_total'       => 'SUM(item.base_row_total - item.base_discount_amount + item.base_tax_amount)',
                    'name'            => 'name', 'sku' => 'sku',
                    'item_product_id' => 'item.product_id',
                    'product_type'    => 'item.product_type'
                )
            )
            ->group('item.sku')
        ;
        if (true === $joinParentItem) {
            $this->getSelect()
                ->joinLeft(
                    array('item_parent' => $itemTable),
                    "item.parent_item_id = item_parent.item_id",
                    array(
                        'parent_sum_total'  => 'SUM(item_parent.base_row_total - item_parent.base_discount_amount + item_parent.base_tax_amount)',
                        'parent_product_id' => 'item_parent.product_id',
                    )
                )
                ->group('item_parent.sku')
            ;
        }
        return $this;
    }
}
