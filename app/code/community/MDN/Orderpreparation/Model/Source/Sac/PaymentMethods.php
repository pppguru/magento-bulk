<?php


class MDN_Orderpreparation_Model_Source_Sac_PaymentMethods extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
	        $config = Mage::getStoreConfig('payment');
	        foreach ($config as $code => $methodConfig) {
	        	if (Mage::getStoreConfigFlag('payment/'.$code.'/active')) {
                    if(array_key_exists('title', $methodConfig)){
                        $options[] = array(
                                        'value' => $code,
                                        'label' => $methodConfig['title'],
                                    );
                    }
		        }
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