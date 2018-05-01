<?php
abstract class Extendware_EWCore_Model_Varien_Data_Collection extends Varien_Data_Collection
{
	private $_helperBase;
	private $_moduleName;
	
	public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->_getModuleName());
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }
    
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
}