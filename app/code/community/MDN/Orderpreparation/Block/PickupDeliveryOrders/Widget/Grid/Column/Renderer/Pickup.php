<?php

class MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_Pickup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $html = '';

        if ($row->getpickup_is_picked() == '1')
        {
            $html = $row->getpickup_information();
        }
        else
        {
            $url = $this->getUrl('adminhtml/OrderPreparation_PickupDelivery/PickupForm', array('order_id' => $row->getId()));
            $html .= '<input type="button" value="'.$this->__('Pickup !').'" onclick="document.location.href=\''.$url.'\'">';
        }

        return $html;
    }

}