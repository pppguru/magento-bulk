<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('inventory_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml() {
        $inventory = Mage::registry('current_inventory');

        $this->addTab('tab_summary', array(
            'label' => Mage::helper('AdvancedStock')->__('Summary'),
            'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_Summary')->initForm()->toHtml(),
        ));

        if ($inventory->getId()) {

            $this->addTab('tab_stock_picture', array(
                'label' => Mage::helper('AdvancedStock')->__('Stock picture'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_StockPicture')->toHtml(),
            ));

            $this->addTab('tab_scan_products', array(
                'label' => Mage::helper('AdvancedStock')->__('Scanned products'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_ScannedProducts')->toHtml(),
            ));

            if(!$inventory->isLocationScanAllowed()){
                $this->addTab('tab_not_scan_products', array(
                'label' => Mage::helper('AdvancedStock')->__('Not scanned products'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_NotScannedProducts')->toHtml(),
                ));
            }

            $this->addTab('differences', array(
                'label' => Mage::helper('AdvancedStock')->__('Differences'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_Differences')->toHtml(),
            ));

            if($inventory->isLocationScanAllowed()){
                $this->addTab('missed_locations', array(
                    'label' => Mage::helper('AdvancedStock')->__('Missed locations'),
                    'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_MissedLocations')->toHtml(),
                ));
            }
            
            if ($inventory->getei_status() == MDN_AdvancedStock_Model_Inventory::kStatusClosed) {
                $this->addTab('fixed_products', array(
                    'label' => Mage::helper('AdvancedStock')->__('Adjustment stock movements'),
                    'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_FixedProducts')->toHtml(),
                ));
            }


            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/inventory/apply')) {
                if ($inventory->getei_status() == MDN_AdvancedStock_Model_Inventory::kStatusOpened) {
                    $this->addTab('apply_inventory', array(
                        'label' => Mage::helper('AdvancedStock')->__('Apply stock take'),
                        'content' => $this->getLayout()->createBlock('AdvancedStock/Inventory_Edit_Tabs_ApplyInventory')->initForm()->toHtml(),
                    ));
                }
            }
        }

        return parent::_beforeToHtml();
    }

}
