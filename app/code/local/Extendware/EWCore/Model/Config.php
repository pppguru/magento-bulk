<?php

class Extendware_EWCore_Model_Config extends Extendware_EWCore_Model_Singleton_Abstract
{
	static protected $data = null;
	
	protected function getProcessedValues($scope, $scopeId) {
		if (!$scopeId) $scope = 'default';
		if ($scope == 'default') $scopeId = 0;
		if (in_array($scope, array('default', 'stores', 'website', 'config')) === false) {
			Mage::throwException($this->__('Invalid scope entered for config item'));
		}
		return array($scope, (int)$scopeId);
	}
	
	public function setConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
    	list($scope, $scopeId) = $this->getProcessedValues($scope, $scopeId);
    	$data = array('path' => trim($path, '/'), 'scope' => $scope, 'scope_id' => $scopeId);
    	$model = Mage::getModel('ewcore/config_data')->loadByData($data);
	    	$model->addData($data);
	    	$model->setValue($value);
    	$model->save();
    	
    	$this->updateCache($path, $value, $scope, $scopeId);
        return $this;
    }
    
	public function deleteConfig($path, $scope = 'default', $scopeId = 0)
    {
    	list($scope, $scopeId) = $this->getProcessedValues($scope, $scopeId);
    	$data = array('path' => trim($path, '/'), 'scope' => $scope, 'scope_id' => $scopeId);
    	$model = Mage::getModel('ewcore/config_data')->loadByData($data);
	    if ($model->getId() > 0) $model->delete();
		$this->deleteFromCache($path, $scope, $scopeId);
        return $this;
    }
    
    public function cleanCache() {
    	self::$data = null;
    	return $this;
    }
    
    protected function hasConfigData() {
    	return isset(self::$data);
    }
    
    public function getConfigData() {
    	if (self::$data === null) {
    		self::$data = array();
    		$collection = Mage::getResourceModel('ewcore/config_data_collection');
    		$data = $collection->getData();
    		foreach ($data as $item) {
    			if (isset(self::$data[$item['scope']]) === false) {
    				self::$data[$item['scope']] = array();
    			}
    			
    			if (isset(self::$data[$item['scope']][$item['scope_id']]) === false) {
    				self::$data[$item['scope']][$item['scope_id']] = array();
    			}
    			
    			self::$data[$item['scope']][$item['scope_id']][$item['path']] = $item['value'];
    		}
    	}
    	
    	return self::$data;
    }
    
	protected function updateCache($path, $value, $scope = 'default', $scopeId = 0) {
		if (self::$data === null) return $this;
    	list($scope, $scopeId) = $this->getProcessedValues($scope, $scopeId);
    	if (isset(self::$data[$scope]) === false) {
			self::$data[$scope] = array();
		}
		
		if (isset(self::$data[$scope][$scopeId]) === false) {
			self::$data[$scope][$scopeId] = array();
		}
		
		self::$data[$scope][$scopeId][$path] = $value;
        return $this;
    }
    
    protected function deleteFromCache($path, $scope = 'default', $scopeId = 0) {
    	list($scope, $scopeId) = $this->getProcessedValues($scope, $scopeId);
    	unset(self::$data[$scope][$scopeId][$path]);
    	return $this;
    }
    
    protected function getDefaultConfig($path) {
    	$data = $this->getConfigData();
    	if (isset($data['default'][0][$path]) === true) {
    		return $data['default'][0][$path];
    	}
    	
    	return null;
    }
    
    public function getConfig($path, $scope = 'default', $scopeId = 0) {
    	list($scope, $scopeId) = $this->getProcessedValues($scope, $scopeId);
    	$data = $this->getConfigData();
    	
    	if ($scope == 'default') {
	    	$defaultConfig = $this->getDefaultConfig($path);
	    	if ($defaultConfig !== null) {
	    		return $defaultConfig;
	    	}
    	}
    	
    	if ($scope == 'stores') {
    		return $this->getConfigForStore($path, $scopeId);
    	} elseif ($scope == 'websites') {
    		return $this->getConfigForWebsite($path, $scopeId);
    	}
    	
    	return null;
    }
    
    public function getConfigForStore($path, $store = null) {
    	if (!$store) $store = null;
    	$store = Mage::app()->getStore($store);
    	if (!$store) return null;
    	
    	$data = $this->getConfigData();
    	if (isset($data['stores'][$store->getId()][$path]) === true) {
    		return $data['stores'][$store->getId()][$path];
    	}
    	
    	return $this->getConfigForWebsite($path, $store->getWebsiteId());
    }
    
	public function getConfigForWebsite($path, $website = null) {
    	if (!$website) $website = null;
    	$website = Mage::app()->getWebsite($website);
    	if (!$website) return null;
    	
    	$data = $this->getConfigData();
    	if (isset($data['websites'][$website->getId()][$path]) === true) {
    		return $data['websites'][$website->getId()][$path];
    	}
    	
    	return $this->getDefaultConfig($path);
    }
	public function getResourceModel()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('core/config_data');
        }
        return $this->_resourceModel;
    }
}