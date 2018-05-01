<?php
class Extendware_EWCore_Block_Generated_Css extends Extendware_EWCore_Block_Generated_Abstract
{
    public function __construct()
    {
		$this->setCacheDirectory(Mage::getConfig()->getOptions()->getMediaDir() . DS . 'extendware' . DS . 'ewcore' . DS . 'generated' . DS . 'css');
        parent::__construct();
    }
    
    public function getCachedFilename() 
    {
        return md5(parent::_getCacheKey()) . '.css';
    }
}

