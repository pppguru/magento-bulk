<?php

class MDN_AdvancedStock_Model_Sales_Order_Tool {

    /**
     * Return preparation warehouses depending of order item
     */
    public function getPreparationWarehouses($order)
    {
        $warehouseIds = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getpreparation_warehouse())
				if(!in_array($item->getpreparation_warehouse(),$warehouseIds))
					$warehouseIds[] = (int)$item->getpreparation_warehouse();			
        }
	

        $collection = mage::getModel('AdvancedStock/Warehouse')
            ->getCollection()
            ->addFieldToFilter('stock_id', array('in' => $warehouseIds));
			
        return $collection;
    }

    /**
     * Check if an order is in process in order preparation
     *
     * @return bool
     */
    public function isBeingDispatched($order)
    {
        $isBeingPrepared = Mage::getModel('Orderpreparation/ordertoprepare')->getCollection()->addFieldToFilter('order_id', $order->getId())->getFirstItem();
        if (!$isBeingPrepared->getId())
            return false;
        else
        {
            $user = Mage::getModel('admin/user')->load($isBeingPrepared->getuser());
            return $user->getusername();
        }
    }

    /**
     * Return order date (depending of magento version)
     *
     * @return unknown
     */
    public function getOrderPlaceDate($order)
    {
        $value = $order->getCreatedAtStoreDate();
        if ($value == '')
            $value = $order->getcreated_at();
        return $value;
    }

    /**
     * Check if the reservation is enabled for one order
     *
     * @return bool
     */
    public function productReservationAllowed($order)
    {

        $reservationAllowed = true;
        if (mage::getStoreConfig('advancedstock/valid_orders/do_not_consider_invalid_orders_for_stocks')) {
            if (!mage::helper('AdvancedStock/Sales_ValidOrders')->orderIsValid($order)) {
                $reservationAllowed = false;
            }
        }
        return $reservationAllowed;
    }

    /**
     * Return true if all products are reserved
     *
     */
    public function allProductsAreReserved($order)
    {
        foreach ($order->getItemsCollection() as $item) {
            $product = mage::getModel('catalog/product')->load($item->getproduct_id());
            $manageStock = true;
            if ($product->getId())
                $manageStock = $product->getStockItem()->getManageStock();
            if ($manageStock) {
                $remaining_qty = $item->getRemainToShipQty() - $item->getreserved_qty();
                if ($remaining_qty > 0)
                    return false;
            }
        }
        return true;
    }

    /**
     * Return true if an order is completely shipped
     *
     */
    public function IsCompletelyShipped($order)
    {
        foreach ($order->getItemsCollection() as $item) {
            if ($item->getRemainToShipQty() > 0)
                return false;
        }
        return true;
    }

    /**
     * Return true if all product are reserved
     *
     * @return unknown
     */
    public function IsFullStock($order, $warehouseId = null)
    {
        foreach ($order->getItemsCollection() as $item) {
            if (($warehouseId != null) && ($warehouseId != $item->getpreparation_warehouse()))
                continue;

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getproduct_id());
            if ($stockItem) {
                if ($stockItem->getManageStock()) {
                    $remaining_qty = $item->getRemainToShipQty();
                    if (($item->getreserved_qty() < $remaining_qty) && ($remaining_qty > 0)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Return real shipped qty for an item
     * Welcome in magento.....
     *
     * @param unknown_type $item
     */
    public function getRealShippedQtyForShipmentItem($item) {
        //init vars
        $qty = $item->getQty();
        $orderItem = $item->getOrderItem();
        $orderItemParentId = $orderItem->getparent_item_id();

        //define if we have to multiply qty by parent qty
        $mustMultiplyByParentQty = false;
        if ($orderItemParentId > 0) {
            $parentOrderItem = mage::getmodel('sales/order_item')->load($orderItemParentId);
            if ($parentOrderItem->getId()) {
                //if shipped together
                if ((($parentOrderItem->getproduct_type() == 'bundle') && (!$parentOrderItem->isShipSeparately())) || ($parentOrderItem->getproduct_type() == 'configurable')) {
                    $mustMultiplyByParentQty = true;
                    $qty = ($orderItem->getqty_ordered() / $parentOrderItem->getqty_ordered());
                }
            }
        }

        //if multiply by parent qty
        if ($mustMultiplyByParentQty) {
            $parentShipmentItem = null;
            foreach ($item->getShipment()->getAllItems() as $ShipmentItem) {
                if ($ShipmentItem->getorder_item_id() == $orderItemParentId)
                    $parentShipmentItem = $ShipmentItem;
            }
            if ($parentShipmentItem) {
                $qty = $qty * $parentShipmentItem->getQty();
            }
        }

        return $qty;
    }

}
