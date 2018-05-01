<?php
class Extendware_EWMinify_Model_Override_Mage_Core_Layout_Update extends Extendware_EWMinify_Model_Override_Mage_Core_Layout_Update_Bridge
{
	private $handleOrder = array();
	private $handleToHandle = array();
	
	public function addHandle($handle)
    {
        if (is_array($handle)) {
            foreach ($handle as $h) $this->addHandle($h);
        } else {
            if (in_array($handle, $this->handleOrder) === false) {
            	$this->handleOrder[] = $handle;
            }
        }
        
        return parent::addHandle($handle);
    }
    
	private function ewfetchPackageLayoutUpdates($handle, $parentHandle = null, $inherit = false)
    {
    	if (empty($this->_packageLayout)) {
            $this->fetchFileLayoutUpdates();
        }
		
        $loops = 500;
        $currentHandle = $inherit === false ? $handle : $parentHandle;
        while (isset($this->handleToHandle[$currentHandle]) and $currentHandle != $this->handleToHandle[$currentHandle] and $loops--) {
        	$currentHandle = $this->handleToHandle[$currentHandle];
        }
        $this->handleToHandle[$handle] = $currentHandle;
        
        if (isset($this->_packageLayout->$handle)) {
	        foreach ($this->_packageLayout->$handle as $updateXml) {
	    		foreach ($updateXml as $innerXml) {
	    			self::writeXmlAttribute($innerXml, '_ewminify_handle', $handle);
	    			self::writeXmlAttribute($innerXml, '_ewminify_parent_handle', $parentHandle);
	    			self::writeXmlAttribute($innerXml, '_ewminify_default_group', $this->handleToHandle[$handle]);
					
	    			// detect the original html/head block and mark it
					if ($handle == 'default' and $innerXml->getName() == 'block' and $innerXml->getAttribute('type') == 'page/html') {
						foreach ($innerXml as $innerXml2) {
							self::writeXmlAttribute($innerXml2, '_ewminify_handle', $handle);
							self::writeXmlAttribute($innerXml2, '_ewminify_parent_handle', $parentHandle);
							self::writeXmlAttribute($innerXml2, '_ewminify_default_group', ($inherit === false ? $handle : $parentHandle));
						}
					}
				}
				
				$this->fetchRecursiveUpdates($updateXml);
	            $this->addUpdate($updateXml->innerXml());
	        }
        }
        return true;
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
    
	private function ewmerge($handle, $parentHandle = null, $inherit = false)
    {
        $packageUpdatesStatus = $this->ewfetchPackageLayoutUpdates($handle, $parentHandle, $inherit);
        if (Mage::app()->isInstalled()) {
            $this->fetchDbLayoutUpdates($handle);
        }
        return $this;
    }
    
	public function fetchPackageLayoutUpdates($handle)
    {
    	return $this->ewfetchPackageLayoutUpdates($handle);
    }
    
	public function fetchRecursiveUpdates($updateXml)
    {
    	if ($this instanceof AnattaDesign_MultipleHandles_Model_Core_Layout_Update) {
    		return parent::fetchRecursiveUpdates($updateXml);
    	}
    	
        foreach ($updateXml->children() as $child) {
            if (strtolower($child->getName())=='update' && isset($child['handle'])) {
                $this->ewmerge((string)$child['handle'], $updateXml->getName(), (bool)@$child['ewminify_inherit']);
                $this->addHandle((string)$child['handle']);
            }
        }
        return $this;
    }
}