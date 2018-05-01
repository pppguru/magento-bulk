<?php
final class Extendware_EWCore_Model_Module extends Extendware_EWCore_Model_Singleton_Abstract
{
	static private $modules = array();
	
	static public function load($id)
    {
    	return self::factory($id);
    }
    
	static public function findById($id)
    {
    	foreach (Mage::getConfig()->getNode('modules')->children() as $key => $value) {
    		if (strtolower($id) === strtolower($key)) {
    			return self::factory($key);
    		}
    	}
    	return self::factory($id);
    }
    
	static public function exists($id)
    {
    	return isset(Mage::getConfig()->getNode('modules')->{$id});
    }
    
	static public function factory($id)
    {
    	if (self::exists($id) === false) {
    		$id = null;
    	}
    	
    	if (isset(self::$modules[$id]) === false) {
    		self::$modules[$id] = Mage::getModel('ewcore/module_item')->load($id);
    	}
    	
    	return self::$modules[$id];
    }
    
    static public function getCollection($force = false)
    {
    	static $collection = null;
    	if ($collection === null or $force === true) {
	    	$collection = new Varien_Data_Collection();
	    	$moduleConfig = Mage::getConfig()->getModuleConfig();
	    	foreach ($moduleConfig->children() as $moduleIdentifier => $config) {
	    		if ($collection->getItemById($moduleIdentifier) === null) {
	    			$collection->addItem(self::factory($moduleIdentifier));
	    		}
	    	}
    	}
    	
    	return $collection;
    } 
    
	static public function getSortedCollection($force = false)
    {
    	static $collection = null;
    	if ($collection === null or $force === true) {
	    	$collection = self::getCollection();
	    	$sortedCollection = new Varien_Data_Collection();
	    	
	    	$sortedItems = array();
	    	foreach ($collection as $item) {
	    		$sortedItems[$item->getFriendlyName() . '-' . $item->getId()] = $item;
	    	}
	    	ksort($sortedItems);
	    	foreach ($sortedItems as $item) {
	    		$sortedCollection->addItem($item);
	    	}
	    	$collection = $sortedCollection;
    	}
    	
    	return $collection;
    }
}