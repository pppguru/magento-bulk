<?php

class MDN_AdvancedStock_Block_Inventory_Simulation extends Mage_Core_Block_Template {
    
    
    /**
     * Return current inventory
     */
    public function getInventory()
    {
        return Mage::registry('current_inventory');
    }  
    
    public function getBackUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Inventory/Edit', array('ei_id' => $this->getInventory()->getId()));
    }
    
}
