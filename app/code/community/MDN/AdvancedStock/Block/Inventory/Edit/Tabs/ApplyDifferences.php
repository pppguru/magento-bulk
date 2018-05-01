<?php

class MDN_Inventory_Block_Inventory_Edit_Tabs_ApplyDifferences extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();

        $this->setTemplate('Inventory/Edit/Tabs/ApplyDifferences.phtml');
    }

    public function getInventory() {
        return $inventory = Mage::registry('current_inventory');
    }
    
    public function applyInventoryUrl()
    {
        return $this->getUrl('Inventory/Admin/Apply');
    }

}
