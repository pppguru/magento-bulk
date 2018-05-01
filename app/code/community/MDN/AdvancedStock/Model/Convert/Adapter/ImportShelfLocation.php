<?php

class MDN_AdvancedStock_Model_Convert_Adapter_ImportShelfLocation extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    public function load() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function saveRow(array $importData) {

        $sku = '';
        $warehouseId = '';
        $shelfLocation = '';

        //get data from file
        if(array_key_exists('sku',$importData )) {
            $sku = trim($importData['sku']);
        }

        if(array_key_exists('stock_id',$importData )) {
            $warehouseId = trim($importData['stock_id']);
        }

        if(array_key_exists('location',$importData )) {
            $shelfLocation = trim($importData['location']);
        }

        //check data consistency
        if ($sku == '')
            throw new Exception('Skip row : sku is missing');

        if ($warehouseId == '')
            throw new Exception('Skip row : stock_id is missing');

        if ($shelfLocation == '')
            throw new Exception('Skip row : location is missing');

        //check if product exists
        $productId = mage::getModel('catalog/product')->getIdBySku($sku);
        if (!$productId) {
            throw new Exception('Skip row : Unable to load product with sku = ' . $sku);
        }

        //load Stock Line ( i don't want to create stock line it does not exists)
        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

        //Add the shelf location if possible
        if($stockItem != NULL && $stockItem->getId()) {
            $stockItem->setshelf_location($shelfLocation)->save();
        }else{
            throw new Exception('Skip row : Unable to load warehouse '.$warehouseId.' for product sku = ' . $sku);
        }

        return true;
    }


}