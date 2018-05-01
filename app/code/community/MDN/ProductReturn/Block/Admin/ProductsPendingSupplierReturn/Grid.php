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
class MDN_ProductReturn_Block_Admin_ProductsPendingSupplierReturn_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductsPendingSupplierReturnGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
        $this->setDefaultSort('rsrp_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }


    /**
     * Charge la collection des produits en attentes
     */
    protected function _prepareCollection()
    {
        $collection = Mage::helper('ProductReturn/SupplierReturn')->getProductsPending();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * Définit les colonnes du grip
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('rsrp_id', array(
            'header' => Mage::helper('ProductReturn')->__('ID'),
            'index'  => 'rsrp_id',
            'width'  => '100px'
        ));

        $this->addColumn('rsrp_product_sku', array(
            'header'   => Mage::helper('ProductReturn')->__('SKU'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSku'
        ));

        $this->addColumn('rsrp_product_name', array(
            'header' => mage::helper('ProductReturn')->__('Product Name'),
            'index'  => 'rsrp_product_name'
        ));

        $this->addColumn('rsrp_serial', array(
            'header' => mage::helper('ProductReturn')->__('Product Serial'),
            'index'  => 'rsrp_serial'
        ));

        $this->addColumn('rsrp_supplier_name', array(
            'header'  => Mage::helper('ProductReturn')->__('Supplier Name'),
            'index'   => 'rsrp_sup_id',
            'type'    => 'options',
            'options' => $this->getSuppliersAsArray()
        ));

        $this->addColumn('product_supplier_reference', array(
            'header'   => Mage::helper('ProductReturn')->__('Product Supplier SKU'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSupplierSku'
        ));


        $this->addColumn('rsrp_supplier_informations', array(
            'header'   => Mage::helper('ProductReturn')->__('Supplier Informations'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSupplierInformations',
            'filter'   => false
        ));

        $this->addColumn('rsrp_creation_date', array(
            'header' => Mage::helper('ProductReturn')->__('Creation date'),
            'index'  => 'rsrp_creation_date',
            'type'   => 'date'
        ));

        $this->addColumn('rsrp_comments', array(
            'header' => Mage::helper('ProductReturn')->__('Comments'),
            'index'  => 'rsrp_comments'
        ));


        $this->addColumn('action',
            array(
                'width'    => '80px',
                'type'     => 'action',
                'getter'   => 'getrsrp_id',
                'actions'  => array(
                    array(
                        'caption' => Mage::helper('ProductReturn')->__('Edit'),
                        'url'     => array('base' => 'ProductReturn/ProductsPendingSupplierReturn/Edit'),
                        'field'   => 'rsrp_id'
                    )
                ),
                'filter'   => false,
                'sortable' => false
            ));

        $this->addExportType('adminhtml/ProductReturn_ProductsPendingSupplierReturn/exportCsv/', Mage::helper('customer')->__('CSV'));

        return parent::_prepareColumns();
    }


    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rsrp_id');
        $this->getMassactionBlock()->setFormFieldName('rsrp_ids');

        $this->getMassactionBlock()->addItem('create_supplierReturn', array(
            'label' => Mage::helper('ProductReturn')->__('Create Supplier Return'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassCreateSupplierReturn'),
        ));

        $this->getMassactionBlock()->addItem('destroy_product', array(
            'label' => Mage::helper('ProductReturn')->__('Destroy Product'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassDestroyProducts'),
        ));

        $this->getMassactionBlock()->addItem('assign_supplier', array(
            'label'      => Mage::helper('sales')->__('Assign a supplier'),
            'url'        => $this->getUrl('adminhtml/ProductReturn_ProductsPendingSupplierReturn/MassAssignSupplier'),
            'additional' => array(
                'supplier' => array(
                    'name'   => 'supplier',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('catalog')->__('Supplier'),
                    'values' => $this->getSuppliersAsArray()
                )
            )
        ));

        return $this;
    }


    public function getSuppliersAsArray()
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