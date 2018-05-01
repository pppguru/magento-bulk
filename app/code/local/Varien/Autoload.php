<?php
if (defined('DS') === false) define('DS', DIRECTORY_SEPARATOR);
if (defined('PS') === false) define('PS', PATH_SEPARATOR);
if (defined('BP') === false) define('BP', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
__ewDependencyCheck();
// we always must do this if compilation is enabled due to the double declaration of Zend_Cache_Backend otherwise
if (defined('COMPILER_INCLUDE_PATH') === true or class_exists('Extendware_EWCore_Model_Autoload') === false) {
	if (defined('COMPILER_INCLUDE_PATH')) include_once(COMPILER_INCLUDE_PATH . DS . 'Varien/Autoload.php');
	else include_once(BP . DS . 'lib/Varien/Autoload.php');
	Varien_Autoload::register();
}

// get our custom auto loader
$autoloader = new Extendware_EWCore_Model_Autoload();

// you can set this to false and it will increase performance. you will need to ensure that you
// clear extendware core cache whenever you edit files (edit core files, update extensions, etc).
// $autoloader->setOption('check_mtime', false);

// use this to use [magento root]/var/extendware/system/memfs/ for override files whether.
// this is mostly useful only if memfs directory is a mounted ram disk. this has the potential
// to speed some information such as checking mtime or existence of the file
// $autoloader->setOption('override_path', '/full/path/to/directory');

// change this to affect the permissions used for written files
// $autoloader->setOption('umask', 0777);

// if you have turned of file modification checking in APC, then force file evaluation
// this will make it so apc does not cache the file. note: disabling stat checking in apc
// is NOT recommended as it can cause issues in magento under certain circumstances
if ((ini_get('apc.stat') != '' and !ini_get('apc.stat')) or (ini_get('eaccelerator.check_mtime') != '' and !ini_get('eaccelerator.check_mtime')) or (ini_get('opcache.validate_timestamps') != '' and !ini_get('opcache.validate_timestamps'))) {
	$autoloader->setOption('force_php_evaluation', true);
}

// manually add overrides that need to be set before we initalize
// aitoc will delete this autload, so we must override it first and then set their autoload after ours
$autoloader->addOverride('model', 'Aitoc_Aitsys_Model_Rewriter_Autoload', 'Extendware_EWCore_Model_Override_Aitoc_Aitsys_Rewriter_Autoload');

// this is used by our cache optimizer extension and it can be deleted if you do not use this extension
$autoloader->addOverride('model', 'Mage_Core_Model_Cache', 'Extendware_EWCacheBackend_Model_Override_Mage_Core_Cache');

// unregister autoloaders (Varien_Autoloader will be added back later by varien code)
$originalAutoloadFunctions = spl_autoload_functions();
if (is_array($originalAutoloadFunctions) === true) { // done due hhvm bug
	foreach ($originalAutoloadFunctions as $function) {
		spl_autoload_unregister($function);
	}
}

// regster our auto loader as the first autoloader
spl_autoload_register(array($autoloader, 'autoload'));


// this is used by the store / currency auto switching. if you do not need this you can delete it
if (isset($_SERVER['REQUEST_METHOD'])) {
	if (@is_file($autoloader->getMemoryReadFilePath(BP.DS.'app'.DS.'etc'.DS.'Extendware_EWAutoSwitcher.php')) === true) {
		$autoloader->setOption('can_load_all', true);
		try {
			$helper = new Extendware_EWAutoSwitcher_Helper_Data();
			if ($helper->getConfig()->isEnabled() === true) {
				if ($helper->getConfig()->doSwitchOnEveryRequest() or empty($_POST) and (isset($_COOKIE['frontend']) === false or isset($_GET['__currency']) === true)) {
					if ($helper->isEnabledForUserAgent() === true and $helper->isEnabledForUrl() === true and $helper->isEnabledForIpAddress() === true) {
						$websiteId = $helper->getDeterminedWebsiteId();
						if (!$websiteId) $websiteId = $helper->getDeterminedWebsiteId('hostname');
						$storeCode = $helper->autoSwitchToStore($websiteId);
						if (!$storeCode and $websiteId > 0) {
							$helper->setCookieSettingsFromWebsite($websiteId);
						}
						$currencyCode = $helper->autoSwitchToCurrency($storeCode, $websiteId);
						$_COOKIE['__ewstore'] = $storeCode;
						$_COOKIE['__ewcurrency'] = $currencyCode;
					}
				}
			}
		} catch (Exception $e) {}
		$autoloader->setOption('can_load_all', false);
	}
}

// this is used by cookie message. if you are not using this you can delete this
if (isset($_SERVER['REQUEST_METHOD']) === true and isset($_COOKIE['ewcountry']) === false) {
	if (@is_file($autoloader->getMemoryReadFilePath(BP.DS.'app'.DS.'etc'.DS.'Extendware_EWCookieMessage.php')) === true) {
		$autoloader->setOption('can_load_all', true);
		try {
			$helper = new Extendware_EWCookieMessage_Helper_Data();
			if ($helper->getConfig()->isEnabled() === true) {
				try {
					$helper->sendCookies();
				} catch (Exception $e) {}
			}
		} catch (Exception $e) {}
		$autoloader->setOption('can_load_all', false);
	}
}

// this is used by the full page cache. if you are not using the full page cache
// then you can delete this and it will not cause any problems.
if (isset($_SERVER['REQUEST_METHOD']) === true) {
	if (@is_file($autoloader->getMemoryReadFilePath(BP.DS.'app'.DS.'etc'.DS.'Extendware_EWPageCache.php')) === true) {
		$autoloader->setOption('can_load_all', true);
		try {
			if (class_exists('Extendware_EWPageCache_Model_Request_Processor_Primary') === true) {
				// error suppression is added because some customers run new extendware core / old page cache
				$content = @Extendware_EWPageCache_Model_Request_Processor_Primary::extractContent();
				if (isset($content{0}) === true) {
					echo $content; 
					exit;
				}
			}
		} catch (Exception $e) {}
		$autoloader->setOption('can_load_all', false);
	}
}

// ensure that the normal autoloader has been included
if (class_exists('Varien_Autoload') === false) {
	if (defined('COMPILER_INCLUDE_PATH')) include_once(COMPILER_INCLUDE_PATH . DS . 'Varien/Autoload.php');
	else include_once(BP . DS . 'lib/Varien/Autoload.php');
}

// add back original autoloaders
if (is_array($originalAutoloadFunctions) === true) { // done due hhvm bug
	foreach ($originalAutoloadFunctions as $function) {
		spl_autoload_register($function);
	}
}

// uncomment this function if you want to enable advanced logging / debugging functions of Extendware Core
/*function __extendwareErrorHandler($errno, $errstr, $errfile, $errline) {
	if ($errno == 0) return false;
	if (!($errno & error_reporting())) return false;
	if (class_exists('Mage', false) === false) return;
	static $maxLogItems = 25; // prevent infinite loops
	if ($maxLogItems-- > 0) {
		$isDeveloperMode = Mage::getIsDeveloperMode();
		Mage::setIsDeveloperMode(true);
		
		try {
			mageCoreErrorHandler($errno, $errstr, $errfile, $errline);
		} catch (Exception $e) {
			if ($isDeveloperMode) {
				throw $e;
			} else {
				if (@class_exists('Zend_Log')) {
					Mage::setIsDeveloperMode($isDeveloperMode);
			        Mage::log($e->getMessage(), Zend_Log::ERR);
			        Mage::setIsDeveloperMode(true);
				}
		    }
		    
		    try {
    			if (Mage::getConfig() and @class_exists('Zend_Log')) {
    		        if (Mage::getStoreConfig('ewcore_developer/system_exception_log/enabled')) {
    			        $file = 'exception_system.log';
    			        Mage::log("\n" . $e->__toString(), Zend_Log::ERR, $file);
    		        }
    			}
		    } catch (Exception $e) {}
		}
		
		Mage::setIsDeveloperMode($isDeveloperMode);
    } else {
    	mageCoreErrorHandler($errno, $errstr, $errfile, $errline);
    }
}*/

// sounds scary but should never be modified as it protects the store
function __ewDisableModule($module) {
	if (class_exists('Mage', false) === false) return;
	try {
		if (Mage::helper('ewcore/config')->isViolationDisablingEnabled() === true) {
			Mage::getModel('compiler/process')->registerIncludePath(false);
			$configTools = Mage::helper('ewcore/config_tools');
			if ($configTools) $configTools->disableModule($module);
		}
	} catch (Exception $e) { Mage::logException($e); }
}

function __ewDependencyCheck() {
	$file = BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Extendware' . DS . 'EWCore' . DS . 'Model' . DS . 'Autoload.php';
	if (@file_exists($file) === false) {
		if (@class_exists('Extendware_EWCore_Model_Autoload') === false and @is_dir(dirname($file)) === true) {
			die(sprintf('Cannot load file %s/app/code/local/Extendware/EWCore/Model/Autoload.php. Please ensure all files have been uploaded. If all files have been uploaded, then your hosting company is deleting files without your permission. <br/><br/>To resolve please login to your extendware.com account and re-download the software. When you redownload the software ensure that you select the PHP version as <b>PHP 5.3 - 5.6 (ic)</b>. Re-uploading all the files from the "ic" package type should resolve this.', BP));
		}
	}
	
	if (function_exists('ioncube_license_properties') === false) {
		$flagFile = DS . 'var' . DS . 'extendware' . DS . 'system' . DS . 'encoded.flag';
		if (@filesize($flagFile) > 0) {
			$files = array();
			$files[] = BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Extendware' . DS . 'EWCore' . DS . 'de.php';
			$files[] = BP . DS . 'app' . DS . 'code' . DS . 'community' . DS . 'Extendware' . DS . 'EWCore' . DS . 'de.php';
			foreach ($files as $file) {
				if (is_file($file)) {
					include $file;
				}
			}
		}
	}
}