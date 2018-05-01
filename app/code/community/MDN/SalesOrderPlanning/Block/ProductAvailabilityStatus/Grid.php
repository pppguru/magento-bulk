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
class MDN_SalesOrderPlanning_Block_ProductAvailabilityStatus_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ProductAvailabilityStatusGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setVarNameFilter('product_availability_status');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));

        $this->setDefaultSort('pa_id', 'desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')
                ->getCollection()
                ->join('catalog/product', '`catalog/product`.entity_id=pa_product_id')
                ->join('cataloginventory/stock_item', '`catalog/product`.entity_id=product_id and stock_id = 1')
                ->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and store_id = 0 and attribute_id = ' . mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('pa_id', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Id'),
            'index' => 'pa_id',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('pa_product_id', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Product Id'),
            'index' => 'pa_product_id',
            'type'  => 'number'
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Sku'),
            'index' => 'sku',
        ));

        $this->addColumn('value', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Product'),
            'index' => 'value',
        ));

        $this->addColumn('pa_available_qty', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Available Qty'),
            'index' => 'pa_available_qty',
            'align' => 'center',
            'type'  => 'number'
        ));

        $this->addColumn('pa_supply_delay', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Supply Delay'),
            'index' => 'pa_supply_delay',
            'align' => 'center',
            'type'  => 'number'
        ));

        $this->addColumn('pa_allow_backorders', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Allow backorders'),
            'index' => 'pa_allow_backorders',
            'type' => 'options',
            'options' => array(
                '2' => Mage::helper('catalog')->__('Yes with notification'),
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('pa_backinstock_date', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Supply date'),
            'index' => 'pa_backinstock_date',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('outofstock_period', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Out of stock Period'),
            'index' => 'outofstock_period',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_OutOfStockPeriod',
            'align' => 'center'
        ));

        $this->addColumn('pa_is_saleable', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Is Saleable'),
            'index' => 'pa_is_saleable',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('pa_status', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Status'),
            'filter' => false,
            'sortable' => false,
            'index' => 'pa_status',
            'type' => 'options',
            'options' => mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->getStatuses()
        ));

        $this->addColumn('is_in_stock', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Magento<br>Status'),
            'index' => 'is_in_stock',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('available_date', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Available date'),
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_AvailableDate',
            'align' => 'center'
        ));

        $this->addColumn('message', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Message'),
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_Message',
            'align' => 'center'
        ));

        $this->addColumn('view_product', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('View'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getpa_product_id',
            'actions' => array(
                array(
                    'caption' => Mage::helper('SalesOrderPlanning')->__('View'),
                    'url' => array('base' => 'adminhtml/AdvancedStock_Products/Edit'),
                    'field' => 'product_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addColumn('refresh', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Refresh'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getpa_product_id',
            'actions' => array(
                array(
                    'caption' => Mage::helper('SalesOrderPlanning')->__('Refresh'),
                    'url' => array('base' => 'adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/RefreshProduct'),
                    'field' => 'product_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Return url to refresh all product availability statuses
     */
    public function getRefreshUrl() {
        return $this->getUrl('adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/RefreshAll');
    }

    /**
     * Return url to refresh only missing product availability statuses
     */
    public function getRefreshMissingUrl() {
        return $this->getUrl('adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/RefreshOnlyMissing');
    }
    
    protected function _prepareMassaction() {

        $this->setMassactionIdField('pa_product_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('products', array(
            'label' => Mage::helper('SalesOrderPlanning')->__('Refresh only selected'),
            'url' => $this->getUrl('*/*/RefreshOnlySelected/products/', array('_current' => true))
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('SalesOrderPlanning')->__('Delete'),
            'url' => $this->getUrl('*/*/MassDelete', array('_current' => true))
        ));

        $this->getMassactionBlock()->addItem('force_in_stock', array(
            'label' => Mage::helper('SalesOrderPlanning')->__('Force stock status to in_stock'),
            'url' => $this->getUrl('*/*/ForceInStock', array('_current' => true))
        ));

        return $this;
    }

}
