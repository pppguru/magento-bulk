<?php


class MDN_Scanner_Adminhtml_Scanner_PurchaseOrderController extends Mage_Adminhtml_Controller_Action {

    /**
     * Select supplier
     *
     */
    public function SelectSupplierAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Select purchase order
     *
     */
    public function SelectPurchaseOrderAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Select products for delivery
     *
     */
    public function SelectProductDeliveryAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create delivery
     *
     */
    public function CreateDeliveryAction() {
        //load datas
        $poId = $this->getRequest()->getPost('po_num');
        $purchaseOrder = mage::getModel('Purchase/Order')->load($poId);
        $purchaseOrderUpdater = mage::getModel('Purchase/Order_Updater')->init($purchaseOrder);

        $warehouseId = $purchaseOrder->getpo_target_warehouse();
        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($warehouseId);

        foreach ($purchaseOrder->getProducts() as $product) {
            $qty = $this->getRequest()->getPost('product_' . $product->getId());
            if ($qty > 0) {
                //todo : add a setting to enable to select warehouse at user level
                $description = 'Purchase Order #' . $purchaseOrder->getpo_order_id();
                $purchaseOrder->createDelivery($product, $qty, date('Y-m-d'), $description, $warehouseId);

                //store location (if set)
                $location = $this->getRequest()->getPost('location_' . $product->getId());
                if ($location != '')
                    $warehouse->setProductLocation($product->getpop_product_id(), $location);

                //add barcode (if set)
                $barcode = $this->getRequest()->getPost('barcode_' . $product->getId());
                if ($barcode != '') {
                    $productId = $product->getpop_product_id();
                    mage::helper('AdvancedStock/Product_Barcode')->addBarcodeIfNotExists($productId, $barcode);
                }
            }
        }

        //update PO status & progress delivery
        if ($purchaseOrder->isCompletelyDelivered())
            $purchaseOrder->setpo_status(MDN_Purchase_Model_Order::STATUS_COMPLETE);
        $purchaseOrder->computeDeliveryProgress();

        $purchaseOrderUpdater->checkForChangesAndLaunchUpdates($purchaseOrder);

        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/scanner');
    }

}

