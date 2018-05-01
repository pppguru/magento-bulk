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
class MDN_AdvancedStock_Block_Product_Stocks extends Mage_Adminhtml_Block_Widget_Grid {

    private $_productId = null;
    private $_readOnlyMode = false;

    public function setProductId($productId) {
        $this->_productId = $productId;
        return $this;
    }

    /**
     * Used to disable control box in shelf location column (read only mode)
     */
    public function setReadOnlyMode() {
        $this->_readOnlyMode = true;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('ProductStocksGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setDefaultSort('stock_id', 'asc');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Load product stock collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = mage::helper('AdvancedStock/Product_Base')->getStocksToDisplay($this->_productId);
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
            'header' => Mage::helper('AdvancedStock')->__('Warehouse'),
            'index' => 'stock_name',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Physical Qty'),
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

        $this->addColumn('stock_reserved_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Reserved Qty'),
            'index' => 'stock_reserved_qty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center'
        ));

        $this->addColumn('prefered_stock_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Warning stock level'),
            'index' => 'prefered_stock_level',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_PreferedStockLevel',
            'read_only' => $this->_readOnlyMode
        ));


        $this->addColumn('ideal_stock_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Ideal stock level'),
            'index' => 'ideal_stock_level',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_IdealStockLevel',
            'read_only' => $this->_readOnlyMode
        ));

        $this->addColumn('stock_ordered_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Ordered Qty'),
            'index' => 'stock_ordered_qty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderedQty'
        ));

        $this->addColumn('needed_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Needed Qty'),
            'index' => 'needed_qty',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_NeededQty'
        ));

        if ($this->_readOnlyMode) {
            $this->addColumn('shelf_location', array(
                'header' => Mage::helper('AdvancedStock')->__('Shelf<br>Location'),
                'index' => 'shelf_location',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
            ));
        } else {
            $this->addColumn('shelf_location', array(
                'header' => Mage::helper('AdvancedStock')->__('Shelf<br>Location'),
                'index' => 'shelf_location',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_ShelfLocation'
            ));
        }

        if ($this->_readOnlyMode) {
            $this->addColumn('is_favorite_warehouse', array(
                'header' => Mage::helper('AdvancedStock')->__('Favorite<br>Warehouse'),
                'index' => 'is_favorite_warehouse',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'type' => 'options',
                'options' => array(
                    '1' => Mage::helper('AdvancedStock')->__('Yes'),
                    '0' => Mage::helper('AdvancedStock')->__('No'),
                )
            ));
        } else {
            $this->addColumn('is_favorite_warehouse', array(
                'header' => Mage::helper('AdvancedStock')->__('Favorite<br>Warehouse'),
                'index' => 'is_favorite_warehouse',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_FavoriteWarehouse'
            ));
        }
        
        if ($this->_readOnlyMode) {
            $this->addColumn('erp_exclude_automatic_warning_stock_level_update', array(
                'header' => Mage::helper('AdvancedStock')->__('Exclude from<br>Auto update'),
                'index' => 'erp_exclude_automatic_warning_stock_level_update',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'type' => 'options',
                'options' => array(
                    '1' => Mage::helper('AdvancedStock')->__('Yes'),
                    '0' => Mage::helper('AdvancedStock')->__('No'),
                )
            ));
        } else {
            $this->addColumn('erp_exclude_automatic_warning_stock_level_update', array(
                'header' => Mage::helper('AdvancedStock')->__('Exclude from<br>Auto update'),
                'index' => 'erp_exclude_automatic_warning_stock_level_update',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_AutoUpdate'
            ));
        }

        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'status',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockStatus'
        ));

        if (!$this->_readOnlyMode) {

            $this->addColumn('action',
                    array(
                        'header' => Mage::helper('AdvancedStock')->__('Action'),
                        'width' => '50px',
                        'type' => 'action',
                        'getter' => 'getstock_id',
                        'actions' => array(
                            array(
                                'caption' => Mage::helper('AdvancedStock')->__('View'),
                                'url' => array('base' => 'adminhtml/AdvancedStock_Warehouse/Edit'),
                                'field' => 'stock_id'
                            )
                        ),
                        'filter' => false,
                        'sortable' => false,
                        'is_system' => true,
                        'align' => 'center'
            ));
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getRowUrl($row) {

    }

}
