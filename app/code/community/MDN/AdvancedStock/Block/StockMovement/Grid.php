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
class MDN_AdvancedStock_Block_StockMovement_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('StockMovementGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setDefaultSort('sm_date', 'desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        //charge
        $collection = mage::getModel('AdvancedStock/StockMovement')
                ->getCollection()
                ->join('catalog/product', 'sm_product_id=`catalog/product`.entity_id')
                ->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and store_id = 0 and attribute_id = ' . mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Defini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('sm_date', array(
            'header' => Mage::helper('AdvancedStock')->__('Date'),
            'index' => 'sm_date',
            'type' => 'datetime',
            'sortable' => true
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku',
            'sortable' => true
        ));

        $this->addColumn('value', array(
            'header' => Mage::helper('AdvancedStock')->__('Product'),
            'index' => 'value',
            'sortable' => true
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this, 'sm_product_id');

        $this->addColumn('sm_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Qty'),
            'index' => 'sm_qty',
            'align' => 'center',
            'type' => 'number',
            'sortable' => true
        ));

        $this->addColumn('picto', array(
            'header' => '',
            'index' => 'sm_id',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'is_system' => true,
            'renderer' => 'MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_Picto',
        ));

        $this->addColumn('sm_source_stock', array(
            'header' => Mage::helper('AdvancedStock')->__('From warehouse'),
            'index' => 'sm_source_stock',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align' => 'center'
        ));

        $this->addColumn('sm_target_stock', array(
            'header' => Mage::helper('AdvancedStock')->__('To warehouse'),
            'index' => 'sm_target_stock',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align' => 'center'
        ));

        $this->addColumn('sm_type', array(
            'header' => Mage::helper('AdvancedStock')->__('Type'),
            'index' => 'sm_type',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/StockMovement')->GetTypes(),
            'align' => 'center'
        ));

        $this->addColumn('sm_description', array(
            'header' => Mage::helper('AdvancedStock')->__('Description'),
            'index' => 'sm_description',
            'sortable' => true
        ));

        $this->addColumn('sm_user', array(
            'header' => Mage::helper('AdvancedStock')->__('User'),
            'index' => 'sm_user',
            'sortable' => true,
            'align' => 'right',
        ));

        if (Mage::getStoreConfig('advancedstock/general/log_adjustment_stock_movement'))
        {
            $this->addColumn('log', array(
                'header' => Mage::helper('AdvancedStock')->__('Log'),
                'index' => 'sm_id',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'renderer' => 'MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_Log',
            ));
        }


        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/stockmovement_grid/delete'))
        {
            $this->addColumn('delete', array(
                'header' => Mage::helper('AdvancedStock')->__('Delete'),
                'index' => 'sm_id',
                'type' => 'options',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_DeleteStockMovement',
            ));
        }

        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getRowUrl($row) {
        //nothing
    }

}
