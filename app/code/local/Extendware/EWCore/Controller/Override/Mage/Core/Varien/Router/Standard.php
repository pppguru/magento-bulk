<?php
class Extendware_EWCore_Controller_Override_Mage_Core_Varien_Router_Standard extends Extendware_EWCore_Controller_Override_Mage_Core_Varien_Router_Standard_Bridge
{
	protected function _includeControllerClass($controllerFileName, $controllerClassName)
    {
    	if (Extendware_EWCore_Model_Autoload::isOverridden($controllerClassName)) {
    		try {
    			$controllerFileName = Extendware_EWCore_Model_Autoload::getIncludeFileFor($controllerClassName);
    		} catch (Exception $e) { Mage::logException($e); }
    	}
    	
       return parent::_includeControllerClass($controllerFileName, $controllerClassName);
    }
}
