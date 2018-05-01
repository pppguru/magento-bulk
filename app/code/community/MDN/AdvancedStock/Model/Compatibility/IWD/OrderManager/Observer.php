<?php

/**
 * Class MDN_AdvancedStock_Model_Compatibility_IWD_OrderManager_Observer
 *
 * Enabel compatibility of ERP with IWD Order Magnager
 *
 */
class MDN_AdvancedStock_Model_Compatibility_IWD_OrderManager_Observer {


    /**
     *  For IWD Order manager since 1.8.4.0 version
     *  from app/code/local/IWD/OrderManager/Model/Order/Edit.php
     *  line 427
     *  Mage::dispatchEvent('iwd_sales_order_item_updated', array('order_item' => $orderItem));
     *
     * @param Varien_Event_Observer $observer
     */
    public function item_updated_from_order(Varien_Event_Observer $observer){
        $orderItem = $observer->getEvent()->getorder_item();
        if($orderItem->getId()>0)
            $this->erpUpdate($orderItem);
    }

    /**
     * For IWD Order manager since 1.8.4.0 version
     * from app/code/local/IWD/OrderManager/Model/Order/Edit.php
     * line 879
     * Mage::dispatchEvent('iwd_sales_order_item_added', array('order_item' => $orderItem));
     *
     * @param Varien_Event_Observer $observer
     */
    public function item_added_from_order(Varien_Event_Observer $observer){
        $orderItem = $observer->getEvent()->getorder_item();
        if($orderItem->getId()>0)
            $this->erpReserve($orderItem);
    }

    /**
     * For IWD Order manager since 1.8.4.0 version
     * from app/code/local/IWD/OrderManager/Model/Order/Edit.php
     * line 953
     * 
     *     Mage::dispatchEvent('iwd_sales_order_item_removed', array('order_item' => $orderItem));
     * @param Varien_Event_Observer $observer
     */
    public function item_deleted_from_order(Varien_Event_Observer $observer){
        $orderItem = $observer->getEvent()->getorder_item();
        if($orderItem->getId()>0)
            $this->erpUnReserve($orderItem);
    }


    public function erpReserve($item)
    {
        $order = Mage::getModel('sales/order')->load($item->getorder_id());

        $debug = '';
        //Affect order item to warehouse
        try{
            //get preparation warehouse
            $preparationWarehouseId = mage::helper('AdvancedStock/Router')->getWarehouseForOrderItem($item, $order);
            if (!$preparationWarehouseId){
                $preparationWarehouseId = 1;
            }

            $debug .= 'Affect warehouse #'.$preparationWarehouseId;

            Mage::helper('AdvancedStock/Router')->affectWarehouseToOrderItem(array('order_item_id' => $item->getId(), 'warehouse_id' => $preparationWarehouseId));
        }
        catch(Exception $ex){
            $productId = $item->getproduct_id();
            $debug .= '<p>Error updating stocks for PID='.$productId.' order item #' . $item->getId() . ' : ' . $ex->getMessage() . '</p>';
        }

        Mage::log($debug, null, 'erp_new_orders_consideration.log');
    }

    protected function erpUnReserve($item){

        //$order_item sales_flat_order_item
        $order = Mage::getModel('sales/order')->load($item->getorder_id());

        //unreserve product
        mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);

        //plan product stocks update
        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), 'from order item is deleted with IWD event');       

    }

    protected function erpUpdate($item){

        //$order_item sales_flat_order_item
        $order = Mage::getModel('sales/order')->load($item->getorder_id());

        //unreserve product
        mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);

        //$this->erpReserve($order, $item);
        mage::helper('AdvancedStock/Product_Reservation')->reserveOrderProduct($order, $item);

        //plan product stocks update
        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), 'from order item is updated with IWD event');


        $this->erpUpdatePotentialSubItem($item,$order);

    }

    protected function erpUpdatePotentialSubItem($item,$order){
        $parentId = $item->getitem_id();
        if($item->getproduct_type() != 'simple'){
            foreach ($order->getItemsCollection() as $subItem) {
                if($subItem->getparent_item_id() == $parentId){
                    mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($subItem->getproduct_id(), 'from order sub item is updated with IWD event');
                }
            }
        }
    }
}