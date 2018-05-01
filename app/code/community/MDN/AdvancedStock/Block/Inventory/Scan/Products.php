<?php

class MDN_AdvancedStock_Block_Inventory_Scan_Products extends Mage_Core_Block_Template {
    
    public function getInventory()
    {
        return Mage::registry('current_inventory');
    }
    
    /**
     * Return expected products
     */
    public function getProducts()
    {
        return $this->getInventory()->getExpectedProducts($this->getLocation());
    }
    
}