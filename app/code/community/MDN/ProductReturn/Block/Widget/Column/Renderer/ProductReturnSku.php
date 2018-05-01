<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //get purchase price
        $html = '<a href="' . $this->getUrl('AdvancedStock/Products/Edit', array('product_id' => $row->getrsrp_product_id())) . '">' . $row->getrsrp_product_sku() . '</a>';

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        die();
    }
}