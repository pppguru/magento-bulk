<?php

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_StockSummary
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $html = '<div style="white-space: nowrap;">';

        $stocks = array();
        $productId = $row->getId();

        //Display stock quantity for a product : Available/Total
        $collection = mage::helper('AdvancedStock/Product_Base')->getStocksToDisplay($productId);
        foreach ($collection as $item) {
            if ($item->ManageStock()) {
                $qty = ((int) $item->getqty());
                $available = ((int) $item->getAvailableQty());
                $color = ($available > 0 ? 'green' : 'red');
                $name = ($item->getstock_code())?$item->getstock_code():$item->getstock_name();
                $stocks[] = '<font color="'.$color.'">'.$name . ' : ' . $available . ' / ' . $qty . '</font>';
            }
        }

        $html .= implode('<br/>',$stocks);
        $html .= '</div>';

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        $html = '';

        $productId = $row->getId();


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