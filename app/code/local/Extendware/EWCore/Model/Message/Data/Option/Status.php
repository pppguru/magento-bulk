<?php

class Extendware_EWCore_Model_Message_Data_Option_Status extends Extendware_EWCore_Model_Data_Option_Singleton_Abstract
{
	public function __construct()
	{
		$this->options = array(
        	'enabled' => $this->__('Enabled'),
            'disabled' => $this->__('Disabled'),
        );
        
        parent::__construct();
	}
}
