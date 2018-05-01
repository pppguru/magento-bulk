<?php

class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_PrintBarcode
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $productId = $row->getpop_product_id();
        $count = $row->getpop_qty() - $row->getpop_supplied_qty();
        if ($count < 0)
            $count = 1;
        $html = '<input type="button" value="' . $this->__('Print') . '" onclick="printBarcodeForProduct(' . $productId . ', ' . $count . ');" >';

        return $html;
    }
}
