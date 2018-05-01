<?php
class Extendware_EWCore_Model_Override_Mage_Core_Exception extends Extendware_EWCore_Model_Override_Mage_Core_Exception_Bridge
{
	public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code);
        if($this instanceof Mage_Core_Model_Store_Exception) return;
        
		static $maxLogItems = 25; // prevent infinite loops
        if ($maxLogItems-- > 0) {
			if (!Mage::getConfig()) return;
	        if (Mage::getStoreConfig('ewcore_developer/verbose_exception_log/enabled')) {
		        $file = 'exception_verbose.log';
		        Mage::log("\n" . $this->__toString(), Zend_Log::ERR, $file);
	        }
        }
    }
}
