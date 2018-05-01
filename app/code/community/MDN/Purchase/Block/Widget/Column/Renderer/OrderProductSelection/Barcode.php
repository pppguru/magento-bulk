<?php

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProductSelection_Barcode extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row) {
        $productId = $row->getentity_id();
        $barcode = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
        return $barcode;
    }

}