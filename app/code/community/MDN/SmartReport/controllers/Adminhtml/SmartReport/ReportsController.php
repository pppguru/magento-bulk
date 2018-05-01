<?php

class MDN_SmartReport_Adminhtml_SmartReport_ReportsController extends Mage_Adminhtml_Controller_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        //overrides POST value with GET values if exist
        $post = $this->getRequest()->getPost('smartreport');
        $get = $this->getRequest()->getParams();
        if (!$post)
            $post = array();
        if (!$get)
            $get = array();

        $vars = array_merge($post, $get);
        Mage::helper('SmartReport')->updateVariables($vars);

        if (!Mage::getStoreConfig('smartreport/filters/order_statuses'))
        {
            $url = $this->getUrl('adminhtml/system_config/edit', array('section' => 'smartreport'));
            Mage::getSingleton('adminhtml/session')->addError($this->__('Filters for order statuses are not set, please configure it in the %sconfiguration%s', '<a href="'.$url.'">', '</a>'));
        }

        return $this;
    }

    public function DashboardAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ExtractAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ExtractDetailsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ExtractDetailsExcelAction()
    {
        $report = Mage::getModel('SmartReport/Report')->getReportById($this->getRequest()->getParam('report_id'));
        $report->setVariables(Mage::helper('SmartReport')->getVariables());

        $fileName = $report->getName().'details .xls';

        $collection = $report->getReportDetails();

        $excel = Mage::helper('SmartReport/Excel')->fromArray($collection);

        $this->_prepareDownloadResponse($fileName, $excel, 'application/vnd.ms-excel');
    }

    public function ExtractExcelAction()
    {
        $report = Mage::getModel('SmartReport/Report')->getReportById($this->getRequest()->getParam('report_id'));
        $report->setVariables(Mage::helper('SmartReport')->getVariables());

        $fileName = $report->getName().'.xls';

        $collection = $report->getReportDatas();

        $excel = Mage::helper('SmartReport/Excel')->fromArray($collection);

        $this->_prepareDownloadResponse($fileName, $excel, 'application/vnd.ms-excel');
    }


    public function CustomerAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function CustomerDetailAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ReviewsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function PurchaseAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function OrderPreparationAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function InventoryAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function PaymentMethodAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ShippingMethodAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function RefundAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function BestSellerAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function InvoiceAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function SkuDetailAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function SkuDetailAjaxAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('SmartReport/Report_Type_Sku');
        $block->setTemplate('SmartReport/Report/Type.phtml');
        $this->getResponse()->setBody($block->toHtml());

    }

    public function SupplierDetailAjaxAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('SmartReport/Report_Type_Supplier');
        $block->setTemplate('SmartReport/Report/Type.phtml');
        $block->setAjaxMode(1);
        $this->getResponse()->setBody($block->toHtml());

    }

    public function CustomerDetailAjaxAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('SmartReport/Report_Type_Customer');
        $block->setTemplate('SmartReport/Report/Type.phtml');
        $block->setAjaxMode(1);
        $this->getResponse()->setBody($block->toHtml());

    }

    public function CategoryAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ManufacturerAction()
    {
        if (!Mage::helper('SmartReport')->getManufacturerAttributeCode())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Manufacturer attribute is not configured'));
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'smartreport'));
        }
        else
        {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function ManufacturerDetailAction()
    {
        if (!Mage::helper('SmartReport')->getManufacturerAttributeCode())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Manufacturer attribute is not configured'));
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'smartreport'));
        }
        else
        {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function CategoryDetailAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function CountryDetailAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function CouponCodeAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function CouponCodeDetailsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return (Mage::getSingleton('admin/session')->isAllowed('admin/erp/smartreport') || Mage::getSingleton('admin/session')->isAllowed('admin/smartreport'));
    }

}