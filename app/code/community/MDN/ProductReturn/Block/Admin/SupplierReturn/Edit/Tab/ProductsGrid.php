<?php

class MDN_ProductReturn_Block_Admin_SupplierReturn_Edit_Tab_ProductsGrid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $_rsrId = null;

    public function setRsrId($rsr)
    {
        $this->_rsrId = $rsr;

        return $this;
    }

    public function getRsrId()
    {
        return $this->_rsrId;
    }

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Item Found'));
        $this->setUseAjax(true);
        $this->setDefaultSort('rsrp_product_name', 'asc');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $collection = mage::getModel('ProductReturn/SupplierReturn_Product')->getCollection()->addFieldToFilter('rsrp_rsr_id', $this->getRsrId());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rsrp_product_id', array(
            'header' => Mage::helper('ProductReturn')->__('Product ID'),
            'index'  => 'rsrp_product_id',
            'width'  => '100px'
        ));

        $this->addColumn('rsrp_product_sku', array(
            'header'   => Mage::helper('ProductReturn')->__('Product SKU'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSku',
            'width'    => '100px'
        ));

        $this->addColumn('product_supplier_reference', array(
            'header'   => Mage::helper('ProductReturn')->__('Product Supplier SKU'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSupplierSku'
        ));

        $this->addColumn('rsr_product_name', array(
            'header' => Mage::helper('ProductReturn')->__('Product Name'),
            'index'  => 'rsrp_product_name'
        ));

        $this->addColumn('rsrp_serial', array(
            'header' => Mage::helper('ProductReturn')->__('Product Serial'),
            'index'  => 'rsrp_serial'
        ));

        $this->addColumn('rsrp_po_id', array(
            'header'   => Mage::helper('ProductReturn')->__('Purchase Order'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnPopId'
        ));

        $this->addColumn('rsrp_comments', array(
            'header'   => Mage::helper('ProductReturn')->__('Comments'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnEditComments',
            'width'    => '100px'
        ));

        $this->addColumn('rsrp_status', array(
            'header' => Mage::helper('ProductReturn')->__('Status'),
            'index'  => 'rsrp_status'
        ));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridAjax', array('rsr_id' => $this->getRsrId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rsrp_id');
        $this->getMassactionBlock()->setFormFieldName('rsrp_ids');

        $this->getMassactionBlock()->addItem('process_status_refunded', array(
            'label' => Mage::helper('ProductReturn')->__('Change products status to Refunded'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassProcess', array('status' => MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusRefunded, 'rsr_id' => $this->getRsrId())),
        ));

        $this->getMassactionBlock()->addItem('process_status_standard_exchange', array(
            'label' => Mage::helper('ProductReturn')->__('Change products status to Standard Exchange'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassProcess', array('status' => MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusStandardExchange, 'rsr_id' => $this->getRsrId())),
        ));

        $this->getMassactionBlock()->addItem('process_status_creditmemo', array(
            'label' => Mage::helper('ProductReturn')->__('Change products status to Credit Memo'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassProcess', array('status' => MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusCreditMemo, 'rsr_id' => $this->getRsrId())),
        ));

        $this->getMassactionBlock()->addItem('process_status_destroy', array(
            'label' => Mage::helper('ProductReturn')->__('Change products status to Destroy'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassProcess', array('status' => MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusDestroy, 'rsr_id' => $this->getRsrId())),
        ));

        $this->getMassactionBlock()->addItem('remove_from_rsr', array(
            'label' => Mage::helper('ProductReturn')->__('Remove products from this supplier return'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassRemoveProductsFromRsr', array('rsr_id' => $this->getRsrId())),
        ));

        return $this;
    }

}
    