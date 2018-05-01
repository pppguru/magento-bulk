<?php

class MDN_Purchase_Model_System_Config_Source_Taxrates extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
        	$collection = mage::getModel('Purchase/TaxRates')->getCollection();
        	$this->_options = array();
        	foreach($collection as $item)
        	{
        		$this->_options[] = array('value' => $item->getptr_id(),'label' => $item->getptr_name());
        	}
        }
        return $this->_options;
    }
}