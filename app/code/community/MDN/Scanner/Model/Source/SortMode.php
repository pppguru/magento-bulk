<?php

class MDN_Scanner_Model_Source_SortMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => 'manufacturer',
                    'label' => mage::helper('Orderpreparation')->__('Manufacturer'),
                ),
            	array(
                    'value' => 'location',
                    'label' => mage::helper('Orderpreparation')->__('Product location'),
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