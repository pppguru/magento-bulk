<?php

class MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_SalesOrder extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $retour = '';

        if ($row->getpps_salesorder_id()) {            
            $order = mage::getModel('sales/order')->load($row->getpps_salesorder_id());
            if($order->getId()>0){
                $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getpps_salesorder_id()));
                $retour = '<a href="' . $url . '" target="_blanck">' . $order->getincrement_id() . '</a>';
            }
        } 

        return $retour;
    }

}
