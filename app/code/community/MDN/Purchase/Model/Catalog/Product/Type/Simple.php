<?php

class MDN_Purchase_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple {

    /**
     * Check is product available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null) {
        $salable = $this->getProduct($product)->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;

        if ($salable) {
            try {
                //check if product manage stocks
                $Product = $this->getProduct($product);
                $StockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($Product->getId());
                if ($StockItem->getManageStock() == 1) {
                    //get available qty for stocks assigned for sale for this website
                    $websiteId = Mage::app()->getStore()->getwebsite_id();
                    $availableQty = 0;
                    $stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales);
                    foreach ($stocks as $stock)
                        $availableQty += $stock->getAvailableQty();

                    //If no available qty and no backorders
                    if (($availableQty <= 0 ) && ($StockItem->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO))
                        $salable = false;
                }
            } catch (Exception $ex) {
                //rien
            }
        }

        if ($salable && $this->getProduct($product)->hasData('is_salable')) {
            $salable = $this->getProduct($product)->getData('is_salable');
        } elseif ($salable && $this->isComposite()) {
            $salable = null;
        }

        return $salable;
    }

}