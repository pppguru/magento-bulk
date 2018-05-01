<?php
// This is a very simple override that allows adding conditionals to action statements. It should not affect anything
class Extendware_EWCore_Model_Override_Mage_Core_Layout extends Extendware_EWCore_Model_Override_Mage_Core_Layout_Bridge
{
	protected function _generateAction($node, $parent)
    {
        if (isset($node['ewifhelper'])) {
			$helperName = explode('/', (string)$node['ewifhelper']);
			$helperMethod = array_pop($helperName);
			$helperName = implode('/', $helperName);

			if (!call_user_func_array(array(Mage::helper($helperName), $helperMethod), array())) {
				$this->writeXmlAttribute($node, '_ewminify_skip_group', 1);
				return $this;
			}
        }
        
    	if (isset($node['ifconfig']) && ($configPath = (string)$node['ifconfig'])) {
            if (!Mage::getStoreConfigFlag($configPath)) {
            	$this->writeXmlAttribute($node, '_ewminify_skip_group', 1);
                return $this;
            }
        }
        
        return parent::_generateAction($node, $parent);
    }
    
	private function writeXmlAttribute(&$xml, $key, $value) {
    	$attrs = $xml->attributes();
    	if (isset($attrs[$key]) === false) {
    		$xml->addAttribute($key, $value);
    	} else {
    		$attrs[$key] = $value;
    	}
    	
    	return $this;
    }
}
