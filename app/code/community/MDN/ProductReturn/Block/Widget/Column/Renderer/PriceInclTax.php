<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_PriceInclTax extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $currentRma = Mage::registry('current_rma');

        $price = Mage::helper('ProductReturn/Tax')->exclToIncl($row->getentity_id(), $row->getPrice(), $currentRma->getShippingAddress());

        return Mage::helper('core')->currency($price,true,false);
    }

}
