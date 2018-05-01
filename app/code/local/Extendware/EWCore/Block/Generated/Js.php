<?php
class Extendware_EWCore_Block_Generated_Js extends Extendware_EWCore_Block_Generated_Abstract
{
    public function __construct()
    {
        $this->setCacheDirectory(Mage::getConfig()->getOptions()->getMediaDir() . DS . 'extendware' . DS . 'ewcore' . DS . 'generated' . DS . 'js');
        parent::__construct();
    }
    
	public function getCacheKey() {
        $key = '-' . parent::getCacheKey();
        $key .= '-' . (int)Mage::app()->getRequest()->isSecure();
        return md5($key);
	}
	
    public function getCachedFilename() 
    {
        return md5(parent::_getCacheKey()) . '.js';
    }
}

