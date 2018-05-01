<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_BarcodeController extends Mage_Adminhtml_Controller_Action
{
	public function PrintLabelAction()
	{
            //get param
            $productId = $this->getRequest()->getParam('product_id');
            $qty = $this->getRequest()->getParam('qty');

            //create pdf & download
            $obj = mage::getModel('AdvancedStock/Pdf_BarcodeLabels');
            $pdf = $obj->getPdf(array($productId => $qty));
            $this->_prepareDownloadResponse(mage::helper('purchase')->__('Barcode labels') . '.pdf', $pdf->render(), 'application/pdf');
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }
}
