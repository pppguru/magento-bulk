<?php

class MDN_ProductReturn_Block_Admin_SupplierReturn_Tabs_TabCompleteGrid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('TabCompleteGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Item Found'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::helper('ProductReturn/SupplierReturn')->getCompleteSupplierReturns();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rsr_id', array(
            'header' => Mage::helper('ProductReturn')->__('ID'),
            'index'  => 'rsr_id',
            'width'  => '100px'
        ));

        $this->addColumn('rsr_supplier', array(
            'header'  => Mage::helper('ProductReturn')->__('Supplier'),
            'index'   => 'rsr_supplier_id',
            'type'    => 'options',
            'options' => $this->getSupplierOptions()
        ));

        $this->addColumn('rsr_created_at', array(
            'header' => Mage::helper('ProductReturn')->__('Created at'),
            'index'  => 'rsr_created_at',
            'type'   => 'date'
        ));

        $this->addColumn('rsr_status_set_to_inquiry_at', array(
            'header'   => Mage::helper('ProductReturn')->__('Inquiry at'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnStatusInquiry',
            'type'     => 'date'
        ));

        $this->addColumn('rsr_status_set_to_sent_at', array(
            'header'   => Mage::helper('ProductReturn')->__('Sent to supplier at'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnStatusSent',
            'type'     => 'date'
        ));

        $this->addColumn('rsr_reference', array(
            'header' => Mage::helper('ProductReturn')->__('Reference'),
            'index'  => 'rsr_reference'
        ));

        $this->addColumn('rsr_supplier_reference', array(
            'header' => Mage::helper('ProductReturn')->__('Supplier Reference'),
            'index'  => 'rsr_supplier_reference'
        ));

        $this->addColumn('rsr_comments', array(
            'header' => Mage::helper('ProductReturn')->__('Comments'),
            'index'  => 'rsr_comments'
        ));

        $this->addColumn('rsr_purchase_price', array(
            'header'   => Mage::helper('ProductReturn')->__('Purchase Price'),
            'filter'   => false,
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnPurchaseTotalPrice'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('ProductReturn')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('ProductReturn')->__('View'),
                        'url'     => array('base' => 'ProductReturn/SupplierReturn/edit/'),
                        'field'   => 'rsr_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        $this->addExportType('adminhtml/ProductReturn_SupplierReturn/exportCsv/type/SupplierReturnComplete', Mage::helper('customer')->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/ProductReturn_SupplierReturn/edit', array('rsr_id' => $row->getrsr_id()));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

    public function getSupplierOptions()
    {
        $retour = array();

        //charge la liste des pays
        $collection = Mage::getModel('Purchase/Supplier')
            ->getCollection()
            ->setOrder('sup_name', 'asc');
        foreach ($collection as $item) {
            $retour[$item->getsup_id()] = $item->getsup_name();
        }

        return $retour;
    }

}
    