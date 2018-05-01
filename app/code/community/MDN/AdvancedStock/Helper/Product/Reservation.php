<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Helper_Product_Reservation extends Mage_Core_Helper_Abstract {

     /**
     * Store reserved qty for one stock
     *
     * @param unknown_type $stock
     */
    public function storeReservedQtyForStock($stock, $productId) {
        $stockHasBeenSaved = 0;
        if ($stock != null) {
            $newReservedQty = $this->getReservedQtyForStock($stock, $productId);

            if ($newReservedQty != $stock->getstock_reserved_qty()) {
                if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
                    $stock->setProcessIndexEvents(false);
                }
                $stock->setstock_reserved_qty($newReservedQty);
                $stock->save();
                $stockHasBeenSaved = 1;
            }
        }
        return $stockHasBeenSaved;
    }

    /**
     * Return reserved qty computed from pending order (skip cache)
     *
     * @param unknown_type $stock
     * @param unknown_type $productId
     */
    public function getReservedQtyForStock($stock, $productId) {
        $value = 0;

        //collect pending orders matching to stock
        $pendingOrdersIds = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);

        //retrieve order items to compute order qty
        $pendingOrderItems = mage::getModel('sales/order_item')
            ->getCollection()
            ->addFieldToFilter('order_id', array('in' => $pendingOrdersIds))
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('preparation_warehouse', $stock->getstock_id());

        foreach ($pendingOrderItems as $orderItem) {
            if ($orderItem->getRemainToShipQty() > 0)
                $value += $orderItem->getreserved_qty();
        }

        return $value;
    }

    /**
     * Reserved one product for one order
     *
     * @param unknown_type $order
     * @param unknown_type $orderItem
     */
    public function reserveOrderProduct($order, &$orderItem) {
        $debug = 'Reserve product #' . $orderItem->getproduct_id() . ' for order #' . $order->getincrement_id();


        //first check if orders fullfill conditions for reservation
        if (!Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->productReservationAllowed($order)) {
            $debug .= 'Reservation is not allowed';
            mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshForOneProduct($orderItem->getproduct_id());
            return false;
        }

        //todo : check if function returns nothing
        $preparationWarehouse = $orderItem->getPreparationWarehouse();

        //init vars
        $alreadyReservedQy = $orderItem->getreserved_qty();
        $remainToShipQty = $orderItem->getRemainToShipQty();
        $qtyToReserve = $remainToShipQty - $alreadyReservedQy;
        $debug .= ', remaintToShip=' . $remainToShipQty . ', qtyToReserve=' . $qtyToReserve . ', warehouse=' . $preparationWarehouse->getId();
        if ($qtyToReserve == 0)
            return true;
        $productId = $orderItem->getproduct_id();
        $reservableQty = $this->getReservableQty($preparationWarehouse, $productId);
        $debug .= ', reservableQty=' . $reservableQty;
        if ($reservableQty < $qtyToReserve)
            $qtyToReserve = $reservableQty;

        //reserve qty if positive
        if ($qtyToReserve > 0) {
            //save reserved qty in order_item            
            $orderItem->getErpOrderItem()->setreserved_qty($orderItem->getreserved_qty() + $qtyToReserve)->save();

            //update reserved qty for stock
            $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $preparationWarehouse->getId());
            $this->storeReservedQtyForStock($stockItem, $productId);

            $debug .= ' ---> Reserve ' . $qtyToReserve . ' units';
        }

        return $debug;
    }

    /**
     * Release one product for one order
     *
     * @param unknown_type $order
     * @param unknown_type $orderItem
     */
    public function releaseProduct($order, $orderItem) {
        //init vars
        $productId = $orderItem->getproduct_id();
        //$websiteId = $order->getStore()->getwebsite_id();

        if ($orderItem->getreserved_qty() > 0) {
            //reset reserved qty            
            $orderItem->getErpOrderItem()->setreserved_qty(0)->save();

            //update reserved qty for stock
            $orderPreparationWarehouse = $orderItem->getPreparationWarehouse();
            $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $orderPreparationWarehouse->getId());
            $this->storeReservedQtyForStock($stockItem, $productId);
        }
    }

    /**
     * Return reservable qty for one product and one website
     *
     * @param unknown_type $website
     * @param unknown_type $productId
     */
    public function getReservableQty($warehouse, $productId) {
        //init vars
        $value = 0;

        $stock = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouse->getId());
        if ($stock) {
            $value = $stock->getqty() - $stock->getstock_reserved_qty();
        }

        return $value;
    }





    /**
     * Reserve product for pending orders
     *
     *
     * @param unknown_type $productId
     */
    public function reserveProductForPendingOrders($productId) {
        $stockHasBeenSaved = 0;;
        $debug = '';

        //get an array with available qty per warehouse
        $availableStocks = $this->getAvailableQtyArray($productId);

        //get pending orders ids
        $pendingOrderIds = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);

        //collection sales order items
        $salesOrderItems = mage::getModel('sales/order_item')
            ->getCollection()
            ->addFieldToFilter('order_id', array('in' => $pendingOrderIds))
            ->addFieldToFilter('product_id', $productId);
        foreach ($salesOrderItems as $orderItem) {
            //check if warehouse has available qty
            $preparationWarehouse = $orderItem->getpreparation_warehouse();
            if (isset($availableStocks[$preparationWarehouse]) && ($availableStocks[$preparationWarehouse] > 0)) {
                $order = mage::getModel('sales/order')->load($orderItem->getorder_id());
                $debug .= "\n" . $this->reserveOrderProduct($order, $orderItem);
                $availableStocks = $this->getAvailableQtyArray($productId);
            }
        }

        mage::log('reserveProductForPendingOrders for product #' . $productId);
        return $stockHasBeenSaved;;
    }

    /**
     * Return an array with key = stock id, value = available qty
     */
    protected function getAvailableQtyArray($productId) {
        $retour = array();

        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
        foreach ($stocks as $stock) {
            $stockId = $stock->getstock_id();
            $availableQty = $stock->getqty() - $stock->getstock_reserved_qty();
            $retour[$stockId] = $availableQty;
        }

        return $retour;
    }

    /**
     * Fix issue when reserved quantity > qty in stock
     *
     * @param type $productId
     */
    public function FixOverReservation($stockItemId) {
        $debug = 'Fix over reservation for stock item #' . $stockItemId;

        //load
        $stockItem = Mage::getModel('cataloginventory/stock_item')->load($stockItemId);
        $productId = $stockItem->getproduct_id();
        $debug .= ', productid = '.$productId;
        $qtyToRelease = $stockItem->getstock_reserved_qty() - $stockItem->getqty();
        $debug .= ', Qty to release = ' . $qtyToRelease;
        if ($qtyToRelease > 0) {

            //collect pending orders items
            $pendingOrdersIds = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);
            $pendingOrderItems = mage::getModel('sales/order_item')
                ->getCollection()
                ->addFieldToFilter('order_id', array('in' => $pendingOrdersIds))
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('preparation_warehouse', $stockItem->getstock_id())
                ->setOrder('item_id', 'DESC');
            $debug .= ', pending order items count = ' . $pendingOrderItems->getSize();

            //release product from newer order items
            foreach ($pendingOrderItems as $pendingOrderItem) {
                $reservedQty = $pendingOrderItem->getreserved_qty();
                $qtyToReleaseForOrderItem = ($qtyToRelease > $reservedQty ? $reservedQty : $qtyToRelease);
                $newReservedQty = $reservedQty - $qtyToReleaseForOrderItem;
                /*if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
                  $pendingOrderItem->setProcessIndexEvents(false);//to be removed
                }*/
                $pendingOrderItem->setreserved_qty($newReservedQty)->save();

                $debug .= ', release ' . $qtyToRelease . ' for order item #' . $pendingOrderItem->getId();

                $pendingOrderItem->getErpOrderItem()->setreserved_qty($newReservedQty)->save();

                $qtyToRelease -= $qtyToReleaseForOrderItem;
                if ($qtyToRelease <= 0)
                    break;
            }

            //update reserved qty in DB
            $this->storeReservedQtyForStock($stockItem, $stockItem->getproduct_id());
        }

        //store logs
        mage::log($debug, null, 'erp_product_reservation.log');

        return $this;
    }

    public function releaseAllReservation($stockItemId) {
        $debug = 'Fix over reservation for stock item #' . $stockItemId;

        //load
        $stockItem = Mage::getModel('cataloginventory/stock_item')->load($stockItemId);
        $productId = $stockItem->getproduct_id();
        $qtyToRelease = $stockItem->getstock_reserved_qty();
        if ($qtyToRelease > 0) {

            //collect pending orders items
            $pendingOrdersIds = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);
            $pendingOrderItems = mage::getModel('sales/order_item')
                ->getCollection()
                ->addFieldToFilter('order_id', array('in' => $pendingOrdersIds))
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('preparation_warehouse', $stockItem->getstock_id())
                ->setOrder('item_id', 'DESC');

            //release product from newer order items
            foreach ($pendingOrderItems as $pendingOrderItem) {
                $reservedQty = $pendingOrderItem->getreserved_qty();
                $qtyToReleaseForOrderItem = ($qtyToRelease > $reservedQty ? $reservedQty : $qtyToRelease);
                $newReservedQty = $reservedQty - $qtyToReleaseForOrderItem;
                $pendingOrderItem->setreserved_qty($newReservedQty)->save();
                $pendingOrderItem->getErpOrderItem()->setreserved_qty($newReservedQty)->save();

                $qtyToRelease -= $qtyToReleaseForOrderItem;
                if ($qtyToRelease <= 0)
                    break;
            }

            //update reserved qty in DB
            $this->storeReservedQtyForStock($stockItem, $stockItem->getproduct_id());
        }

        //store logs
        mage::log($debug, null, 'erp_product_release_reservation.log');

        return $this;
    }

}
