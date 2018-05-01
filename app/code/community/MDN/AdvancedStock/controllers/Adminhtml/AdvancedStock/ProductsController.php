<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_ProductsController extends Mage_Adminhtml_Controller_Action {

    /**
     * Enter description here...
     *
     */
    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Product List'));

        $this->renderLayout();
    }

    /**
     *
     *
     */
    public function EditAction() {

        //check if product exists
        $productId = $this->getRequest()->getParam('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('This product does not exist !'));
            $this->_redirect('adminhtml/AdvancedStock_Products/Grid');
        }
        else
        {
            $this->loadLayout();

            $this->_setActiveMenu('erp');
            $this->getLayout()->getBlock('head')->setTitle($this->__('Product').' #'.$productId);
            
            $this->renderLayout();
        }
    }

    /**
     * Return customer pending orders grid
     *
     */
    public function CustomerPendingOrdersGridAction() {
        $this->loadLayout();
        $ProductId = $this->getRequest()->getParam('product_id');
        $product = mage::getModel('catalog/product')->load($ProductId);
        $Block = $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_PendingSalesOrder');
        $Block->setProduct($product);
        $this->getResponse()->setBody($Block->toHtml());
    }
    
    /**
     * Return all orders grid for one product
     *
     */
    public function AllOrdersGridAction() {
        $this->loadLayout();
        $ProductId = $this->getRequest()->getParam('product_id');
        $product = mage::getModel('catalog/product')->load($ProductId);
        $Block = $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_AllSalesOrder');
        $Block->setProduct($product);
        $this->getResponse()->setBody($Block->toHtml());
    }

    /**
     * Save advanced stock product sheet data
     *
     */
    public function SaveAction() {
      
        
        $productId = $this->getRequest()->getPost('product_id');
        $product = mage::getModel('catalog/product')->load($productId);
        $currentTab = $this->getRequest()->getPost('current_tab');



        try {
            //save stocks information
            $stockData = $this->getRequest()->getPost('stocks');
            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/stock/stocks'))
            {
                $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
                foreach ($stocks as $stock) {
                    //store main information
                    foreach ($stockData as $key => $value) {
                        $stock->setData($key, $value);
                    }

                    //store stocks info
                    $usedDefautNotifyStockQty = 0;
                    if ($this->getRequest()->getPost('use_config_notify_stock_qty_' . $stock->getId()) == 1)
                        $usedDefautNotifyStockQty = 1;
                    $usedDefautIdealStockLevel = 0;
                    if ($this->getRequest()->getPost('use_config_ideal_stock_level_' . $stock->getId()) == 1)
                        $usedDefautIdealStockLevel = 1;
                    $stock->setuse_config_notify_stock_qty($usedDefautNotifyStockQty);
                    $stock->setuse_config_ideal_stock_level($usedDefautIdealStockLevel);
                    $stock->setshelf_location($this->getRequest()->getPost('shelf_location_' . $stock->getId()));
                    $stock->setis_favorite_warehouse($this->getRequest()->getPost('is_favorite_warehouse_'.$stock->getId()));
                    if ($this->getRequest()->getPost('notify_stock_qty_' . $stock->getId()) != '') {
                        $stock->setnotify_stock_qty($this->getRequest()->getPost('notify_stock_qty_' . $stock->getId()));
                    }
                    if ($this->getRequest()->getPost('ideal_stock_level_' . $stock->getId()) != '') {
                        $stock->setideal_stock_level($this->getRequest()->getPost('ideal_stock_level_' . $stock->getId()));
                    }
                    $stock->seterp_exclude_automatic_warning_stock_level_update($this->getRequest()->getPost('erp_exclude_automatic_warning_stock_level_update_'.$stock->getId()));
                    $stock->save();
                }
            }
            
            //associate new warehouse (if required)
            $associateWarehouseData = $this->getRequest()->getPost('affect_to_warehouse');
            if ($associateWarehouseData) {
                if ($associateWarehouseData['warehouse_id']) {
                    $warehouseId = $associateWarehouseData['warehouse_id'];
                    $preferedStockLevel = $associateWarehouseData['prefered_stock_level'];
                    $idealStockLevel = $associateWarehouseData['ideal_stock_level'];
                    $isFavorite = $associateWarehouseData['is_favorite'];

                    $newStockItem = mage::getModel('cataloginventory/stock_item')->createStock($productId, $warehouseId);
                    if ($isFavorite)
                        $newStockItem->setis_favorite_warehouse($isFavorite);
                    if ($preferedStockLevel)
                        $newStockItem->setuse_config_notify_stock_qty(0)->setnotify_stock_qty($preferedStockLevel);
                    if ($idealStockLevel)
                        $newStockItem->setuse_config_ideal_stock_level(0)->setideal_stock_level($idealStockLevel);
                    $newStockItem->save();
                }
            }

            //process barcodes & serials
            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/barcode/edit_barcode'))
            {
                try
                {
                    $string = $this->getRequest()->getPost('barcodes');
                    mage::helper('AdvancedStock/Product_Barcode')->saveBarcodesFromString($productId, $string);
                }
                catch(Exception $ex)
                {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('This barcode is already used for another product'));
                    Mage::logException($ex);
                }
            }
            
            $string = $this->getRequest()->getPost('serials_to_add');
            if ($string)
                $insertedSerials = mage::helper('AdvancedStock/Product_Serial')->addSerialsFromString($productId, $string);

            //dispatch event to allow other extension to save data for their own tabs
            $postData = $this->getRequest()->getPost();
            Mage::dispatchEvent('advancedstock_product_sheet_save', array('product' => $product, 'post_data' => $postData));

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Changes saved'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }

        //redirect to purchase product sheet
        $this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId, 'tab' => $currentTab));
    }

    /**
     * Return stock graph picture
     *
     */
    public function StockGraphAction() {
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        $productId = $this->getRequest()->getParam('product_id');
        $groupBy = $this->getRequest()->getParam('groupby');

        $displayStock = $this->getRequest()->getParam('displaystock');
        $displayOutgoing = $this->getRequest()->getParam('displayoutgoing');
        $displayIngoing = $this->getRequest()->getParam('displayingoing');

        mage::helper('AdvancedStock/Product_StockGraph')->getGraphImage($productId, $from, $to, $groupBy, $displayStock, $displayOutgoing, $displayIngoing);
        die(''); //die to flush output
    }

    /**
     * Export products grid
     *
     */
    public function exportCsvAction() {
        $fileName = 'products.csv';
        $content = $this->getLayout()->createBlock('AdvancedStock/Product_Grid')
                        ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportExcelAction() {
        $fileName = 'products.xls';
        $content = $this->getLayout()->createBlock('AdvancedStock/Product_Grid')
            ->getExcelFile();

        $content = file_get_contents($content['value']);

        $this->_prepareDownloadResponse($fileName, $content);
    }


    public function SaleHistoryExportCsvAction() {
        $fileName = 'products_sales_history.csv';
        $content = $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_AllSalesOrder')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }



    /**
     * Update stocks, ordered qty, reserved qty for product's stocks
     *
     */
    public function UpdateStockAction() {
        $productId = $this->getRequest()->getParam('product_id');
        $startTimeStamp = time();

        try {
            //update stocks
            $product = mage::getModel('catalog/product')->load($productId);
            if ($product->getId()) {
                mage::helper('AdvancedStock/Product_Base')->updateStocks($product);

                //launch event to allow other updates for product (for example, purchase module handles this event to update waiting for delivery date and quantities
                Mage::dispatchEvent('advancedstock_product_force_stocks_update_requested', array('product' => $product));
            }

            $endTimeStamp = time();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Stocks updated in %s seconds', ($endTimeStamp - $startTimeStamp)));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }

        //redirect to product sheet
        $this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId));
    }

    /**
     * Release product for order
     *
     */
    public function ReleaseProductForOrderAction() {
        $productId = $this->getRequest()->getParam('product_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $orderItemId = $this->getRequest()->getParam('order_item_id');

        try {
            $order = mage::getModel('sales/order')->load($orderId);
            $orderItem = mage::getModel('sales/order_item')->load($orderItemId);
            mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $orderItem);

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product released'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }

        //redirect to product sheet
        $this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId));
    }

    /**
     * reserve product for order
     *
     */
    public function ReserveProductForOrderAction() {
        $productId = $this->getRequest()->getParam('product_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $orderItemId = $this->getRequest()->getParam('order_item_id');

        try {
            $order = mage::getModel('sales/order')->load($orderId);
            
            if (!Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->productReservationAllowed($order))
                throw new Exception($this->__('This order doesnt fullfill reservation conditions'));

            $orderItem = mage::getModel('sales/order_item')->load($orderItemId);
            mage::helper('AdvancedStock/Product_Reservation')->reserveOrderProduct($order, $orderItem);

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product reserved'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }

        //redirect to product sheet
        $this->_redirect('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId));
    }

    /**
     * Return serial grid in ajax
     *
     */
    public function ProductSerialGridAction() {
        $this->loadLayout();
        $ProductId = $this->getRequest()->getParam('product_id');
        $product = mage::getModel('catalog/product')->load($ProductId);
        $Block = $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_Serials');
        $Block->setProduct($product);
        $this->getResponse()->setBody($Block->toHtml());
    }

    /**
     * Mass a associate a product to a supplier
     */
    public function MassChangeFavoriteWarehouseAction(){

        $request = $this->getRequest();

        $productList = $request->getPost('ProductsList');
        $warehouseId = $request->getPost('warehouses');

        $associationCreatedCount = 0;
        $errors = '';

        if($productList && $warehouseId) {
            
            foreach ($productList as $productId) {

                try{
                    $stockItem = mage::getModel('cataloginventory/stock_item')->getOrCreateStock($productId, $warehouseId);
                    $stockItem->setis_favorite_warehouse(1);
                    $stockItem->save();

                    $associationCreatedCount++;
                }catch(Exception $ex){
                    $errors .= $this->__('Warehouse not defined as favorite for product %s, reason : %s',$productId,$ex->getMessage());
                }
            }
        }

        if($associationCreatedCount>0){
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s warehouses set as favorite',$associationCreatedCount));
        }

        if($errors){
           Mage::getSingleton('adminhtml/session')->addError($errors);
        }

        $this->_redirect('adminhtml/AdvancedStock_Products/Grid/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }

}
