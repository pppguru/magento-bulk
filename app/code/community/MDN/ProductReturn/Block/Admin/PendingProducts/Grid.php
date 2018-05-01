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
class MDN_ProductReturn_Block_Admin_PendingProducts_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('PendingProductsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {
        $collection = Mage::helper('ProductReturn/PendingProducts')->getList();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('rma_ref', array(
            'header'   => Mage::helper('ProductReturn')->__('Customer Return'),
            'index'    => 'rma_ref',
            'renderer' => 'MDN_ProductReturn_Block_Admin_PendingProducts_Widget_Grid_Column_Renderer_Rma'
        ));

        $this->addColumn('product', array(
            'header' => Mage::helper('ProductReturn')->__('Product'),
            'index'  => 'rp_product_name',
        ));

        $this->addColumn('rp_qty', array(
            'header' => Mage::helper('ProductReturn')->__('Qty'),
            'index'  => 'rp_qty',
            'type'   => 'number'
        ));

        $this->addColumn('reason', array(
            'header'  => Mage::helper('ProductReturn')->__('Reason'),
            'index'   => 'rp_reason'
        ));

        $this->addColumn('rma_reception_date', array(
            'header' => Mage::helper('ProductReturn')->__('Received on'),
            'index'  => 'rma_reception_date',
            'type'   => 'date'
        ));

        $this->addColumn('rp_destination', array(
            'header'  => Mage::helper('ProductReturn')->__('Destination'),
            'index'   => 'rp_destination',
            'type'    => 'options',
            'options' => mage::getModel('ProductReturn/RmaProducts')->getDestinations()
        ));

        $this->addColumn('rp_description', array(
            'header' => Mage::helper('ProductReturn')->__('Comments'),
            'index'  => 'rp_description',
        ));

        return parent::_prepareColumns();
    }


    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

    /**
     * Manage mass actions
     *
     * @return unknown
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->setMassactionIdField('rp_id');
        $this->getMassactionBlock()->setFormFieldName('pending_products');

        $this->getMassactionBlock()->addItem('print', array(
            'label' => Mage::helper('ProductReturn')->__('Print'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_PendingProducts/Print')
        ));
        $this->getMassactionBlock()->addItem('set_as_processed', array(
            'label' => Mage::helper('ProductReturn')->__('Set as processed'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_PendingProducts/SetAsProcessed')
        ));
        $this->getMassactionBlock()->addItem('print_and_set_as_processed', array(
            'label' => Mage::helper('ProductReturn')->__('Print and set as processed'),
            'url'   => $this->getUrl('adminhtml/ProductReturn_PendingProducts/PrintProcessed')
        ));

        return $this;
    }
}
