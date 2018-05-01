<?php

class MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Filter_ShippingMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select 
{
	protected function _getOptions()
    {
        $options = Mage::helper('Orderpreparation/PickupDeliveryOrders')->getAllowedShippingMethods(true);
        return $options;
    }	

}