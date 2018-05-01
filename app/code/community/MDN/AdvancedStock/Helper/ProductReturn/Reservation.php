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
class MDN_AdvancedStock_Helper_ProductReturn_Reservation extends MDN_ProductReturn_Helper_Reservation {

    /**
     * Reserve product for RMA
     *
     * @param unknown_type $productId
     * @param unknown_type $qty
     * @param unknown_type $rma
     */
    public function reserveProduct($rma, $productId, $qty) {
        if ($qty == 0)
            throw new Exception($this->__('Quantity to reserve must be greater than 0'));

        //load datas
        $product = mage::getModel('catalog/product')->load($productId);
        $websiteId = $rma->getSalesOrder()->getStore()->getwebsite_id();

        //get preparation warehouse
        $orderItem = $this->getOrderItemFromProductId($rma->getSalesOrder(), $productId);
        $preparationWarehouse = $orderItem->getPreparationWarehouse();

        $rmaReservationWarehouse = mage::helper('AdvancedStock/Warehouse')->getWarehouseForAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentRmaReservation);
        if (!$rmaReservationWarehouse)
            throw new Exception($this->__('Cant find warehouse for rma reservation for website # %s', $websiteId));

        //check if qty is available
        $availableQty = $preparationWarehouse->getAvailableQty($productId);
        if (!($availableQty >= $qty))
            throw new Exception($this->__('Stock level too low for reservation'));

        //create stock movement
        $description = $this->__('Reservation for RMA #%s', $rma->getrma_ref());
        $additionalData = array('sm_type' => 'rma_reservation');
        mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                $preparationWarehouse->getId(),
                $rmaReservationWarehouse->getId(),
                $qty,
                $description,
                $additionalData);
    }

    /**
     * Release product for RMA
     *
     * @param unknown_type $productId
     * @param unknown_type $qty
     * @param unknown_type $rma
     */
    public function releaseProduct($rma, $productId, $qty) {
        //load datas
        $product = mage::getModel('catalog/product')->load($productId);
        $websiteId = $rma->getSalesOrder()->getStore()->getwebsite_id();

        $preparationWarehouse = null;
        foreach($rma->getSalesOrder()->getAllItems() as $orderItem)
        {
            $preparationWarehouse = $orderItem->getPreparationWarehouse();
        }

        $rmaReservationWarehouse = mage::helper('AdvancedStock/Warehouse')->getWarehouseForAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentRmaReservation);
        if (!$rmaReservationWarehouse)
            throw new Exception($this->__('Cant find warehouse for rma reservation for website # %s', $websiteId));

        //check if qty is available
        $availableQty = $rmaReservationWarehouse->getAvailableQty($productId);
        if (!($availableQty >= $qty))
            throw new Exception($this->__('Stock level too low to release reservation for product %s', $product->getName()));

        //create stock movement
        $description = $this->__('Release reservation for RMA #%s', $rma->getrma_ref());
        $additionalData = array('sm_type' => 'rma_reservation');
        mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                $rmaReservationWarehouse->getId(),
                $preparationWarehouse->getId(),
                $qty,
                $description,
                $additionalData);
    }

    /**
     * Affect reserved products to created order
     *
     * @param unknown_type $order
     */
    public function affectProductsToCreatedOrder($rma, $order) {
        //release products
        foreach ($rma->getReservations() as $reservation) {
            //update reserved qty for product in order
            $productId = $reservation->getrr_product_id();
            $orderItem = $this->getOrderItemFromProductId($order, $productId);
            if ($orderItem == null)
                continue;
            $qty = $reservation->getrr_qty();
            if ($qty > $orderItem->getqty_ordered())
                $qty = $orderItem->getqty_ordered();
            
            $orderItem->getErpOrderItem()->setreserved_qty($qty)->save();

            //release product in rma
            $rma->releaseProduct($productId);

            //schedule stocks update for this product
            mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($productId, 'from affectProductsToCreatedOrder');
        }
    }

    protected function getOrderItemFromProductId($order, $productId) {

        //check order item from sales order
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getproduct_id() == $productId)
                return $orderItem;
        }

        //if not found, return default one
        foreach ($order->getAllItems() as $orderItem) {
            return $orderItem;
        }
    }

}