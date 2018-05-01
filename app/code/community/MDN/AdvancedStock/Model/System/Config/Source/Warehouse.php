<?php

class MDN_AdvancedStock_Model_System_Config_Source_Warehouse extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
        	$this->_options = array();
        	
			$collection = mage::getModel('AdvancedStock/Warehouse')->getCollection();
			foreach($collection as $item)
			{
	        	$this->_options[] = array('value' => $item->getId() ,'label' => $item->getstock_name());			
			}
        }
        return $this->_options;
    }
    
    public function getListForFilter()
    {
    	$retour = array();
		$collection = mage::getModel('AdvancedStock/Warehouse')->getCollection();
		foreach($collection as $item)
		{
        	$retour[$item->getId()] = $item->getstock_name();			
		}
		return $retour;
    }
}