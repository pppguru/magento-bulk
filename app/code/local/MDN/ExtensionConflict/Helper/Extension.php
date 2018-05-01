<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2015 BoostMyShop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_ExtensionConflict
 */
class MDN_ExtensionConflict_Helper_Extension extends Mage_Core_Helper_Abstract
{
	/**
	 * Return config file path
	 *
	 * @param unknown_type $editor
	 * @param unknown_type $module
	 */
	public function getConfigFilePath($editor, $module)
	{
		$moduleInfo = mage::getConfig()->getModuleConfig($editor . '_' . $module);
		try {
			$moduleInfo = $moduleInfo->asArray();
			$path = mage::getBaseDir('base') . DS . 'app' . DS . 'code' . DS . $moduleInfo['codePool'] . DS . $editor . DS . $module . DS . 'etc' . DS . 'config.xml';
		} catch (Exception $ex) {
			echo 'Error getting config file path for "' . $editor . '_' . $module . '" : ' . $ex->getMessage();
			die();
		}
		return $path;
	}

	/**
	 * Return class path
	 *
	 * @param unknown_type $class
	 */
	public function getClassPath($class)
	{
		$classArray = explode('_', $class);
		$editor = trim($classArray[0]);
		$module = trim($classArray[1]);

		$moduleInfo = mage::getConfig()->getModuleConfig($editor . '_' . $module);
		$moduleInfo = $moduleInfo->asArray();

		$path = mage::getBaseDir('base') . DS . 'app' . DS . 'code' . DS . $moduleInfo['codePool'] . DS . str_replace('_', DS, $class) . '.php';
		return $path;

	}

	/**
	 * Return class declaration
	 *
	 * @param unknown_type $class
	 */
	public function getClassDeclaration($class)
	{
		//instantiate class
		$obj = new $class();
		$ref = new ReflectionObject($obj);
		$parentClass = $ref->getParentClass()->getname();

		$declaration = 'class ' . $class . ' extends ' . $parentClass;

		return $declaration;
	}

	/**
	 * Return installed extensions
	 *
	 */
	public function getInstalledExtensions()
	{
		return Mage::getConfig()->getNode('modules')->children();
	}

	public function disableExtension($moduleName){
		$this->modifyConfigFile($moduleName,false);
		$this->refreshMagentoConfCache();
	}

	public function enableExtension($moduleName){
		$this->modifyConfigFile($moduleName,true);
		$this->refreshMagentoConfCache();
	}

	public function findModuleConfigFilePath($moduleName){
		$moduleFilePath = '';
		$modulesConfDir = Mage::getConfig()->getBaseDir().DS.'app'.DS.'etc'.DS.'modules'.DS;
		$configFilePath = $modulesConfDir.$moduleName.'.xml';
		if(file_exists($configFilePath)){
			$moduleFilePath = $configFilePath;
		}else{
			$loopCount = 0;
			$maxLoop = 500;
			$handle = opendir($modulesConfDir);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					$loopCount ++;
					if ($loopCount>$maxLoop) {
						break;
					}
					if ($file != '.' && $file != '..') {
						$currentFilePath = $modulesConfDir . $file;
						if (is_file($currentFilePath)) {
							$fileAsString = file_get_contents($currentFilePath);
							if(strpos($fileAsString,$moduleName) != false){
								$moduleFilePath = $currentFilePath;
								break;
							}
						}
					}
				}
			}
			closedir($handle);
		}
		return $moduleFilePath;

	}
	public function modifyConfigFile($moduleName,$enable){
		$configFilePath = $this->findModuleConfigFilePath($moduleName);
		if(file_exists($configFilePath)){
			@chmod($configFilePath, 755);
			$fileAsString = file_get_contents($configFilePath);
			if($enable){
				$origin = '<active>false</active>';
				$target = '<active>true</active>';
			}else{
				$origin = '<active>true</active>';
				$target = '<active>false</active>';
			}
			$fileAsString = str_replace($origin,$target,$fileAsString);
			file_put_contents($configFilePath,$fileAsString);
		}
	}

	public function refreshMagentoConfCache(){

		Mage::app()->getCacheInstance()->cleanType('config');
		Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => 'config'));

		Mage::app()->getCacheInstance()->cleanType('block');
		Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => 'block'));
	}


}