<?php


class MDN_AdvancedStock_Model_Observer_Shipment {


    /**
     * Prevent to create a shipment if protection enabled AND qty are not reserved for order
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function sales_order_shipment_save_before(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('healthyerp/erp/enabled') != 1)
            return;

        if (!Mage::getStoreConfig('advancedstock/general/prevent_non_reserved_shipment'))
            return;

        $shipment = $observer->getEvent()->getshipment();
        if ($shipment->getOrigData('entity_id'))
            return;

        foreach ($shipment->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            $productStock = mage::getModel('cataloginventory/stock_item')->load($item->getproduct_id(), 'product_id');
            if ((!$productStock->getId()) || (!$productStock->ManageStock()))
                continue;

            //prevent to have wrong stock movement from magento native ship button (or other button)
            //that are is not from ERP order preparation screen (which by nature prevent this)
            $reservedQty = $orderItem->getreserved_qty();
            if(!$reservedQty)
                $reservedQty = 0;
            $qtyRequestedToBeShipped = $item->getqty();

            if ($reservedQty < 1 || $reservedQty < $qtyRequestedToBeShipped) {
                $errorMessage = mage::helper('AdvancedStock')->__("Cannot ship this order because the product %s has %s reserved quantity and shipment requested quantity is %s", $orderItem->getname(), $reservedQty, $qtyRequestedToBeShipped);
                Mage::getSingleton('adminhtml/session')->addError($errorMessage);
                throw new Exception($errorMessage);
            }
        }

    }



    /**
     * When shipment is saved, create stock movements
     *
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_shipment_save_after(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('healthyerp/erp/enabled') != 1)
            return;

        $shipment = $observer->getEvent()->getshipment();
        if ($shipment->getOrigData('entity_id'))
            return;

        $order = $shipment->getOrder();
        foreach ($shipment->getAllItems() as $item) {
            try {

                $orderItem = $item->getOrderItem();
                $qty = Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->getRealShippedQtyForShipmentItem($item);
                $orderPreparationWarehouse = $orderItem->getPreparationWarehouse();

                //if order preparation is empty,
                if (!$orderPreparationWarehouse->getId()) {
                    $preparationWarehouseId = mage::helper('AdvancedStock/Router')->getWarehouseForOrderItem($orderItem, $order);
                    $orderPreparationWarehouse = Mage::getModel('AdvancedStock/Warehouse')->load($preparationWarehouseId);
                }

                //check if product manages stock
                $productStock = mage::getModel('cataloginventory/stock_item')->load($item->getproduct_id(), 'product_id');
                if ((!$productStock->getId()) || (!$productStock->ManageStock()))
                    continue;

                if ($orderPreparationWarehouse) {
                    $this->createStockMovement($item, $qty, $orderPreparationWarehouse, $order);
                } else
                    throw new Exception(mage::helper('AdvancedStock')->__('Cant find warehouse for orderitem #' . $orderItem->getId()));

                //reset reserved qty
                $productId = $item->getproduct_id();
                $oldReservedQty = $orderItem->getreserved_qty();
                $newReservedQty = $oldReservedQty - $qty;
                if ($newReservedQty < 0)
                    $newReservedQty = 0;

                $orderItem->getErpOrderItem()->setreserved_qty($newReservedQty)->save();

                $this->processProductUpdates($productId, $productStock, $shipment);

            } catch (Exception $ex) {
                mage::log($ex->getMessage(), null, 'erp_create_shipment.log');
            }
        }

        //prevent to execute this function several times
        $shipment->setOrigData('entity_id', $shipment->getId());
    }

    /**
     * Launch updates for product stock
     *
     * @param $productId
     * @param $productStock
     * @param $shipment
     */
    protected function processProductUpdates($productId, $productStock, $shipment)
    {
        mage::helper('AdvancedStock/Product_Reservation')->storeReservedQtyForStock($productStock, $productId);
        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($productId, 'from shipment #' . $shipment->getincrement_id());
        mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' (after shipment)',
            'SalesOrderPlanning/ProductAvailabilityStatus',
            'RefreshForOneProduct',
            $productId,
            null,
            false
        );

    }

    /**
     * Create stock movement for the shipment
     *
     * @param $item
     * @param $orderPreparationWarehouse
     * @param $order
     */
    protected function createStockMovement($item, $qty, $orderPreparationWarehouse, $order)
    {
        $additionalDatas = array('sm_ui' => $item->getId(), 'sm_type' => 'order');
        mage::getModel('AdvancedStock/StockMovement')->createStockMovement($item->getproduct_id(),
            $orderPreparationWarehouse->getId(),
            null,
            $qty,
            mage::helper('AdvancedStock')->__('Shipment for order #') . $order->getincrement_id(),
            $additionalDatas);

    }


}
