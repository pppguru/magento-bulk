<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_OrderPreparationAction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour = '';

        $retour = '<a href="' . $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId())) . '">' . $this->__('View order') . '</a>';
        $retour .= '<br><a href="' . $this->getUrl('adminhtml/ProductReturn_OrderPreparation/AddToSelection', array('order_id' => $row->getId())) . '">' . $this->__('Select') . '</a>';

        return $retour;
    }
}