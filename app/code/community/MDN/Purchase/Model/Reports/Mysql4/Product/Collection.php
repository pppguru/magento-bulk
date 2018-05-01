<?php

class MDN_Purchase_Model_Reports_Mysql4_Product_Collection extends Mage_Reports_Model_Mysql4_Product_Collection
{
	/*
    public function addOrderedQty($from = '', $to = '')
    {
        $qtyOrderedTableName = $this->getTable('sales/order_item');
        $qtyOrderedFieldName = 'mage_qty_ordered';

        $productIdTableName = $this->getTable('sales/order_item');
        $productIdFieldName = 'product_id';

        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (e.type_id NOT IN (?))', $compositeTypeIds);

        if ($from != '' && $to != '') {
            $dateFilter = " AND `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = "";
        }

        $this->getSelect()->reset()
            ->from(
                array('order_items' => $qtyOrderedTableName),
                array('mage_qty_ordered' => "SUM(order_items.{$qtyOrderedFieldName})"))
            ->joinInner(
                array('order' => $this->getTable('sales/order')),
                'order.entity_id = order_items.order_id'.$dateFilter,
                array())
            ->joinInner(array('e' => $this->getProductEntityTableName()),
                "e.entity_id = order_items.{$productIdFieldName} AND e.entity_type_id = {$this->getProductEntityTypeId()}{$productTypes}")
            ->group('e.entity_id')
            ->having('mage_qty_ordered > 0');

        return $this;
    }
    */
}