<?php
class Extendware_EWCore_Model_Override_Mage_Compiler_Process extends Extendware_EWCore_Model_Override_Mage_Compiler_Process_Bridge
{
	public function run() {
		self::protectFromBadCompilation();
		return parent::run();
	}
	
	public function registerIncludePath($flag = true)
    {
        if ($flag) {
        	self::protectFromBadCompilation();
        }
        return parent::registerIncludePath($flag);
    }
    
    private function protectFromBadCompilation() {
    	$disableUnkonwnCompiler = true;
    	// [[if normal]]
    	$disableUnkonwnCompiler = Mage::helper('ewcore/config')->doDisableUnknownCompiler();
    	// [[/if]]
   		if ($disableUnkonwnCompiler === true) {
			if (get_class($this) != 'Mage_Compiler_Model_Process') {
				Mage::throwException(Mage::helper('ewcore')->__('Compilation is disabled because compilation has been rewritten by an extension that can cause compatability issues. Deleting this rewrite may solve the issue: %s', get_class($this)));
			}
		}
		return $this;
    }
    
	protected function removeOverriddenClasses($classes, array $types = null)
	{
		$overriddenClasses = Extendware_EWCore_Model_Autoload::getAffectedClassesList($types);
		
		foreach ($overriddenClasses as $class) {
    		$index = array_search($class, $classes);
    		if ($index !== false) {
    			unset($classes[$index]);
    		}
    	}
    	
    	return $classes;
	}
	
	protected function isAffectedOverriddenSearchClass($class) 
	{
		$overriddenClasses = Extendware_EWCore_Model_Autoload::getAffectedClassesList(array('search'));
		return in_array($class, $overriddenClasses);
	}
	
	
	protected function _getClassesSourceCode($classes, $scope)
    {
        $sortedClasses = array();
        foreach ($classes as $className) {
        	if (class_exists($className) === false) continue;
            $implements = array_reverse(class_implements($className));
            foreach ($implements as $class) {
                if (!in_array($class, $sortedClasses) && !in_array($class, $this->_processedClasses) && strstr($class, '_')) {
                    $sortedClasses[] = $class;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $class;
                    }
                }
            }
            $extends    = array_reverse(class_parents($className));
            foreach ($extends as $class) {
                if (!in_array($class, $sortedClasses) && !in_array($class, $this->_processedClasses) && strstr($class, '_')) {
                    $sortedClasses[] = $class;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $class;
                    }
                }
            }
            if (!in_array($className, $sortedClasses) && !in_array($className, $this->_processedClasses)) {
                $sortedClasses[] = $className;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $className;
                    }
            }
        }
        
        // remove all the classes that are included in the overridden classes include file (created by extendware ewcore)
        $sortedClasses = $this->removeOverriddenClasses($sortedClasses, array('search_alias', 'replace', 'bridge'));
        
        $classesSource = "<?php\n";
        foreach ($sortedClasses as $className) {
        	$file = null;
        	if ($this->isAffectedOverriddenSearchClass($className)) {
        		$file = Extendware_EWCore_Model_Autoload::getIncludeFileFor($className);
        	} else {
            	$file = $this->_includeDir.DS.$className.'.php';
        	}
            if (!file_exists($file)) {
                continue;
            }
            $content = file_get_contents($file);
            $content = ltrim($content, '<?php');
            $content = rtrim($content, "\n\r\t?>");
            $content = $this->wrapClass($className, $content, $scope);
            $classesSource.= $content;
        }
        
        return $classesSource;
    }
    
    private function wrapClass($class, $content, $scope = 'default') {
    	if ($this->isWrappingAllowed($scope) === true) {
    		$content = "\nif (class_exists('" . $class . "', false) === false and interface_exists('". $class ."', false) === false) {\n" . $content . "\n}\n";
    	}
    	
    	return $content;
    }
    
    private function isWrappingAllowed($scope) {
    	if ($scope == 'default') return false;
    	$modules = array('Extendware_EWPageCache');
    	foreach ($modules as $name) {
			if (Mage::helper('ewcore/environment')->isModuleActive($name)) {
				return true;
			}
    	}
    	
    	return false;
    }
}
