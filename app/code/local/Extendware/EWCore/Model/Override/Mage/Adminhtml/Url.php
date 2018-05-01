<?php
class Extendware_EWCore_Model_Override_Mage_Adminhtml_Url extends Extendware_EWCore_Model_Override_Mage_Adminhtml_Url_Bridge
{
	public function getUrl($routePath = null, $routeParams = null)
    {
    	if (Mage::helper('ewcore/config')->isWhiteLabeled() === true) {
	    	$routePathParts = explode('/', $routePath);
	    	if (preg_match('/^extendware/', $routePathParts[0])) {
		    	$routePath = preg_replace('/^extendware/', Mage::helper('ewcore/config')->getWhiteLabelIdentifier(), $routePath);
	    	}
    	}
    	
    	return parent::getUrl($routePath, $routeParams);
    }
}
