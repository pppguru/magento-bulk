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
class MDN_AdvancedStock_Block_MassStockEditor_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('MassStockEditorGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('AdvancedStock')->__('No Items'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $collection = mage::getResourceModel('AdvancedStock/MassStockEditor_Collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * Columns grid definition
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
            $this->addColumn('manufacturer', array(
                'header' => Mage::helper('AdvancedStock')->__('Manufacturer'),
                'index' => $manufacturerCode,
                'type' => 'options',
                'options' => mage::helper('AdvancedStock/Product_Base')->getManufacturerListForFilter(),
            ));
        }

        $this->addColumn('sku', array(
            'header' => Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku'
        ));


        $this->addColumn('barcode', array(
            'header' => Mage::helper('AdvancedStock')->__('Barcode'),
            'index' => 'product_id',
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_Barcode',
            'filter' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_Barcode',
            'sortable' => false
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'name'
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this, 'product_id');

        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('AdvancedStock')->__('Enabled'),
                '2' => Mage::helper('AdvancedStock')->__('Disabled')
            )
        ));

        $this->addColumn('stock_id', array(
            'header' => Mage::helper('AdvancedStock')->__('Warehouse'),
            'width' => '80',
            'index' => 'stock_id',
            'type' => 'options',
            'options' => Mage::getSingleton('AdvancedStock/System_Config_Source_Warehouse')->getListForFilter(),
        ));

        $this->addColumn('stock', array(
            'header' => Mage::helper('AdvancedStock')->__('Stock'),
            'index' => 'qty',
            'renderer' => 'MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_Stock',
            'align' => 'center',
            'type' => 'number'
        ));

        $this->addColumn('warning_stock_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Warning stock level'),
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_WarningStockLevel',
            'align' => 'center'
        ));

        $this->addColumn('ideal_stock_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Ideal stock level'),
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_IdealStockLevel',
            'align' => 'center'
        ));

        $this->addColumn('shelf_location', array(
            'header' => Mage::helper('AdvancedStock')->__('Shelf location'),
            'index' => 'shelf_location',
            'renderer' => 'MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_StockLocation',
            'align' => 'center'
        ));
        
        //raise event to allow other modules to add columns
        Mage::dispatchEvent('advancedstock_masstockeditor_grid_preparecolumns', array('grid'=>$this));

        $this->addColumn('action', array(
            'header' => Mage::helper('AdvancedStock')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getproduct_id',
            'actions' => array(
                array(
                    'caption' => Mage::helper('AdvancedStock')->__('View'),
                    'url' => array('base' => 'adminhtml/AdvancedStock_Products/Edit'),
                    'field' => 'product_id',
                    'target' => '_blank'
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

    public function getGridUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Misc/MassStockEditorAjax', array('_current' => true));
    }

}
