<?php


class MDN_Purchase_Adminhtml_Purchase_SupplyNeedsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        
    }

    /**
     * 
     *
     */
    public function StatsAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Stats'));

        $this->renderLayout();
    }

    /**
     * Display grid
     *
     */
    public function GridAction() {
        
        //if manufacturer attribute is not set, display warning message
        if (!Mage::getStoreConfig('purchase/supplyneeds/manufacturer_attribute'))
            Mage::getSingleton('adminhtml/session')->addError($this->__('Manufacturer attribute is not set in system > configuration > purchase > supply needs, supply needs may not be displayed'));
        
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Supply needs'));


        $warehouseId = $this->getRequest()->getParam('warehouse');
        Mage::helper('purchase/SupplyNeeds')->setCurrentWarehouse($warehouseId);

        $block = $this->getLayout()->createBlock('Purchase/SupplyNeeds_Grid');
        $block->setTemplate('Purchase/SupplyNeeds/Grid.phtml');
        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    /**
     * Return supply needs grid in ajax
     */
    public function AjaxGridAction() {
        $poNum = $this->getRequest()->getParam('po_num');
        $mode = $this->getRequest()->getParam('mode');

        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Purchase/SupplyNeeds_Grid');
        $block->setMode($mode, $poNum);
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Create a purchase order and add products from selected supply needs
     *
     */
    public function CreatePurchaseOrderAction() {

        //init vars
        $data = $this->getRequest()->getPost('supply_needs_log');
        $supId = $this->getRequest()->getPost('sup_id');

        //convert data
        $supplyNeeds = array();
        $data = explode(';', $data);
        foreach ($data as $item) {
            $t = explode('=', $item);
            if (count($t) == 2) {
                $snId = str_replace('qty_', '', $t[0]);
                $qty = $t[1];
                if ($qty > 0)
                    $supplyNeeds[$snId] = $qty;
            }
        }

        //create order
        $order = Mage::helper('purchase')->createNewOrder($supId);

        //add products
        foreach ($supplyNeeds as $productId => $qty) {

            try {
                $order->AddProduct($productId, $qty);
            } catch (Exception $ex) {
                Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            }
        }

        //confirme
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order successfully Created'));
        $this->_redirect('adminhtml/Purchase_Orders/Edit', array('po_num' => $order->getId()));
    }

    /**
     * Create purchase order from stats
     */
    public function CreatePoFromStatsAction() {

        //get datas
        $supplierId = $this->getRequest()->getParam('sup_id');
        $mode = $this->getRequest()->getParam('mode');

        //create PO
        $po = mage::helper('purchase/Order')->createNewOrder($supplierId);

        //get supply needs
        $supplyNeeds = mage::getResourceModel('Purchase/SupplyNeeds_NewCollection')->joinSuppliersById($supplierId);
        

        foreach ($supplyNeeds as $supplyNeed) {

            $qty = $supplyNeed->getData($mode);
            $productId = $supplyNeed->getentity_id();
            try {
                if ($qty > 0)
                    $po->AddProduct($productId, $qty);
            } catch (Exception $ex) {
                Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            }
        }

        //confirm and redirect to PO
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order successfully Created'));
        $this->_redirect('adminhtml/Purchase_Orders/Edit', array('po_num' => $po->getId()));
    }

    public function exportCsvSupplyNeedAction() {

        $fileName = 'supply_needs_export.csv';

        $content = $this->getLayout()->createBlock('Purchase/SupplyNeeds_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportExcelSupplyNeedAction() {

        $fileName = 'supply_needs_export.xml';

        $content = $this->getLayout()->createBlock('Purchase/SupplyNeeds_Grid')
            ->getExcelFile($fileName);

        $content = file_get_contents($content['value']);

        $this->_prepareDownloadResponse($fileName, $content);
    }


    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}