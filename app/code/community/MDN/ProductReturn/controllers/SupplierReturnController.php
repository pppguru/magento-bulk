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

class MDN_ProductReturn_SupplierReturnController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $type       = ucfirst($this->getRequest()->getParam('type'));
        $block_path = '';

        switch ($type) {
            case 'ProductsPendingSupplierReturn':
                $block_path = 'ProductsPendingSupplierReturn_Grid';
                break;
            case 'SupplierReturnNew':
                $block_path = 'SupplierReturn_Tabs_TabNewGrid';
                break;
            case 'SupplierReturnInquiry':
                $block_path = 'SupplierReturn_Tabs_TabInquiryGrid';
                break;
            case 'SupplierReturnSentToSupplier':
                $block_path = 'SupplierReturn_Tabs_TabSentToSupplierGrid';
                break;
            case 'SupplierReturnComplete':
                $block_path = 'SupplierReturn_Tabs_TabCompleteGrid';
                break;
        }

        try {

            $fileName = $type . '.csv';
            $block    = $this->getLayout()->createBlock('ProductReturn/Admin_' . $block_path);
            $content  = $block->getCsv();
            print_r($content);
            $this->_prepareDownloadResponse($fileName, $content);

        } catch (Exception $e) {

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    public function printAction()
    {
        try {
            $rsrId = $this->getRequest()->getParam('rsr_id');
            $rsr   = Mage::getModel('ProductReturn/SupplierReturn')->load($rsrId);
            $obj   = mage::getModel('ProductReturn/Pdf_SupplierReturn');
            $pdf   = $obj->getPdf(array($rsr));
            $this->_prepareDownloadResponse(mage::helper('ProductReturn')->__('Supplier Return #') . $rsr->getrsr_id() . '.pdf', $pdf->render(), 'application/pdf');
        } catch (Exception $ex) {
            die("An error occured : " . $ex->getMessage());
        }
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        /*
         * TAB GENERAL
         */
        $rsr = mage::getModel('ProductReturn/SupplierReturn')->load($data['data']['rsr_id']);
        foreach ($data['data'] as $key => $value) {
            if ($data['data'][$key] != $rsr->getData($key))
                $rsr->setData($key, $value);
        }
        $rsr->save();

        //TAB PRODUCTS
        foreach ($data['edit_comment'] as $k => $v) {
            $rsrp = Mage::getModel('ProductReturn/SupplierReturn_Product')->load($k);
            if ($rsrp->getrsrp_comments() != $v) {
                $rsrp->setrsrp_comments($v)->save();
            }
        }


        //TAB SEND TO SUPPLIER
        if ($data['send_to_supplier'] == '1') {
            if ($rsr->getSupplier()->getsup_rma_mail() == null) {
                Mage::getSingleton("adminhtml/session")->addError($this->__('Supplier email is not defined, unable to send mail.'));
            } else {
                $rsr->sendToSupplier($data['data']['mail_content']);

                if ($data['data']['change_status'] == 'on') {
                    if ($rsr->getrsr_status() == 'new') {
                        $rsr->setrsr_status('inquiry')->save();
                    }
                }
                Mage::getSingleton("adminhtml/session")->addSuccess($this->__('Email has been sent'));
            }
            $this->_redirectReferer();
        }

        Mage::getSingleton("adminhtml/session")->addSuccess($this->__('Data saved'));
        $this->_redirectReferer();
    }

    public function gridAjaxAction()
    {
        $block = $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Edit_Tab_ProductsGrid');
        $block->setRsrId($this->getRequest()->getParam('rsr_id'));
        $this->getResponse()->setBody($block->toHtml());
    }
}