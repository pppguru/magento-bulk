<?php

class MDN_Scanner_Block_Inventory_FreeDelivery extends Mage_Adminhtml_Block_Widget_Form
{
    private $_errorMessage = '';
    private $_successMessage = '';
    /**
     * Create product delivery
     * @param <type> $barcode
     * @param <type> $location
     */
    public function addProduct($barcode, $location)
    {
        $product = mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode($barcode);
        if (!$product)
        {
            $this->_errorMessage = $this->__('No product with barcode %s', $barcode);
        }
        else
        {
            //init
            $stockMovementCaption = Mage::getStoreConfig('scanner/free_delivery/stock_movement_caption');
            $warehouseId = Mage::getStoreConfig('scanner/free_delivery/warehouse');
            $productId = $product->getId();

            //get stock item
            $stockItem = Mage::getModel('AdvancedStock/CatalogInventory_Stock_Item')->loadByProductWarehouse($productId, $warehouseId);
            if (!$stockItem)
            {
                $this->_errorMessage = $this->__('Stock item doesnt exists !');
                return false;
            }

            //check location change (if enabled)
            if (Mage::getStoreConfig('scanner/free_delivery/prevent_location_change_if_is_in_stock'))
            {
                if ($stockItem->getshelf_location())
                {
                    if (($stockItem->getqty() > 0) && ($location != $stockItem->getshelf_location()))
                    {
                        $this->_errorMessage = $this->__('This product is already stored in location %s !', $stockItem->getshelf_location());
                        return false;
                    }
                }
            }

            //create stock movement
            $stockMovement = Mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId, null, $warehouseId, 1, $stockMovementCaption);

            //change product location
            $stocks = Mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
            foreach($stocks as $stock)
            {
                $stock->setshelf_location($location)->save();
            }

            $this->_successMessage = $this->__('Product %s added', $product->getName());
        }
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function getSuccessMessage()
    {
        return $this->_successMessage;
    }

}