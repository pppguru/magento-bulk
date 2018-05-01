<?php

class Extendware_EWCore_Model_Message extends Extendware_EWCore_Model_Mage_Core_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ewcore/message');
        $this->allowAllPermissionsFor('guest');
        
        $this->addOptionModelMethod('status', 'ewcore/message_data_option_status');
        $this->addOptionModelMethod('state', 'ewcore/message_data_option_state');
        $this->addOptionModelMethod('severity', 'ewcore/message_data_option_severity');
    }
    
	protected function _beforeSave()
	{
		$this->searchReplaceFieldValues(array('url', 'body'), '', null);
		
		if ($this->isDataEmptyFor(array('severity', 'category', 'subject', 'reference_id'))) {
			Mage::throwException($this->__('Missing data for item'));
		}
		
		if (!$this->getStatus()) {
			$this->setStatus('enabled');
		}
		
		if (!$this->getState()) {
			$this->setState('unread');
		}
		
		if ($this->isDataEmptyFor(array('sent_at'), 'date')) {
			Mage::throwException($this->__('Missing data for item'));
		}
		
		$this->setUpdatedAt(now());
		if (is_empty_date($this->getCreatedAt())) {
			$this->setCreatedAt(now());
		}
		
		return parent::_beforeSave();
	}
	
	public function loadByReferenceId($value) {
    	return $this->load((int)$value, 'reference_id');
    }
    
}