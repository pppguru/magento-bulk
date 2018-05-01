<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_Stock extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $stockId = $row->getitem_id();
        $qty = (int) $row->getqty();

        $onChange = 'onchange="persistantGrid.logChange(this.name, \''.$qty.'\')"';
        $html = (int) $this->getAvailableQty($row).' / ';
        $html .= '<input type="text" name="qty_' . $stockId . '" id="qty_' . $stockId . '" value="' . $qty . '" size="4" '.$onChange.'>';

        return $html;
    }
	
	public function getAvailableQty($row) {
        $value = $row->getqty() - $row->getstock_ordered_qty();
        if ($value < 0)
            $value = 0;
        return $value;
    }

}
