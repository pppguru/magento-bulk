<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $html = '<div style="white-space: nowrap;">';

        $productId = $row->getId();
        if($row->getpop_product_id()>0){
            $productId = $row->getpop_product_id();
        }

        //Display stock quantity for a product : Available/Total
        $collection = mage::helper('AdvancedStock/Product_Base')->getStocksToDisplay($productId);
        foreach ($collection as $item) {
            if ($item->ManageStock()) {
                $qty = ((int) $item->getqty());
                $available = ((int) $item->getAvailableQty());
                $color = ($available > 0 ? 'green' : 'red');
                $htmlLine = '<font color="'.$color.'">'.$item->getstock_name() . ' : ' . $available . ' / ' . $qty . '</font><br>';
                $html .= $htmlLine;
            }
        }

        //Display qty pending to be delivered by any supplier
        if(!$row->getpop_product_id()) {
            $waiting_for_delivery_qty = $row->getData('waiting_for_delivery_qty');
            if ($waiting_for_delivery_qty > 0) {
                $html .= Mage::helper('AdvancedStock')->__('Waiting for delivery') . ' : ' . $waiting_for_delivery_qty;
            }
        }

        $html .= '</div>';

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        $html = '';

        $productId = $row->getId();
        if($row->getpop_product_id()>0){
            $productId = $row->getpop_product_id();
        }

        //Display stock quantity for a product : Available/Total
        $collection = mage::helper('AdvancedStock/Product_Base')->getStocksToDisplay($productId);
        foreach ($collection as $item) {
            if ($item->ManageStock()) {
                $qty = ((int) $item->getqty());
                $available = ((int) $item->getAvailableQty());
                $html .= $item->getstock_name() . ' : ' . $available . ' / ' . $qty . ', ';
            }
        }

        return $html;
    }

}