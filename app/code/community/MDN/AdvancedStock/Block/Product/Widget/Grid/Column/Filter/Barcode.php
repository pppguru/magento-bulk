<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    public function getCondition() {
        $searchString = trim($this->getValue());

        if(!empty($searchString)){
            if (Mage::helper('AdvancedStock/Product_Barcode')->useStandardErpBarcodeManagement()) {
                //use erp tables to find barcodes
                $barcodes = mage::getModel('AdvancedStock/ProductBarcode')
                                ->getCollection()
                                ->addFieldToFilter('ppb_barcode', array('like' => '%' . $searchString . '%'));
                $productIds = array();
                foreach ($barcodes as $barcode) {
                    $productIds[] = $barcode->getppb_product_id();
                }
            } else {
                $productIds = Mage::getModel('catalog/product')
                                    ->getCollection()
                                    ->addAttributeToFilter(Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeAttribute(), array('like' => '%'.$searchString.'%'))
                                    ->getAllIds();
            }
        }

        return array('in' => $productIds);
    }

}