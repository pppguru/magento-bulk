<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs_Differences extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('InventoryDifferences');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setUseAjax(true);
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $inventory = Mage::registry('current_inventory');
        
        $collection = $inventory->getDifferences();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('sku', array(
            'header'=> Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku'
        ));

        $this->addColumn('barcode', array(
            'header' => Mage::helper('AdvancedStock')->__('Barcode'),
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_Barcode',
            'filter' => 'AdvancedStock/Product_Widget_Grid_Column_Filter_Barcode',
            'align' => 'center',
            'entity' => 'product',
            'sort' => false,
            'index' => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'=> Mage::helper('AdvancedStock')->__('Product'),
            'index' => 'name'
        ));

        if(Mage::registry('current_inventory')->isLocationScanAllowed()){
            $this->addColumn('eisp_shelf_location', array(
                'header'=> Mage::helper('AdvancedStock')->__('Location'),
                'index' => 'eisp_shelf_location'
            ));
        }
        
        $this->addColumn('eip_qty', array(
            'header'=> Mage::helper('AdvancedStock')->__('Scanned qty'),
            'index' => 'eip_qty',
            'type' => 'number'
        ));
        
        $this->addColumn('eisp_stock', array(
            'header'=> Mage::helper('AdvancedStock')->__('Expected qty'),
            'index' => 'eisp_stock',
            'type' => 'number'
        ));

        $this->addColumn('eisp_shelf_location', array(
            'header'=> Mage::helper('AdvancedStock')->__('Location'),
            'index' => 'eisp_shelf_location',
        ));

        $inventory = Mage::registry('current_inventory');
        $url = '*/*/exportCsvDifferences/ei_id/'.$inventory->getId();
        $this->addExportType($url, Mage::helper('AdvancedStock')->__('CSV'));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        $inventory = Mage::registry('current_inventory');
        return $this->getUrl('adminhtml/AdvancedStock_Inventory/AjaxDifferences', array('ei_id' => $inventory->getId()));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}
