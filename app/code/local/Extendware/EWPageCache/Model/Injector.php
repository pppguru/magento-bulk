<?php
class Extendware_EWPageCache_Model_Injector extends Extendware_EWCore_Model_Singleton_Abstract
{
	static public function load($id)
    {
    	return self::factory($id);
    }
    
	static public function factory($id, $canLog = true)
    {
    	$id = strtolower($id);
    	if ($id != 'abstract' and $id != 'interface') {
    		$model = null;
    		
    		$class = 'Extendware_EWPageCache_Model_Injector_' . uc_words($id);
    		$model = false;
    		if (class_exists($class)) {
    			$model = new $class();
    		}
	    	if ($model and $model instanceof Extendware_EWPageCache_Model_Injector_Abstract) {
	    		$model->setId($id);
	    		return $model;
	    	}
	    	
	    	// not in object context so do not use $this
	    	if ($canLog) Mage::helper('ewpagecache/system')->log(Mage::helper('ewpagecache')->__('Could not load injector: %s', $id));
    	}
    	return false;
    }
}
