<?php

class MDN_Orderpreparation_Model_Source_AdditionalWeightMethod extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_NONE,
                    'label' => Mage::helper('purchase')->__('None'),
                ),
            	array(
                    'value' => MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_FIX_WEIGHT,
                    'label' => Mage::helper('purchase')->__('Add fixed weight'),
                ),
            	array(
                    'value' => MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_FIX_WEIGHT_PER_PRODUCT,
                    'label' => Mage::helper('purchase')->__('Add fixed weight per product'),
                ),
            	array(
                    'value' => MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_PERCENT,
                    'label' => Mage::helper('purchase')->__('Add fixed percent'),
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