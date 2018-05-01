<?php

class MDN_Orderpreparation_Model_Source_pageSize extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => 50,
                    'label' => 50,
                ),
            	array(
                    'value' => 100,
                    'label' => 100,
                ),
            	array(
                    'value' => 200,
                    'label' => 200,
                ),
            	array(
                    'value' => 300,
                    'label' => 300,
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