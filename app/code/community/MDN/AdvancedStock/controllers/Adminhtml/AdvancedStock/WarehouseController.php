<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_WarehouseController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Warehouses'));

        $this->renderLayout();
    }

    public function warehouseGridAction() {
        $this->loadLayout();
        $Block = $this->getLayout()->createBlock('AdvancedStock/Warehouse_grid');
        $this->getResponse()->setBody($Block->toHtml());
    }

    public function NewAction() {             
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Warehouse'));

        $this->renderLayout();
    }

    public function EditAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Warehouse'));
        
        $this->renderLayout();
    }

    public function CreateAction() {
        try {
            $data = $this->getRequest()->getPost('data');
            $obj = mage::getModel('AdvancedStock/Warehouse')
                            ->setstock_name($data['stock_name'])
                            ->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('New warehouse created'));
            $this->_redirect('*/*/Edit', array('stock_id' => $obj->getId()));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured'));
            $this->_redirect('*/*/New');
        }
    }

    public function SaveAction() {

        $data = $this->getRequest()->getPost('data');

        try {
            //save stock information
            if (!isset($data['stock_available_for_sales']))
                $data['stock_available_for_sales'] = '0';
            if (!isset($data['stock_disable_supply_needs']))
                $data['stock_disable_supply_needs'] = '0';
            $obj = mage::getModel('AdvancedStock/Warehouse')->load($data['stock_id']);
            foreach ($data as $key => $value)
                $obj->setData($key, $value);
            $obj->save();

            //save assignments
            mage::getResourceModel('AdvancedStock/Assignment')->deleteAssignmentsForStock($obj->getId());
            $assignments = $this->getRequest()->getPost('assignment');
            if (is_array($assignments)) {
                foreach ($assignments as $website => $assignmentData) {
                    foreach ($assignmentData as $assignment => $value) {
                        if ($value == 1) {
                            //insert new assignment
                            mage::getModel('AdvancedStock/Assignment')
                                    ->setcsa_stock_id($obj->getId())
                                    ->setcsa_website_id($website)
                                    ->setcsa_assignment($assignment)
                                    ->save();
                        }
                    }
                }
            }


            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Changes saved'));
            $this->_redirect('*/*/Edit', array('stock_id' => $obj->getId()));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
            $this->_redirect('*/*/Edit', array('stock_id' => $data['stock_id']));
        }
    }

    /**
     * Delete a warehouse
     */
    public function DeleteAction() {

        $stockId = $this->getRequest()->getParam('stock_id');
        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($stockId);

        try
        {
          if ($warehouse->canDelete()) {

              $warehouse->deleteWarehouse();

              Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Warehouse deleted'));
          }
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }
        
        $this->_redirect('*/*/Grid');
    }

    /**
     * Return products grid for warehouse
     *
     */
    public function ProductsGridAction() {
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Products')->setWarehouseId($warehouseId)->toHtml()
        );
    }

    /**
     * Export stock levels at a specific date
     *
     */
    public function ExportStockAtDateAction() {
        $date = $this->getRequest()->getPost('date_end');
        $stockId = $this->getRequest()->getPost('stock_id');

        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($stockId);

        $fileName = $this->__('Stock levels for %s at %s', $warehouse->getstock_name(), (($date)?$date:date("Y-m-d")));

        $content = mage::helper('AdvancedStock/Warehouse')->getStockAtDateContent($stockId, $date);
        
        $this->_prepareDownloadResponse($fileName.'.csv', $content, 'text/plain');
    }

    /**
     * 
     *
     * @param unknown_type $fileName
     * @param unknown_type $content
     * @param unknown_type $contentType
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null) {
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', strlen($content))
                ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
                ->setBody($content);
    }

    /**
     * Import stock level at a specific date
     */
    public function ImportStockAtDateAction() {
        $stockId = $this->getRequest()->getPost('stock_id');
        $caption = $this->getRequest()->getPost('stock_movement_caption');
        $date = $this->getRequest()->getPost('import_date_end');
        $result = '';

        try {
            //save text file
            $uploader = new Varien_File_Uploader('import_stock');
            $uploader->setAllowedExtensions(array('txt', 'csv'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/';
            $uploader->save($path);

            //If file is uploaded
            if ($uploadFile = $uploader->getUploadedFileName()) {
                //load file
                $filePath = $path . $uploadFile;
                $lines = file($filePath);

                //process file
                $result = mage::helper('AdvancedStock/ImportStock')->process($lines, $stockId, $date, $caption);
            }
            else
                throw new Exception($this->__('Unable to load file'));

        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        //render
        mage::register('import_result', $result);
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('core/template', 'import_result', array('template' => 'AdvancedStock/Warehouse/Result.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    /**
     * Import Shelf Location
     */
    public function ImportShelfLocationAction() {
        $stockId = $this->getRequest()->getPost('stock_id');
        $result = '';

        try {
            //save text file
            $uploader = new Varien_File_Uploader('import_shelf_location');
            $uploader->setAllowedExtensions(array('txt', 'csv'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/';
            $uploader->save($path);

            //If file is uploaded
            if ($uploadFile = $uploader->getUploadedFileName()) {
                //load file
                $filePath = $path . $uploadFile;
                $lines = file($filePath);

                //process file
                $result = mage::helper('AdvancedStock/ImportShelfLocation')->process($lines, $stockId);
            }
            else
                throw new Exception($this->__('Unable to load file'));

        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        //render
        mage::register('import_result', $result);
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('core/template', 'import_result', array('template' => 'AdvancedStock/Warehouse/Result.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function exportWarehouseProductsCsvAction()
    {
        $fileName = 'warehouse_products_export.csv';
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $content = $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Products')->setWarehouseId($warehouseId)->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);

    }

    public function exportWarehouseProductsExcelAction()
    {
        $fileName = 'warehouse_products_export.xls';
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $content = $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Products')->setWarehouseId($warehouseId)->getExcelFile();
        $content = file_get_contents($content['value']);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }

}