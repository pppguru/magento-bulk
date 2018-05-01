<?php

class MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Filter_YesNoEmpty extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
 public function getCondition()
    {
    	$searchString = $this->getValue();
    	if ($searchString == '')
    		return;

        switch($searchString)
        {
            case '0':
            	return array('null' => 1);
                break;
            case '1':
            	return array('eq' => '1');
                break;
        }

    }
}