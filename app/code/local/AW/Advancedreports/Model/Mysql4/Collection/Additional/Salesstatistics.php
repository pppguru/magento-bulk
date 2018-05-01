<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_ARUnits_Salesstatistics
 * @copyright  Copyright (c) 2009-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

class AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesstatistics extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Reinitialize select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Sales
     */
    public function reInitItemSelect()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        $this->getSelect()->reset();
        $this->getSelect()->from(array($this->_getSalesCollectionTableAlias() => $orderTable), array(
            'grouper' => new Zend_Db_Expr('ROUND(1)'),
        ));

        $this->getSelect()->group('grouper');
        return $this;
    }

    public function reInitOrderSelect()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        $tableAlias = $this->_getSalesCollectionTableAlias();

        $this->getSelect()->reset();
        $this->getSelect()->from(array($tableAlias => $orderTable), array(
            'orders_count' => "COUNT({$tableAlias}.entity_id)", # Just because it's unique
            'base_subtotal' => "SUM({$tableAlias}.base_subtotal)",
            'base_tax_amount' => "SUM({$tableAlias}.base_tax_amount)",
            'base_discount_amount' => "SUM({$tableAlias}.base_discount_amount)",
            'base_grand_total' => "SUM({$tableAlias}.base_grand_total)",
            'base_total_invoiced' => "SUM({$tableAlias}.base_total_invoiced)",
            'base_total_refunded' => "SUM({$tableAlias}.base_total_refunded)",
            'grouper' => new Zend_Db_Expr('ROUND(1)'),
        ));

        $this->getSelect()->group('grouper');

        return $this;
    }

    /**
     * Add items
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesstatistics
     */
    public function addItems()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $tableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()->join(array(
            'item' => $itemTable), "{$tableAlias}.entity_id = item.order_id AND item.parent_item_id IS NULL",
            array(
                'items_count' => 'SUM(item.qty_ordered)',
                'items_invoiced' => 'SUM(item.qty_invoiced)'
            )
        );
        return $this;
    }
}
