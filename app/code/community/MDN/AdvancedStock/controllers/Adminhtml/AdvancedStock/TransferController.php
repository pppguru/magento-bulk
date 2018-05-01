<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_TransferController extends Mage_Adminhtml_Controller_Action {

    /**
     * Transfer grid
     */
    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Stock transfer'));

        $this->renderLayout();
    }

    /**
     * Edit transfer
     */
    public function EditAction() {

        $transferId = $this->getRequest()->getParam('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);
        mage::register('current_transfer', $transfer);

        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Transfer').' #'.$transferId);

        $this->renderLayout();
    }

    /**
     * Save stock transfer
     */
    public function SaveAction() {
        //load object
        $transfer = mage::getModel('AdvancedStock/StockTransfer');

        $data = $this->getRequest()->getPost();
        if (isset($data['st_id']) && $data['st_id'])
            $transfer->load($data['st_id']);

        if($data['st_source_warehouse'] != $data['st_target_warehouse']){

            //update datas
            foreach ($data as $key => $value)
                $transfer->setData($key, $value);

            //add products
            $productsToAdd = $this->getProductsToAdd($data['add_product_log']);
            foreach ($productsToAdd as $productId => $qty) {
                if ($qty > 0)
                    $transfer->addProduct($productId, $qty);
            }

            //update products data
            $productsChanges = $this->getProductsChanges($data['product_log']);
            foreach ($productsChanges as $id => $fields) {
                $tranferProduct = mage::getModel('AdvancedStock/StockTransfer_Product')->load($id);
                foreach ($fields as $field => $value)
                    $tranferProduct->setData($field, $value);

                if ($tranferProduct->getdelete())
                    $tranferProduct->delete();
                else
                    $tranferProduct->save();
            }

            //save
            $transfer->save();

            //update status
            $transfer->updateStatus();

            //confirm & redirect
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('AdvancedStock')->__('Data saved'));
            
        }else{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('AdvancedStock')->__('Source warehouse is identical to target warehouse'));
        }

        
        $this->_redirect('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $transfer->getId()));
    }

    /**
     * Converts products to add string to array
     * @param <type> $data
     */
    protected function getProductsToAdd($data) {
        $productsToAdd = array();
        if(!empty($data)){
            $lines = explode(';', $data);
            foreach ($lines as $line) {
                $t = explode('=', $line);
                if (count($t) != 2)
                    continue;
                $qty = $t[1];
                $id = str_replace('add_qty_', '', $t[0]);
                $productsToAdd[$id] = $qty;
            }
        }
        return $productsToAdd;
    }

    /**
     * Converts products to change string to array
     * @param <type> $data
     */
    protected function getProductsChanges($data) {
        $productsToChange = array();

        if(!empty($data)){
            $lines = explode(';', $data);
            foreach ($lines as $line) {
                $t = explode('=', $line);
                if (count($t) != 2)
                    continue;

                $value = $t[1];
                $fieldName = $t[0];
                $lastUnderscore = strrpos($fieldName, '_');

                $id = substr($fieldName, $lastUnderscore + 1);
                $fieldName = substr($fieldName, 0, $lastUnderscore);

                if (!isset($productsToChange[$id]))
                    $productsToChange[$id] = array();
                $productsToChange[$id][$fieldName] = $value;
           }
        }
        return $productsToChange;
    }

    /**
     * Ajax update for add products grid
     */
    public function AddProductsGridAction() {
        $transferId = $this->getRequest()->getParam('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);
        mage::register('current_transfer', $transfer);

        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('AdvancedStock/Transfer_Edit_Tabs_AddProducts')->toHtml()
        );
    }

    /**
     * Ajax update for products grid
     */
    public function ProductsGridAction() {
        $transferId = $this->getRequest()->getParam('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);
        mage::register('current_transfer', $transfer);

        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('AdvancedStock/Transfer_Edit_Tabs_Products')->toHtml()
        );
    }

    /**
     * Apply transfer
     * @return <type>
     */
    public function ApplyAction() {
        try {
            $transferId = $this->getRequest()->getParam('st_id');
            $forceTransfer = $this->getRequest()->getParam('force_transfer');
            $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);

            if ((!$transfer->canBeApplied()) && (!$forceTransfer)) {
                $this->_redirect('adminhtml/AdvancedStock_Transfer/NotFullyApplicable', array('st_id' => $transfer->getId()));
                return;
            }

            $transfer->apply();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Transfer applied'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
        }

        //confirm & redirect
        $this->_redirect('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $transfer->getId()));
    }

    /**
     * Display products that cant be transfered
     */
    public function NotFullyApplicableAction() {
        $transferId = $this->getRequest()->getParam('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);
        mage::register('current_transfer', $transfer);

        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Transfer').' #'.$transferId);
        
        $this->renderLayout();
    }

    /**
     * Print transfer action
     */
    public function PrintAction() {
        $transferId = $this->getRequest()->getParam('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);

        //create pdf & download
        $obj = mage::getModel('AdvancedStock/Pdf_StockTransfer');
        $pdf = $obj->getPdf(array($transfer));
        $this->_prepareDownloadResponse(mage::helper('AdvancedStock')->__('Transfer #%s', $transfer->getst_name()) . '.pdf', $pdf->render(), 'application/pdf');
    }

    /**
     * Display screen to add products to a transfer scanning products
     */
    public function AddProductsWithScannerAction()
    {
        //load transfer
        $transferId = $this->getRequest()->getParam('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);
        Mage::register('current_transfer', $transfer);
        
        //render
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Transfer').' #'.$transferId);

        $this->renderLayout();
    }
    
    /**
     * Return product information in JSON from product barcode 
     */
    public function ScannerProductInformationAction()
    {
        $result = array('');
        
        $barcode = $this->getRequest()->getParam('barcode');
        $product = Mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode($barcode);
        
        $result['error'] = ($product == null);
        if ($product)
        {
            $product->setqty(0);
            $product->setbarcode($barcode);
            $product->setimage_url($this->getImageUrl($product));
            $result['product'] = $product->getData();
            $result['message'] = $this->__('%s added', $product->getName());
        }
        else
        {
            $result['message'] = $this->__('No product matching to barcode %s', $barcode);
        }
        
        //return result as json
        $result = Zend_Json::encode($result);
        $this->getResponse()->setBody($result);        
    }
    
    /**
     * Add products to transfer (coming from the screen to add products scanning barcode) 
     */
    public function AddProductsToTransferAction()
    {
        $transferId = $this->getRequest()->getPost('st_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);
        
        $productAddedCount = 0;

        //add product to transfer if quantity >0
        if($transfer->getId()>0){
          $productData = $this->getRequest()->getPost('data');

          if(!empty($productData) && strlen($productData)>1){
            $rows = explode('#', $productData);
            foreach($rows as $row)
            {
              if(!empty($row)){
                list($productId, $qty) = explode('=', $row);
                if($qty>0){
                  $transfer->addProduct($productId, $qty);
                  $productAddedCount++;
                }
              }
            }
          }
        }
        
        //confirm & redirect
        if($productAddedCount>0){
          Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s products added to transfer', $productAddedCount));
        }else{
          Mage::getSingleton('adminhtml/session')->addError($this->__('%s products added to transfer', $productAddedCount));
        }
        
        $this->_redirect('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $transferId));
    }
    
    /**
     * Return image url for product
     * @param <type> $product
     * @return <type>
     */
    protected function getImageUrl($product) {
        return $imageUrl = Mage::helper('AdvancedStock/Product_Image')->getProductImageUrl($product);
    }


    public function ImportProductsAction()
    {
        $transferId = $this->getRequest()->getPost(MDN_AdvancedStock_Block_Transfer_Edit_Tabs_ImportProducts::fieldTransferId);
        $delimiter = $this->getRequest()->getPost(MDN_AdvancedStock_Block_Transfer_Edit_Tabs_ImportProducts::fieldOptionDelimiter);
        
        $result = '';
        

        try {

            //save csv file
            $uploader = new Varien_File_Uploader(MDN_AdvancedStock_Block_Transfer_Edit_Tabs_ImportProducts::fieldFile);
            $uploader->setAllowedExtensions(array('txt', 'csv'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/';
            $uploader->save($path);

            
            $uploadFile = $uploader->getUploadedFileName();
            if ($uploadFile) {

                //process file
                $result = mage::helper('AdvancedStock/StockTransfer')->importTransferProducts(file($path.$uploadFile), $transferId, $delimiter);

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__($result));
            }
            else
                throw new Exception($this->__('Unable to load file'));
            
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        mage::register('import_result', $result);


        $this->_redirect('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $transferId));


    }

    public function populateWithSupplyNeedsAction()
    {
        $transferId = $this->getRequest()->getParam('transfer_id');
        $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);

        try
        {
            $count = $transfer->populateWithSupplyNeeds();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s products added to the transfer', $count));
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        $this->_redirect('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $transferId));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }

}