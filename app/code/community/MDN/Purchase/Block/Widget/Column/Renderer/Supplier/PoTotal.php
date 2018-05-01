<?php

class MDN_Purchase_Block_Widget_Column_Renderer_Supplier_PoTotal
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $row->getPoTotal();
    }
}