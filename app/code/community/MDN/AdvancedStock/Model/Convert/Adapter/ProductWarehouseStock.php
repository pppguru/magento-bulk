<?php

class MDN_AdvancedStock_Model_Convert_Adapter_ProductWarehouseStock extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    public function load() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function saveRow(array $importData) {

        //load data
        $sku = $importData['sku'];
        $qty = $importData['qty'];
        $warehouseCode = $importData['stock_code'];

        //check if product exists
        $productId = mage::getModel('catalog/product')->getIdBySku($sku);
        if (!$productId) {
            throw new Exception('Unable to load product with sku = ' . $sku);
        }

        //check if warehouse exists
        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($warehouseCode, 'stock_code');
        if (!$warehouse || !$warehouse->getId()) {
            throw new Exception('Unable to load warehouse with code ' . $warehouseCode);
        }

        //update stock
        $stockItem = $this->getStockItem($productId, $warehouse);
        foreach($importData as $key => $value)
        {
            $stockItem->setData($key, $value);
        }
        $stockItem->save();

        //if is favorite, ensure that other are not favorite
        $isFavorite = (isset($importData['is_favorite_warehouse']) ? $importData['is_favorite_warehouse'] : 0);
        if ($isFavorite)
        {
            $stocks = Mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
            foreach($stocks as $otherStock)
            {
                if ($otherStock->getId() == $stockItem->getId())
                        continue;
                if ($otherStock->getis_favorite_warehouse())
                        $otherStock->setis_favorite_warehouse(0)->save();
            }
        }

        return true;
    }

    public function getStockItem($productId, $warehouse)
    {
        $stockItem = $warehouse->getProductStockItem($productId);

        //if stock doesn't exist, create it
        if (!$stockItem || !$stockItem->getId())
            $stockItem = mage::getModel('cataloginventory/stock_item')->createStock($productId, $warehouse->getId());

        return $stockItem;
    }

}