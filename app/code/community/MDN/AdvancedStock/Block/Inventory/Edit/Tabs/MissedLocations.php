<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs_MissedLocations extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('InventoryMissedLocations');
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
        
        $collection = $inventory->getMissedLocations();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('eisp_shelf_location', array(
            'header'=> Mage::helper('AdvancedStock')->__('Location'),
            'index' => 'eisp_shelf_location'
        ));

        $this->addColumn('product_count', array(
            'header'=> Mage::helper('AdvancedStock')->__('Product count'),
            'index' => 'product_count',
            'type' => 'number'
        ));

        $inventory = Mage::registry('current_inventory');
        $url = '*/*/exportCsvMissedLocations/ei_id/'.$inventory->getId();
        $this->addExportType($url, Mage::helper('AdvancedStock')->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        $inventory = Mage::registry('current_inventory');
        return $this->getUrl('adminhtml/AdvancedStock_Inventory/AjaxMissedLocations', array('ei_id' => $inventory->getId()));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}
