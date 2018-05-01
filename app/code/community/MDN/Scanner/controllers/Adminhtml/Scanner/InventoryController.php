<?php


class MDN_Scanner_Adminhtml_Scanner_InventoryController extends Mage_Adminhtml_Controller_Action {

    /**
     * Display menu
     *
     */
    public function IndexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Process search
     *
     */
    public function processSearchAction() {
        $this->loadLayout();

        $query = $this->getRequest()->getPost('query');

        $resultBlock = $this->getLayout()->getBlock('scanner_inventory_result');
        $resultBlock->initResult($query);
        if ($resultBlock->hasOnlyOneResult()) {
            $this->_redirect('adminhtml/Scanner_Inventory/ProductInformation', array('product_id' => $resultBlock->getOnlyProduct()->getId()));
        } else {
            $this->renderLayout();
        }
    }

    /**
     * Return product information
     *
     */
    public function ProductInformationAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * add a barcode to product
     *
     */
    public function AddBarcodeAction() {
        //get data
        $productId = $this->getRequest()->getParam('product_id');
        $barcode = $this->getRequest()->getParam('barcode');
        $barcodeHelper = mage::helper('AdvancedStock/Product_Barcode');

        //init vars
        $error = false;
        $message = '';


        //check if barcode exists
        if ($barcodeHelper->barcodeExists($barcode)) {
            $error = true;
            $message = $this->__('Barcode already used');
        } else {
            //add bar code
            $barcodeHelper->addBarcodeIfNotExists($productId, $barcode);
            $message = $this->__('Barcode added');
        }

        //redirect on product page
        $this->_redirect('adminhtml/Scanner_Inventory/ProductInformation', array('product_id' => $productId));
    }

    /**
     * Edit stock
     *
     */
    public function EditStockAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Change product location
     */
    public function ChangeProductLocationAction() {
        //get param
        $stockId = $this->getRequest()->getParam('stock_id');
        $location = $this->getRequest()->getParam('location');

        //change location
        $stock = mage::getModel('cataloginventory/stock_item')->load($stockId);
        $stock->setshelf_location($location)->save();

        //redirect to product sheet
        $productId = $stock->getProductId();
        $this->_redirect('adminhtml/Scanner_Inventory/ProductInformation', array('product_id' => $productId));
    }

    /**
     * Save stock changes
     *
     */
    public function SaveStockQtyAction() {
        //load information
        $stockId = $this->getRequest()->getPost('stock_id');
        $stock = mage::getModel('cataloginventory/stock_item')->load($stockId);
        $productId = $stock->getproduct_id();
        $newQty = $this->getRequest()->getPost('qty');
        $description = $this->getRequest()->getPost('description');
        $oldQty = $stock->getQty();

        //calculate diff and create stock movement
        $diff = $newQty - $oldQty;
        if ($diff <> 0) {
            $targetWarehouse = null;
            $sourceWarehouse = null;
            if ($diff < 0)
                $sourceWarehouse = $stock->getstock_id();
            else
                $targetWarehouse = $stock->getstock_id();

            $additionalData = array('sm_type' => 'adjustment');
            mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                    $sourceWarehouse,
                    $targetWarehouse,
                    abs($diff),
                    $description,
                    $additionalData);
        }

        //redirect to product sheet
        $this->_redirect('adminhtml/Scanner_Inventory/ProductInformation', array('product_id' => $productId));
    }

    /**
     * Create a free delivery
     */
    public function FreeDeliveryAction() {
        $this->loadLayout();

        $mode = $this->getRequest()->getParam('mode');
        if ($mode == 'add_product')
        {
            $barcode = $this->getRequest()->getParam('barcode');
            $location = $this->getRequest()->getParam('location');
            $block = $this->getLayout()->getBlock('scanner_inventory_freedelivery')->addProduct($barcode, $location);
        }

        $this->renderLayout();
    }

    /**
     * Return product information through ajax
     * */
    public function AjaxProductInformationAction() {
        $barcode = $this->getRequest()->getParam('barcode');
        $response = array();

        $product = mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode($barcode);
        if ($product) {
            $response['error'] = false;
            $response['message'] = $this->__('Product found : %s', $product->getName());
            $response['product_name'] = $product->getName();
            $response['product_sku'] = $product->getSku();
        } else {
            $response['error'] = true;
            $response['message'] = $this->__('Barcode %s unknown', $barcode);
        }

        //json return
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/scanner');
    }

}