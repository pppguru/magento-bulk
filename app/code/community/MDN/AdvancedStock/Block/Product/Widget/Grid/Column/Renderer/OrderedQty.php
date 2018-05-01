<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderedQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

    	$retour = '<span style="white-space: nowrap;">';
    	$retour .= '<b>'.$this->__('Valid : %s', (int)$row->getstock_ordered_qty_for_valid_orders()).'</b>';
    	$retour .= '<br>'.$this->__('All : %s', (int)$row->getstock_ordered_qty());
    	$retour .= '</span>';

        return $retour;
    }

}