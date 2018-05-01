<?php

class MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_Notification extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $html = '';

        if ($row->getpickup_is_notified() == 1)
        {
            $html .= $this->__('Notified on %s', $row->getpickup_notification_time());
        }
        else
        {
            $url = $this->getUrl('adminhtml/OrderPreparation_PickupDelivery/Notify', array('order_id' => $row->getId()));
            $html .= '<input type="button" value="'.$this->__('Notify !').'" onclick="document.location.href=\''.$url.'\'">';
        }

        return $html;
    }

}