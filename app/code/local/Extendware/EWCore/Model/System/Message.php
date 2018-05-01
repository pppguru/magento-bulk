<?php

class Extendware_EWCore_Model_System_Message extends Extendware_EWCore_Model_Mage_Core_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ewcore/system_message');
    }
    
	protected function _beforeSave()
	{
	    if (!$this->getCategory()) $this->setCategory('general');
	    
		if ($this->isDataEmptyFor(array('extension', 'category', 'subject', 'body'))) {
			Mage::throwException($this->__('Missing data for item'));
		}
		
		if (Mage::getSingleton('ewcore/module')->exists($this->getExtension()) === false) {
			Mage::throwException($this->__('Extensions %s does not exist', $this->getExtension()));
		}
		
		$this->setUpdatedAt(now());
		if (is_empty_date($this->getCreatedAt())) {
			$this->setCreatedAt(now());
		}
		
		return parent::_beforeSave();
	}
}