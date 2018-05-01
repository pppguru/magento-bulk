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


class AW_Advancedreports_Model_Mysql4_Collection_Bestsellers
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    /**
     * Not simple product types
     *
     * @var array
     */
    protected $_notSimple = array('configurable', 'bundle');

    /**
     * Retrieves helper
     *
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    public function setDateFilter($from, $to)
    {
        $filterField = $this->_helper()->confOrderDateFilter();
        $this->getSelect()
            ->where("orders.{$filterField} >= ?", $from)
            ->where("orders.{$filterField} <= ?", $to)
        ;
        return $this;
    }

    protected function _get1400ProcessStates()
    {
        $states = explode(",", $this->_helper()->confProcessOrders());
        $isFirst = true;
        $filter = "";
        foreach ($states as $state) {
            if (!$isFirst) {
                $filter .= " OR ";
            }
            $filter .= "orders.status = '" . $state . "'";
            $isFirst = false;
        }
        return "(" . $filter . ")";
    }

    public function setState()
    {
        if ($this->_helper()->checkSalesVersion('0.9.56')) {
            $this->getSelect()
                ->where($this->_get1400ProcessStates());
        } else {
            $entityValues = $this->getTable('sales_order_varchar');
            $entityAtribute = $this->getTable('eav_attribute');
            $this->getSelect()
                ->join(array('attr' => $entityAtribute), "attr.attribute_code = 'status'", array())
                ->join(
                    array('val' => $entityValues),
                    "attr.attribute_id = val.attribute_id AND " . $this->_helper()->getSql()->getProcessStates(),
                    array()
                )
                ->where('orders.entity_id = val.entity_id');
        }
        return $this;
    }

    public function setStoreFilter($storeIds = array())
    {
        $this->getSelect()->where("orders.store_id in ('" . implode("','", $storeIds) . "')");
        return $this;
    }

    public function addOrderItems(
        $limit = 10, $skuType = AW_Advancedreports_Model_System_Config_Source_Skutype::SKUTYPE_SIMPLE
    )
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $notSimple = "'" . implode("','", $this->_notSimple) . "'";

        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }

        if ($skuType == AW_Advancedreports_Model_System_Config_Source_Skutype::SKUTYPE_SIMPLE) {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "(item.product_id = e.entity_id AND ((item.parent_item_id IS NULL "
                    . "AND item.product_type NOT IN ({$notSimple})) OR (item.parent_item_id IS NOT NULL "
                    . "AND item.product_type NOT IN ({$notSimple}))))",
                    array(
                        'product_id' => 'product_id',
                        'sum_qty'    => 'SUM(item.qty_ordered)',
                        'sum_total'  => 'SUM(IF(item.base_row_total>0,item.base_row_total, item2.base_row_total))',
                        'name'       => 'name',
                        'sku'        => 'sku',
                    )
                )
                ->joinLeft(
                    array('item2' => $itemTable),
                    "(item.parent_item_id = item2.item_id AND item2.product_type IN ('configurable','bundle'))", array()
                );
        } else {
            $productTable = $this->_helper()->getSql()->getTable('catalog_product_entity');
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "(item.product_id = e.entity_id AND item.parent_item_id IS NULL)",
                    array(
                        'product_id' => 'product_id',
                        'sum_qty'    => 'SUM(item.qty_ordered)',
                        'sum_total'  => 'SUM(item.base_row_total)',
                        'name'       => 'name',
                        'sku'        => 'realP.sku',
                    )
                )
                ->joinLeft(
                    array('realP' => $productTable),
                    "item.product_id = realP.entity_id",
                    array()
                )
            ;
        }

        $this->getSelect()
            ->join(array('orders' => $orderTable), "orders.entity_id = item.order_id", array())
            ->group('e.entity_id')
            ->limit($limit)
        ;
        return $this;
    }

    /**
     * Set up order by total
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Bestsellers
     */
    public function orderByTotal()
    {
        $this->getSelect()
            ->order('sum_total DESC');
        return $this;
    }

    /**
     * Set up order by quantitty
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Bestsellers
     */
    public function orderByQty()
    {
        $this->getSelect()
            ->order('sum_qty DESC')
        ;
        return $this;
    }
}
