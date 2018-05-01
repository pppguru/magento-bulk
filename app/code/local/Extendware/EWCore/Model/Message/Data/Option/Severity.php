<?php

class Extendware_EWCore_Model_Message_Data_Option_Severity extends Extendware_EWCore_Model_Data_Option_Singleton_Abstract
{
	public function __construct()
	{
		$this->options = array(
        	'notice' => $this->__('Notice'),
            'minor' => $this->__('Minor'),
			'major' => $this->__('Major'),
			'critical' => $this->__('Critical'),
        );
        
        parent::__construct();
	}
}
