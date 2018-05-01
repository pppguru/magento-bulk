<?php
/* @copyright   Copyright (c) 2013 Extendware. (http://www.extendware.com) */

class Extendware {
	static public function helper($name) {
		$registryKey = '_helper/' . $name;
        if (!Mage::registry($registryKey)) {
        	$class = Mage::getConfig()->getHelperClassName($name); 
        	if (@class_exists($class) === true) return Mage::helper($name);
        	return false;
        }
        return Mage::registry($registryKey);
	}
}