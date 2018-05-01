<?php
class Extendware_EWPageCache_Block_Override_Mage_Core_Template extends Extendware_EWPageCache_Block_Override_Mage_Core_Template_Bridge
{
	protected function _toHtml()
    {
    	static $blockTypeToMarker = null;
    	static $blockTypeToTags = null;
    	if ($blockTypeToMarker === null) $blockTypeToMarker = Mage::helper('ewpagecache/config')->getInjectorsList();
    	if ($blockTypeToTags === null) $blockTypeToTags = Mage::helper('ewpagecache/config')->getTaggingBlockTags();
    	
    	if (isset($blockTypeToTags[$this->getType()]) === true) {
    		Mage::helper('ewpagecache/api')->addTagsForSave($blockTypeToTags[$this->getType()]);
    	}
    	$html = parent::_toHtml();
    	if (isset($blockTypeToMarker[$this->getType()]) === true) {
    		$key = $blockTypeToMarker[$this->getType()];
    		$helper = Mage::helper('ewpagecache');
    		return $helper->getBeginMarker($key, array('template' => $this->getTemplate(), 'type' => $this->getType())) . $html . $helper->getEndMarker($key);
    	}
    	
        return $html;
    }
    
///////////////////////////////////////////////////////////
// AITOC DYNAMIC TEMPLATES COMPATIBILITY
///////////////////////////////////////////////////////////
	public function getTemplateFile() 
	{
        $file = parent::getTemplateFile();
        if (strpos($file, 'aitcommonfiles') !== false) {
        	$currentViewDir = $this->_viewDir;
        	if (Mage::getStoreConfigFlag('aitsys/patches/use_dynamic') || is_file($currentViewDir . DS . $file) === false) {
				$newViewDir = Mage::getBaseDir('var') . DS . 'ait_patch' . DS . 'design';
				if (is_file($newViewDir . DS . $file) === true) {
					$this->_viewDir = $newViewDir;
				} else{
                    $fileDefault = str_replace(DS . 'base' . DS, DS . 'default' . DS, $file);
                    if (file_exists($newViewDir . DS . $fileDefault)) {
                        $this->_viewDir = $newViewDir;
                        $file = $fileDefault;
                    }
                }
			}
		}
        return $file;
    }
}
