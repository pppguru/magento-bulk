<?php

class MDN_Orderpreparation_Model_Source_PrintMethod extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => 'download',
                    'label' => mage::helper('Orderpreparation')->__('Download PDF'),
                ),
            	array(
                    'value' => 'send_to_printer',
                    'label' => mage::helper('Orderpreparation')->__('Send to printer (using Magento Client Computer)'),
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