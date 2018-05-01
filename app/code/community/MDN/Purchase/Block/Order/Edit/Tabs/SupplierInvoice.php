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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Block_Order_Edit_Tabs_SupplierInvoice extends Mage_Adminhtml_Block_Widget_Grid {

    private $_order = null;

    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/supplier_invoices/create')) {
            $this->setChild('new_invoice_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('adminhtml')->__('New Invoice'),
                        'onclick' => 'showSupplierInvoiceWindows(\'' . $this->getNewInvoiceUrl() . '\', \'' . $this->__('New') . '\');',
                        'level' => -1,
                        'class' => 'save'
                    ))
            );
            $html .= $this->getChildHtml('new_invoice_button');
        }

        return $html;
    }

    public function getNewInvoiceUrl() {
        return $this->getUrl('adminhtml/Purchase_SupplierInvoice/New', array('po_num' => $this->getPurchaseOrderId()));
    }
    
    public function setOrderId($id) {
        $this->_order = mage::getModel('Purchase/Order')->load($id);
        return $this;
    }

    public function getPurchaseOrder() {
        return $this->_order;
    }

    public function getPurchaseOrderId(){
        return $this->getPurchaseOrder()->getpo_num();
    }

    public function __construct(){
        parent::__construct();
        $this->setId('SupplierInvoiceGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setVarNameFilter('supplier_invoice');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        $this->setDefaultLimit(100);
        $this->setSaveParametersInSession(true);

    }

    /**
     * Load collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Purchase/PurchaseSupplierInvoice')
            ->getPurchaseOrderSupplierInvoicesCollection($this->getPurchaseOrderId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('psi_invoice_id', array(
            'header'=> Mage::helper('purchase')->__('Invoice Id'),
            'index' => 'psi_invoice_id',
            'sortable'	=> true
        ));

        $this->addColumn('psi_date', array(
            'header'=> Mage::helper('purchase')->__('Date'),
            'type' => 'date',
            'index' => 'psi_date',
            'sortable'	=> true
        ));

        $this->addColumn('psi_due_date', array(
            'header'=> Mage::helper('purchase')->__('Due date'),
            'type' => 'date',
            'index' => 'psi_due_date',
            'sortable'	=> true
        ));

        $this->addColumn('psi_payment_date', array(
            'header'=> Mage::helper('purchase')->__('Payment date'),
            'type' => 'date',
            'index' => 'psi_payment_date',
            'sortable'	=> true
        ));

        $this->addColumn('psi_amount', array(
            'header'=> Mage::helper('purchase')->__('Amount'),
            'type' => 'number',
            'align' => 'center',
            'index' => 'psi_amount',
            'sortable'	=> true
        ));

        $this->addColumn('psi_status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'width' => '80px',
            'align' => 'center',
            'sortable'	=> true,
            'index' => 'psi_status',
            'filter' => 'MDN_Purchase_Block_Widget_Column_Filter_SupplierInvoice_SupplierInvoiceStatus',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_SupplierInvoiceStatus'
        ));

        $this->addColumn('psi_attachment', array(
            'header'=> Mage::helper('purchase')->__('File'),
            'width' => '80px',
            'align' => 'center',
            'sortable'	=> true,
            'index' => 'psi_status',
            'filter' => false,
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_SupplierInvoiceAttachment'
        ));

        if(Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/supplier_invoices/edit')) {
            $this->addColumn('edit', array(
                'header' => Mage::helper('purchase')->__('Edit'),
                'index' => 'po_order_id',
                'width' => '80px',
                'align' => 'center',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_Edit',
                'sortable'	=> false,
                'filter'	=> false,
                'is_system' => true
            ));
        }

        if(Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/supplier_invoices/delete')) {
            $this->addColumn('delete', array(
                'header' => Mage::helper('purchase')->__('Delete'),
                'index' => 'po_order_id',
                'width' => '80px',
                'align' => 'center',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_Delete',
                'sortable'	=> false,
                'filter'	=> false,
                'is_system' => true
            ));
        }

        $this->addExportType('adminhtml/Purchase_SupplierInvoice/exportCsv', Mage::helper('customer')->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }


    public function getRowUrl($row) {
        //nothing to enable click on lines
    }

}
