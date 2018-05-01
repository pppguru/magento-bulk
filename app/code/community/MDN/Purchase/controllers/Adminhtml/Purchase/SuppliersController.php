<?php


class MDN_Purchase_Adminhtml_Purchase_SuppliersController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        
    }

    /**
     * 
     *
     */
    public function ListAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Suppliers'));

        $this->renderLayout();
    }

    /**
     *  
     *
     */
    public function NewAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New supplier'));

        $this->renderLayout();
    }

    /**
     * 
     *
     */
    public function CreateAction() {

        $Supplier = mage::getModel('Purchase/Supplier');
        $Supplier->setsup_name($this->getRequest()->getParam('sup_name'));
        $Supplier->setsup_locale(Mage::getStoreConfig('general/locale/code'));
        $Supplier->setsup_currency(Mage::getStoreConfig('currency/options/base'));
        $Supplier->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Supplier Created'));
        $this->_redirect('adminhtml/Purchase_Suppliers/Edit/sup_id/' . $Supplier->getId());
    }

    /**
     *
     *
     */
    public function EditAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit supplier'));

        $this->renderLayout();
    }

    /**
     * Save supplier information
     *
     */
    public function SaveAction() {
        //load supplier & infos
        $Supplier = Mage::getModel('Purchase/Supplier')->load($this->getRequest()->getParam('sup_id'));
        $currentTab = $this->getRequest()->getParam('current_tab');
        $data = $this->getRequest()->getPost();

        //customize datas
        if (isset($data['sup_discount_level']))
            $data['sup_discount_level'] = str_replace(',', '.', $data['sup_discount_level']);

        //save datas
        foreach ($data as $key => $value) {
            $Supplier->setData($key, $value);
        }
        $Supplier->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Supplier Saved'));
        $this->_redirect('adminhtml/Purchase_Suppliers/Edit', array('sup_id' => $Supplier->getId(), 'tab' => $currentTab));
    }

    /**
     * Return supplier's orders grid
     */
    public function AssociatedOrdersGridAction() {
        $this->loadLayout();
        $supId = $this->getRequest()->getParam('sup_id');
        $Block = $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Orders');
        $Block->setSupplierId($supId);
        $this->getResponse()->setBody($Block->toHtml());
    }

    /**
     * Return supplier's products grid
     */
    public function ProductsGridAction() {
        $this->loadLayout();
        $supId = $this->getRequest()->getParam('sup_id');
        $Block = $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Products');
        $Block->setSupplierId($supId);
        $this->getResponse()->setBody($Block->toHtml());
    }

    /**
     * 
     */
    public function SynchronizeWithManufacturersAction() {
        try {
            $result = Mage::helper('purchase/supplier')->synchronizeManufacturersAndSuppliers();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s suppliers created', $result));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'purchase'));
    }

    /**
     * Delete a supplier 
     */
    public function deleteAction() {
        $supId = $this->getRequest()->getParam('sup_id');
        $supplier = Mage::getModel('Purchase/Supplier')->load($supId);

        try {
            //check that there is no puchase order
            $collection = Mage::getModel('Purchase/Order')->getCollection()->addFieldToFilter('po_sup_num', $supplier->getId())->getAllIds();
            if (count($collection) > 0)
                throw new Exception($this->__('You can not delete this supplier, there are attached purchase orders.'));

            //delete supplier
            $supplier->delete();

            //confirm & redirect
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Supplier deleted'));
            $this->_redirect('*/*/List');
            
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
            $this->_redirect('*/*/Edit', array('sup_id' => $supplier->getId()));
        }
        
    }

    public function exportSupplierCsvAction()
    {
        $fileName = 'suppliers.csv';
        $content = $this->getLayout()->createBlock('Purchase/Supplier_Grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);

    }

    public function exportSupplierExcelAction()
    {
        $fileName = 'suppliers.xls';
        $content = $this->getLayout()->createBlock('Purchase/Supplier_Grid')->getExcelFile();
        $content = file_get_contents($content['value']);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportSupplierProductsCsvAction()
    {
        $fileName = 'supplier_products.csv';
        $content = $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Products')->setSupplierId($this->getRequest()->getParam('sup_id'))->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportSupplierProductsExcelAction()
    {
        $fileName = 'supplier_products.xls';
        $content = $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Products')->setSupplierId($this->getRequest()->getParam('sup_id'))->getExcelFile();
        $content = file_get_contents($content['value']);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}