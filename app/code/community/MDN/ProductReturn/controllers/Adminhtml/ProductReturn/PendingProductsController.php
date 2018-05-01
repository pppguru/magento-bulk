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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_ProductReturn_Adminhtml_ProductReturn_PendingProductsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Display pending product grid
     *
     */
    public function GridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Print selected pending products
     *
     */
    public function PrintAction()
    {
        $productIds = $this->getRequest()->getPost('pending_products');

        $pdf = mage::helper('ProductReturn/pendingProducts')->PrintPendingProducts($productIds);
        $this->_prepareDownloadResponse(mage::helper('ProductReturn')->__('Pending products') . '.pdf', $pdf->render(), 'application/pdf');

    }

    /**
     * Print selected pending products
     *
     */
    public function SetAsProcessedAction()
    {
        $productIds = $this->getRequest()->getPost('pending_products');
        mage::helper('ProductReturn/pendingProducts')->processProducts($productIds);


        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Products processed'));
        $this->_redirect('adminhtml/ProductReturn_PendingProducts/Grid');
    }

    /**
     * Print selected pending products
     *
     */
    public function PrintProcessedAction()
    {
        $productIds = $this->getRequest()->getPost('pending_products');

        //process products
        mage::helper('ProductReturn/pendingProducts')->processProducts($productIds);

        //print
        $pdf = mage::helper('ProductReturn/pendingProducts')->PrintPendingProducts($productIds);
        $this->_prepareDownloadResponse(mage::helper('ProductReturn')->__('Pending products') . '.pdf', $pdf->render(), 'application/pdf');

    }


    protected function _isAllowed()
    {
        return true;
    }

}