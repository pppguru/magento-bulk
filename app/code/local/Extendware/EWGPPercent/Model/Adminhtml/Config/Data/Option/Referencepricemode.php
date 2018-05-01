<?php

class Extendware_EWGPPercent_Model_Adminhtml_Config_Data_Option_Referencepricemode extends Extendware_EWCore_Model_Data_Option_Singleton_Abstract {

	public function __construct()
	{
		$this->options = array(
			'price' => $this->__('Price'),
			'cost' => $this->__('Cost'),
			'group_price' => $this->__('Group Price'),
		);
		parent::__construct();
	}
}
