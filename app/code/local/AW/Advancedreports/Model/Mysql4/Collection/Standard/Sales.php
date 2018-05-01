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


class AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
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

    /**
     * Add order columns
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addSumColumns()
    {
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()->columns(
            array(
                 'orders'   => "COUNT({$orderTableAlias}.entity_id)", # Just because it's unique
                 'subtotal' => "SUM({$orderTableAlias}.base_subtotal)",
                 'tax'      => "SUM({$orderTableAlias}.base_tax_amount)",
                 'discount' => "SUM({$orderTableAlias}.base_discount_amount)",
                 'shipping' => "SUM({$orderTableAlias}.base_shipping_amount)",
                 'total'    => "SUM({$orderTableAlias}.base_grand_total)",
                 'invoiced' => "SUM({$orderTableAlias}.base_total_invoiced)",
                 'refunded' => "SUM({$orderTableAlias}.base_total_refunded)",
                 'int_1'    => "ROUND(1)",
            )
        );
        return $this;
    }

    public function addItems()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()
            ->join(
                array('item' => $itemTable),
                "{$orderTableAlias}.entity_id = item.order_id AND item.parent_item_id IS NULL",
                array(
                    'items_count' => 'SUM(item.qty_ordered)',
                )
            );
        return $this;
    }

    /**
     * Group by Entity_Id
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addGroupByEntityId()
    {
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()->group("{$orderTableAlias}.entity_id");
        return $this;
    }

    /**
     * Group by INT_1
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addGroupByIntOne()
    {
        $this->getSelect()->group('int_1');
        return $this;
    }
}
