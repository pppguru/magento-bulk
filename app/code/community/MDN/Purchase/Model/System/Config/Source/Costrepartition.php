<?php

class MDN_Purchase_Model_System_Config_Source_Costrepartition extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'value' => 'by_qty',
                    'label' => Mage::helper('purchase')->__('By Product Qty'),
                ),
            	array(
                    'value' => 'by_amount',
                    'label' => Mage::helper('purchase')->__('By Product amount'),
                ),
                array(
                    'value' => 'by_weight',
                    'label' => Mage::helper('purchase')->__('By Weight'),
                )
            );
        }
        return $this->_options;
    }
}