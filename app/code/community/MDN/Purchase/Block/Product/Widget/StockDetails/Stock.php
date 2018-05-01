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
class MDN_Purchase_Block_Product_Widget_StockDetails_Stock extends Mage_Adminhtml_Block_Widget_Grid {

    //product get / set
    private $_product = null;

    public function setProduct($product) {
        $this->_product = $product;
    }

    public function getProduct() {
        return $this->_product;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('ProductStocksGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setDefaultSort('stock_id', 'asc');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Load collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        //charge
        $collection = mage::helper('AdvancedStock/Product_Base')->getStocks($this->getProduct()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('stock_name', array(
            'header' => Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'stock_name',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Qty'),
            'index' => 'qty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'type' => 'number'
        ));

        $this->addColumn('AvailableQty', array(
            'header' => Mage::helper('AdvancedStock')->__('Available Qty'),
            'index' => 'AvailableQty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_AvailableQty'
        ));

        $this->addColumn('prefered_stock_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Warning stock level'),
            'index' => 'prefered_stock_level',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_PreferedStockLevelReadonly'
        ));

        $this->addColumn('ideal_stock_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Ideal stock level'),
            'index' => 'ideal_stock_level',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_IdealStockLevelReadonly'
        ));

        $this->addColumn('stock_ordered_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Ordered Qty'),
            'index' => 'stock_ordered_qty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center'
        ));

        $this->addColumn('needed_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Needed Qty'),
            'index' => 'needed_qty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_NeededQty'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'status',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockStatus'
        ));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}

