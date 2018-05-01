<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Config_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Config/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_ConfigController extends Extendware_EWCore_Controller_Adminhtml_Config_Action
{
	public function _construct()
	{
		$this->_title($this->__('Extendware'))->_title($this->__('Configuration'));
		Mage::getSingleton(strtolower($this->_getModuleNameIdentifier()) . '/adminhtml_config')->setScope('main');
		return parent::_construct();
	}
}