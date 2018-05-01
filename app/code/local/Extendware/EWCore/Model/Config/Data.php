<?php

class Extendware_EWCore_Model_Config_Data extends Extendware_EWCore_Model_Mage_Core_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ewcore/config_data');
        $this->allowAllPermissionsFor('guest');
    }
    
	protected function _beforeSave()
	{
		if (!$this->getScopeId()) $this->setScope('default');
		if ($this->getScope() == 'default') $this->setScopeId(0);
		if (in_array($this->getScope(), array('stores', 'websites', 'default', 'config')) === false) {
			Mage::throwException($this->__('Invalid scope entered for config item'));
		}
		
		return parent::_beforeSave();
	}
}