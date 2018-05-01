<?php
class Extendware_EWCore_Model_Override_Aitoc_Aitsys_Rewriter_Autoload extends Extendware_EWCore_Model_Override_Aitoc_Aitsys_Rewriter_Autoload_Bridge
{
	static public function getRegisterVersion() {
		if (property_exists('Aitoc_Aitsys_Model_Rewriter_Autoload', '_registered')) {
			return 2;
		}
		
		return 1;
	}
	
	static public function register($bool = false) {
		if (class_exists('Varien_Autoload', false) === false) {
			if (defined('COMPILER_INCLUDE_PATH')) include_once(COMPILER_INCLUDE_PATH . DS . 'Varien/Autoload.php');
			else include_once(BP . DS . 'lib/Varien/Autoload.php');
		}
		
		if (self::getRegisterVersion() == 2) {
			return self::register2($bool);
		}
		
		return self::register1($bool);
	}
	
	static public function register1( $base = false )
    {
        if (defined('COMPILER_INCLUDE_PATH'))
        {
            $paths = array();
            $paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
            $paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
            #$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'core';
            #$paths[] = BP . DS . 'lib';

            $appPath = implode(PS, $paths);
            set_include_path($appPath . PS . get_include_path());
        }
        
        $rewriter = new Aitoc_Aitsys_Model_Rewriter();
        $rewriter->preRegisterAutoloader($base);
        
        // unregistering all, and varien autoloaders to make our performing first
        $firstAutoloader = null;
        $autoloaders = spl_autoload_functions();
        if ($autoloaders and is_array($autoloaders) && !empty($autoloaders))
        {
        	$firstAutoloader = $autoloaders[0];
            foreach ($autoloaders as $autoloader)
            {
                spl_autoload_unregister($autoloader);
            }
        }
        
        // begin additions
        if (is_callable($firstAutoloader)) {
        	spl_autoload_register($firstAutoloader);
        }
        // end additions
        
        if (version_compare(Mage::getVersion(),'1.3.1','>'))
        {
            spl_autoload_unregister(array(Varien_Autoload::instance(), 'autoload'));
        }
        spl_autoload_register(array(self::instance(), 'autoload'), false);
        if (version_compare(Mage::getVersion(),'1.3.1','>'))
        {
            Varien_Autoload::register();
        }
        else
        {
            spl_autoload_register(array(self::instance(), 'performStandardAutoload'));
            #self::_loadOverwrittenClasses();
        }
    }
    
	static public function register2($stopProcessing = false)
    {
        if (!$stopProcessing && !self::$_registered) {
            $rewriter = new Aitoc_Aitsys_Model_Rewriter();
            $rewriter->preRegisterAutoloader();
            
            // unregistering all autoloaders to make our performing first
            $autoloaders = spl_autoload_functions();
            $firstAutoloader = $autoloaders[0]; // EXTENDWARE CHANGE
            if ($autoloaders && is_array($autoloaders) && !empty($autoloaders)) {
                foreach ($autoloaders as $autoloader) {
                    spl_autoload_unregister($autoloader);
                }
            }
    
            // EXTENDWARE CHANGE
            if (is_callable($firstAutoloader)) {
            	spl_autoload_register($firstAutoloader, false);
            }
            
            // register our autoloader
            spl_autoload_register(array(self::instance(), 'autoload'), false);
            
            // register 1.3.1 and older autoloader
            if (version_compare(Mage::getVersion(),'1.3.1','le')) {
                spl_autoload_register(array(self::instance(), 'performStandardAutoload'), true);
            }
            
            // register back all unregistered autoloaders
            if ($autoloaders && is_array($autoloaders) && !empty($autoloaders)) {
                foreach ($autoloaders as $autoloader) {
                    spl_autoload_register($autoloader, (is_array($autoloader) && $autoloader[0] instanceof Varien_Autoload));
                }
            }
            self::$_registered = true;
        }
    }
}
