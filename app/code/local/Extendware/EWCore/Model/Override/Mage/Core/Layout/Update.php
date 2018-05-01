<?php
// This is a very simple override that allows adding conditionals to update handles. It should not affect anything
class Extendware_EWCore_Model_Override_Mage_Core_Layout_Update extends Extendware_EWCore_Model_Override_Mage_Core_Layout_Update_Bridge
{
	public function fetchRecursiveUpdates($updateXml)
    {
        foreach ($updateXml->children() as $node) {
	        if (isset($node['ewifconfig']) && ($configPath = (string)$node['ewifconfig'])) {
	            if (!Mage::getStoreConfigFlag($configPath)) {
	                $node['handle'] = null;
	            }
	        }
	        
	        if (isset($node['ewifhelper'])) {
				$helperName = explode('/', (string)$node['ewifhelper']);
				$helperMethod = array_pop($helperName);
				$helperName = implode('/', $helperName);
	
				if (!call_user_func_array(array(Mage::helper($helperName), $helperMethod), array())) {
					$node['handle'] = null;
				}
	        }
        }
        
        return parent::fetchRecursiveUpdates($updateXml);
    }
}
