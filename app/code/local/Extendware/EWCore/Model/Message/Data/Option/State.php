<?php

class Extendware_EWCore_Model_Message_Data_Option_State extends Extendware_EWCore_Model_Data_Option_Singleton_Abstract
{
	public function __construct()
	{
		$this->options = array(
        	'read' => $this->__('Read'),
            'unread' => $this->__('Unread'),
        );
        
        parent::__construct();
	}
}
