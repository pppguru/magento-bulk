<?php

class MDN_AdvancedStock_Model_Convert_Adapter_ImportBarcode extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    public function load() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function saveRow(array $importData) {

        //load data
        $sku = $importData['sku'];
        $barcode = $importData['barcode'];

        if (($sku == '') || ($barcode == ''))
            throw new Exception('Skip row, sku or barcode is missing');

        //check if product exists
        $productId = mage::getModel('catalog/product')->getIdBySku($sku);
        if (!$productId) {
            throw new Exception('Unable to load product with sku = ' . $sku);
        }

        //add barcode
        mage::helper('AdvancedStock/Product_Barcode')->addBarcodeIfNotExists($productId, $barcode);

        return true;
    }


}