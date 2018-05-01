<?php

class MDN_ProductReturn_Block_Widget_Grid_Column_Renderer_OrderProducts extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $order)
    {
        $html = array();
        foreach($order->getAllVisibleItems() as $item)
        {
            $html[] = ((int)$item->getQtyOrdered()).'x '.$item->getName();
        }
        return implode('<br>', $html);
    }
    
}