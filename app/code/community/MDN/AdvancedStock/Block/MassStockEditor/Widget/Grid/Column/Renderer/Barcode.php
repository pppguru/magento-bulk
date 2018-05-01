<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $html = array();
        $barcodes = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($row->getproduct_id());
        foreach($barcodes as $item)
        {
            $html[] = $item->getppb_barcode();
        }
        return implode('<br>', $html);
    }

}