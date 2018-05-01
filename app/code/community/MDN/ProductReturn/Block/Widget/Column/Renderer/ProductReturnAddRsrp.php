<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnAddRsrp extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //get purchase price
        $html = '<button onclick="addRsrp(' . $row->getentity_id() . ')">' . $this->__('Select') . '</button>';

        return $html;
    }
}