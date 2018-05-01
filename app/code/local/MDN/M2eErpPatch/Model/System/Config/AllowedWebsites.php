<?php

class MDN_M2eErpPatch_Model_System_Config_AllowedWebsites extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
			$websites  = mage::getResourceModel('core/website_collection')->setLoadDefault(true);

							
			//add empty
        	$options[] = array(
                'value' => '',
                'label' => '',
            );
							
        	foreach ($websites as $website)
        	{
				$options[] = array(
					'value' => $website->getId(),
					'label' => $website->getName(),
				);
        	}
        	
            $this->_options = $options;
        }
        return $this->_options;
    }
    
	public function toOptionArray()
	{
		return $this->getAllOptions();
	}
}