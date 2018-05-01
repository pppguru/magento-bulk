<?php
class Extendware_EWCore_Block_Mage_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{
	private $_helperBase;
	private $_moduleName;
	
	protected function mHelper($scope = null) 
    {
    	if ($this->_helperBase === null) {
	    	$nameParts = explode('_', get_class($this));
	    	$this->_helperBase = strtolower($nameParts[1]);
    	}
    	
    	return Mage::helper($this->_helperBase . '/' . ($scope ? $scope : 'data'));
    }
    
	protected function _getModuleName()
    {
        if ($this->_moduleName === null and preg_match('/^([^_]+?\_[^_]+?)\_/', get_class($this), $match)) {
        	$this->_moduleName = $match[1];
        }
        	
        return $this->_moduleName;
    }
    
	protected function _beforeToHtmlHtml() {
    	return null;
    }
    
	protected function _afterToHtmlHtml() {
    	return null;
    }
    
	protected function _toHtml() {
    	$html = $this->_beforeToHtmlHtml();
    	$html .= parent::_toHtml();
    	$html .= $this->_afterToHtmlHtml();
    	return $html;
    }
}
