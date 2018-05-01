<?php

class MDN_Purchase_Model_Order_Delivery extends Mage_Core_Model_Abstract
{
    
    /**
     * Return deliviers date for one PO
     */
    public function getDeliveriesDate($purchaseOrder)
    {
        $prefix = mage::getModel('Purchase/Constant')->getTablePrefix();
        $sql = 'select distinct sm_date from '.$prefix.'stock_movement where sm_po_num = '.$purchaseOrder->getId();
        $dates = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);
        
        return $dates;
    }
    
    /**
     * Return delivered products for one PO and one date
     * @param type $po
     * @param type $date
     */
    public function getDeliveredProducts($po, $date)
    {
        //get products and quantity from stock movement
        $prefix = mage::getModel('Purchase/Constant')->getTablePrefix();
        $sql = 'select sm_product_id, SUM(sm_qty) qty, sm_target_stock from '.$prefix.'stock_movement where sm_po_num = '.$po->getId().' and sm_date = "'.$date.'" group by sm_product_id';
        $productQuantities = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);

        $products = array();
        foreach($productQuantities as $pq)
        {
            $p = array();

            $p['qty'] = $pq['qty'];
            $p['product'] = Mage::getModel('catalog/product')->load($pq['sm_product_id']);
            $p['location'] = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($pq['sm_product_id'], $pq['sm_target_stock'])->getshelf_location();
            
            $products[] = $p;
        }
        
        return $products;
    }
    
}