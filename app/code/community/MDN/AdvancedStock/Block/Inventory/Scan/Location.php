<?php

class MDN_AdvancedStock_Block_Inventory_Scan_Location extends Mage_Core_Block_Template {
    
    /**
     * Return current inventory
     * @return type
     */
    public function getInventory()
    {
        return Mage::registry('current_inventory');
    }
    
    /**
     * Return total expected qty
     */
    public function getTotalExpectedQty()
    {
        $collection = $this->getInventory()->getExpectedProducts($this->getLocation());
        $total = 0;
        foreach($collection as $item)
        {
            $total += $item->geteisp_stock();
        }
        return $total;
    }
    
}