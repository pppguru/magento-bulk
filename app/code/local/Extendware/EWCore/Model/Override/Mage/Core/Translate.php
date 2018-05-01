<?php
class Extendware_EWCore_Model_Override_Mage_Core_Translate extends Extendware_EWCore_Model_Override_Mage_Core_Translate_Bridge
{
	protected function _getFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            return parent::_getFileData($file);
        } else {
        	// fallback to en_US
        	$fileName = basename($file);
        	if (strpos($fileName, 'Extendware_') === 0) {
	        	$locale = basename(dirname(dirname($file)));
	        	if ($locale != 'en_US') {
	        		$file = dirname(dirname(dirname($file))) . DS . 'en_US' . DS . 'extendware' . DS . $fileName;
	        		return parent::_getFileData($file);
	        	}
        	}
        }
        return $data;
    }
    
	protected function _getTranslatedString($text, $code)
    {
    	if (Mage::helper('ewcore/config')->isWhiteLabeled() === false) {
    		return parent::_getTranslatedString($text, $code);
    	}
    	
    	$codeParts = explode('::', $code, 2);
    	if (strpos($codeParts[0], 'Extendware_') !== 0) {
    		return parent::_getTranslatedString($text, $code);
    	}
    	
        $translated = '';
        if (array_key_exists($code, $this->getData())) {
            $translated = $this->_data[$code];
        }
        elseif (array_key_exists($text, $this->getData())) {
            $translated = $this->_data[$text];
        }
        else {
            $translated = $text;
        }

        $translated = Mage::helper('ewcore/whitelabel')->removeLinks($translated);
        $translated = preg_replace('/Extendware/', Mage::helper('ewcore/whitelabel')->getCompanyName(), $translated);
        return $translated;
    }
}
