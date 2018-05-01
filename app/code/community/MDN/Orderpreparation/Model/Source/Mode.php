<?php

class MDN_Orderpreparation_Model_Source_Mode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => 'mass',
                    'label' => mage::helper('Orderpreparation')->__('Mass'),
                ),
            	array(
                    'value' => 'order_by_order',
                    'label' => mage::helper('Orderpreparation')->__('Order by order'),
                )
            );
        }
        return $this->_options;
    }
    
	public function toOptionArray()
	{
		return $this->getAllOptions();
	}
}