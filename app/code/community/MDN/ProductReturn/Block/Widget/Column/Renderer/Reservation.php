<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_Reservation
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $product_id = $row->getId();
        $rma_id     = $this->getColumn()->getrma_id();
        $name       = 'rr_qty_' . $product_id;

        $html = '<input size="3" type="text" name="' . $name . '" id="' . $name . '" value="1">';
        $html .= '<input type="button" value="' . $this->__('Reserve') . '" onclick="reserveProduct(' . $product_id . ')">';

        return $html;
    }

}