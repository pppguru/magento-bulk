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
 * @author     : Sylvain SALERNO
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_ProductReturn_ProductsPendingSupplierReturnController extends Mage_Adminhtml_Controller_Action
{
    /* Action */

    public function GridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function EditAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function AddAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function AddProductAction()
    {
        $productId   = $this->getRequest()->getParam('product_id');
        $warehouseId = $this->getRequest()->getParam('warehouse');

        //load product informations
        $product = mage::getModel('Catalog/Product')->load($productId);

        $rsrp = mage::getModel('ProductReturn/SupplierReturn_Product');
        $rsrp->setrsrp_product_id($productId);
        $rsrp->setrsrp_product_sku($product->getsku());
        $rsrp->setrsrp_product_name($product->getname());
        $rsrp->setrsrp_status('Pending');
        if ($warehouseId != null)
            $rsrp->setrsrp_decrement_from_warehouse($warehouseId);
        $rsrp->save();

        //stock movment if necessary
        if ($warehouseId != null) {
            mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                $productId,
                $warehouseId,
                null,
                1,
                mage::helper('ProductReturn')->__('Product put in the products pending supplier return list')
            );
        }

        $this->_redirect('ProductReturn/ProductsPendingSupplierReturn/Edit/', array('rsrp_id' => $rsrp->getrsrp_id()));
    }

    public function SaveAction()
    {
        $data = $this->getRequest()->getPost();
        $rsrp = mage::getModel('ProductReturn/SupplierReturn_Product')->load($data['rsrp_id']);
        foreach ($data as $k => $v) {
            $rsrp->setData($k, $v);
        }
        if (!isset($data['rsrp_pop_id'])) {
            $rsrp->setrsrp_pop_id('');
        }
        if (isset($data['rsrp_do_not_value_it']) and $data['rsrp_do_not_value_it'] == "on")
            $rsrp->setrsrp_do_not_value_it('1');
        else
            $rsrp->setrsrp_do_not_value_it('0');
        $rsrp->save();
        Mage::getSingleton("adminhtml/session")->addSuccess($this->__('Data saved'));
        $this->_redirect('ProductReturn/ProductsPendingSupplierReturn/Edit', array('rsrp_id' => $this->getRequest()->getParam('rsrp_id')));
    }

    public function loadPoListAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            try {
                //init
                $productId    = $this->getRequest()->getParam('product_id');
                $supplierId   = $this->getRequest()->getParam('supplier_id');
                $defaultPopId = $this->getRequest()->getParam('default_pop_id');
                $html         = mage::helper('ProductReturn/SupplierReturn')->loadSelect($productId, $supplierId, $defaultPopId);

                //send response
                $this->getResponse()->setBody($html);
            } catch (Exception $e) {
                $this->getResponse()->setBody('An error occured.');
            }
        }
    }


    public function checkSerialAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            try {
                $rsrpId    = $this->getRequest()->getParam('rsrp_id');
                $productId = $this->getRequest()->getParam('product_id');
                $serial    = $this->getRequest()->getParam('serial');
                $serial    = str_replace('slash', '/', $serial);
                $html      = mage::helper('ProductReturn/SupplierReturn')->loadCheckSerial($rsrpId, $productId, $serial);
                $this->getResponse()->setBody($html);
            } catch (Exception $e) {
                $this->getResponse()->setBody('An error occured.');
            }
        }
    }

    /*
     * insert product into rma_supplier_return.
     * update entry in rma_supplier_return_products to add the rsr_id.
     */
    public function MassCreateSupplierReturnAction()
    {
        try {
            $rsrp_ids = $this->getRequest()->getPost();
            //check if there is only one supplier in the selected product and if supplier_id and serial is set for each products
            $lastSupId = null;
            foreach ($rsrp_ids['rsrp_ids'] as $rsrp_id) {
                $supId  = null;
                $serial = null;
                $rsrp   = mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrp_id);
                $supId  = $rsrp->getrsrp_sup_id();
                $serial = $rsrp->getrsrp_serial();
                //if supId is null, go to an error
                if (is_null($supId))
                    throw new Exception(mage::helper('ProductReturn')->__('There is at least one selected product with an unknown supplier.'));
                if (is_null($serial))
                    throw new Exception(mage::helper('ProductReturn')->__('There is at least one selected product with an unknown serial.'));
                if ($lastSupId == null)
                    $lastSupId = $supId;
                if ($lastSupId != $supId)
                    throw new Exception(mage::helper('ProductReturn')->__('There is more than one supplier for the selected products.'));
            }

            $rsr = mage::getModel('ProductReturn/SupplierReturn')->createSupplierReturn($supId);
            foreach ($rsrp_ids['rsrp_ids'] as $rsrp_id) {
                $rsr->addProduct($rsrp_id);
            }

            $rsr->addHistory(Mage::Helper('ProductReturn')->__('Create new supplier return'));

            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::Helper('ProductReturn')->__('The new supplier return has been successfully created.'));
            $this->_redirect('ProductReturn/SupplierReturn/edit/rsr_id/' . $rsr->getrsr_id());
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }


    public function MassDestroyProductsAction()
    {
        try {
            $rsrp_ids = $this->getRequest()->getPost();
            foreach ($rsrp_ids['rsrp_ids'] as $rsrpId) {
                $rsrp = mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrpId);
                $rsrp->process(MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusDestroy);
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('ProductReturn')->__('Products have been moved into destroy warehouse'));
            $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    public function MassRemoveProductsFromRsrAction()
    {
        try {
            $rsrp_ids = $this->getRequest()->getPost();
            $rsrId    = $this->getRequest()->getParam('rsr_id');
            foreach ($rsrp_ids['rsrp_ids'] as $rsrp_id) {
                Mage::getModel('ProductReturn/SupplierReturn')->removeProduct($rsrp_id);
            }
            $rsr = mage::getModel('ProductReturn/SupplierReturn')->load($rsrId);
            $rsr->toggleStatusIfAllProductsProcessed();

            Mage::getSingleton("adminhtml/session")->addSuccess("Products has been removed from this supplier return");
            $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    public function MassProcessAction()
    {
        try {
            $rsrp_ids   = $this->getRequest()->getPost();
            $new_status = $this->getRequest()->getParam('status');
            $rsr_id     = $this->getRequest()->getParam('rsr_id');
            foreach ($rsrp_ids['rsrp_ids'] as $rsrp_id) {
                Mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrp_id)->process($new_status);
            }

            $rsr = Mage::getModel('ProductReturn/SupplierReturn')->load($rsr_id);
            $rsr->toggleStatusIfAllProductsProcessed();

            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('ProductReturn')->__("Products Status have been successfully changed"));
            $this->_redirectReferer();

        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    public function MassAssignSupplierAction()
    {
        try {
            $data = $this->getRequest()->getPost();
            foreach ($data['rsrp_ids'] as $rsrp_id) {
                Mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrp_id)->setrsrp_sup_id($data['supplier'])->save();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('ProductReturn')->__("The supplier has been successfully assign to the products"));
            $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    public function exportCsvAction()
    {
        try {
            $fileName = 'ProductsPendingSupplierReturn.csv';
            $content  = Mage::Helper('ProductReturn/SupplierReturn')->getFormatedPendingProductsCsv();
            $this->_prepareDownloadResponse($fileName, $content);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }

    }
}