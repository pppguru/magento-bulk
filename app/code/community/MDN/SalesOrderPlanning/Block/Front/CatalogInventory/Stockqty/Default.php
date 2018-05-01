<?php

class MDN_SalesOrderPlanning_Block_Front_CatalogInventory_Stockqty_Default extends Mage_CatalogInventory_Block_Stockqty_Default{
    
    /**
     * Override this to use the available qty insstead
     *
     * @return float
     */
    public function getStockQty()
    {
        if (!$this->hasData('product_stock_qty')) {
            $qty = 0;
            $productAvailability = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($this->_getProduct()->getId(), 'pa_product_id');
            if ($productAvailability)
            {
                $qty = $productAvailability->getpa_available_qty();
            }
            $this->setData('product_stock_qty', $qty);
        }
        return $this->getData('product_stock_qty');
    }
    
}