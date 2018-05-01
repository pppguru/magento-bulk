<?php

class MDN_AdvancedStock_Model_System_Config_Source_RouterMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
			$this->_options = mage::helper('AdvancedStock/Router')->getAllModes();
        }
        return $this->_options;
    }

}