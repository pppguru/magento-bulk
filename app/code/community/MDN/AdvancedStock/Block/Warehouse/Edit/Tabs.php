<?php

class MDN_AdvancedStock_Block_Warehouse_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('advancedstock_warehouse_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml()
    {
    	$warehouseId = $this->getRequest()->getParam('stock_id');
    	
        $this->addTab('tab_main', array(
            'label'     => Mage::helper('AdvancedStock')->__('Information'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Main')->toHtml(),
        ));
       
        $this->addTab('tab_assignment', array(
            'label'     => Mage::helper('AdvancedStock')->__('Assignments'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Assignment')->toHtml(),
        ));
               
        $this->addTab('tab_products', array(
            'label'     => Mage::helper('AdvancedStock')->__('Products'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Products')
            										->setWarehouseId($warehouseId)
                                                    ->setTemplate('AdvancedStock/Warehouse/Edit/Tab/Products.phtml')
            										->toHtml(),
        ));
        
        $this->addTab('tab_export', array(
            'label'     => Mage::helper('AdvancedStock')->__('Export'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Export')->toHtml(),
        ));
        
        $this->addTab('tab_import', array(
            'label'     => Mage::helper('AdvancedStock')->__('Import stock'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_Import')->toHtml(),
        ));

        $this->addTab('tab_import_shelf_location', array(
            'label'     => Mage::helper('AdvancedStock')->__('Import shelf location'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Warehouse_Edit_Tabs_ImportShelfLocation')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }

}
