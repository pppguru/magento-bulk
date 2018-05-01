<?php
class Extendware_EWCore_Block_Adminhtml_Page_Footer_Guide extends Extendware_EWCore_Block_Mage_Adminhtml_Template
{
	protected function getModule() {
		static $module = null;
		if ($module === null) $module = Mage::getSingleton('ewcore/module')->findById(Mage::app()->getRequest()->getModuleName());
		return $module;
	}
	
	protected function hasModule() {
		return $this->getModule() and $this->getModule()->getId();
	}
	
	protected function getCoreModule() {
		static $module = null;
		if ($module === null) $module = Mage::getSingleton('ewcore/module')->load('Extendware_EWCore');
		return $module;
	}
	
	protected function hasCoreModule() {
		return $this->getCoreModule() and $this->getCoreModule()->getId();
	}
	
	protected function _toHtml()
    {
    	if (!$this->mHelper('config')->isUserGuideButtonEnabled()) return;
    	if (!$this->hasCoreModule()) return;
    	if (!$this->getCoreModule()->hasSerial()) return;
    	if (!$this->hasModule()) return;
    	if (!$this->getModule()->isExtendware()) return;
    	if ($this->getModule()->isForMainsite()) return;
    	
        return parent::_toHtml();
    }
    
    public function getOptions() {
    	$params = array(
    		'iid' => $this->getCoreModule()->getSerial()->getInstallationId(),
    		'sid' => $this->getModule()->getId(),
    	);
    	
    	$options = array(
			'area' => $this->getAction()->getLayout()->getArea(), 
			'requestPath' => $this->mHelper()->getRequestRoute(),
			'url' => $this->mHelper()->getGuideUrl('rwsoftware/guide/iframe', $params),
    		'identifier' => $this->getModule()->getId(),
    		'auto_open' => true,
		);
    	
    	return $options;
    }
}
