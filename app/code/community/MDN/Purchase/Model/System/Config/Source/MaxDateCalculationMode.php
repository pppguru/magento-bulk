<?php

class MDN_Purchase_Model_System_Config_Source_MaxDateCalculationMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options)
        {
            $this->getAllOptions();
        	
        }
        return $this->_options;
    }
    
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => 'days',
                    'label' => Mage::helper('purchase')->__('Add days'),
                ),
            	array(
                    'value' => 'percent',
                    'label' => Mage::helper('purchase')->__('Add percent'),
                )                
            );
        }
        return $this->_options;
    }
}