<?php

abstract class Extendware_EWCore_Model_Module_Updater_Abstract extends Extendware_EWCore_Model_Varien_Object
{
	public function _construct()
	{
		$this->setApi(Mage::getModel('ewcore/extendware_api'));
		return parent::_construct();
	}
	
	protected function writeCombinedToDatabase(Extendware_EWCore_Model_Module_Item $module, $type, $data) {
		if (Mage::helper('ewcore/config')->isDatabaseFallbackEnabled() === false) return null;
		try {
			$combined = Mage::getSingleton('ewcore/config')->getConfig('ewcore/__system/license');
			$combined = @unserialize(gzuncompress(base64_decode($combined)));
			if (is_array($combined) === false) {
				$combined = array();
			}
			
			$value = @unserialize($combined['value']);
			if (is_array($value) === false) $value = array();
			if (!isset($value[$type]) or is_array($value[$type]) === false) $value[$type] = array();
			$value[$type][$module->getId()] = $data;
			
			$combined['value'] = serialize($value);
			
			
			$combined['version'] = '1.0';
			$combined['hash'] = md5(sprintf('%s--combined', $combined['value']));
			
			$data = @base64_encode(gzcompress(serialize($combined), 9));
			Mage::getSingleton('ewcore/config')->setConfig('ewcore/__system/license', $data);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	protected function writeCombinedFile(Extendware_EWCore_Model_Module_Item $module, $file, $data) {
		$combinedData = @eval(preg_replace('/^<\?php/', '', file_get_contents($file), 1));
		if (is_array($combinedData) === false) $combinedData = array();
		$combinedData[$module->getId()] = $data;
		
		$codeToEval = sprintf('return %s;', Mage::helper('ewcore/utility')->varExportMinified($combinedData, true));
		$code = "<?php /*@copyright Extendware*/\n";
		$code .= sprintf("\$__func = strrev('edoced_'.'46esab'); return eval(\$__func('%s'));", base64_encode($codeToEval));
		
		if (@$this->mHelper('licensing')->writeFileSafely($file, $code, false) === false) {
			@unlink($file);
			$this->log($this->__('Updater could not write combined file (%s). You should change file permissions so file can be written.', $file));
		}
		
		if (Extendware_EWCore_Model_Autoload::getOptionFlag('use_memfs') === true) {
			$file = Extendware_EWCore_Model_Autoload::getMemoryPathFromPath($file);
			if (@$this->mHelper('licensing')->writeFileSafely($file, $code) === false) {
				@unlink($file);
				$this->log($this->__('Updater could not write combined file (%s). You should change file permissions so file can be written.', $file));
			}
		}
		return $this;
	}
	
	protected function writeCombinedContentsFile(Extendware_EWCore_Model_Module_Item $module, $file, $data) {
		$contents = @file_get_contents($file);
		$combinedData = @unserialize($contents);
		if (is_array($combinedData) === false) $combinedData = array();
		$combinedData[$module->getId()] = array(
			'value' => $data,
		);
		
		$serializedCombinedData = serialize($combinedData);
		if (@$this->mHelper('licensing')->writeFileSafely($file, $serializedCombinedData, false) === false) {
			@unlink($file);
			$this->log($this->__('Updater could not write combined contents file (%s). You should change file permissions so file can be written.', $file));
		}
		
		if (Extendware_EWCore_Model_Autoload::getOptionFlag('use_memfs') === true) {
			$memoryCombinedFile = Extendware_EWCore_Model_Autoload::getMemoryPathFromPath($file);
			if (@$this->mHelper('licensing')->writeFileSafely($memoryCombinedFile, $serializedCombinedData) === false) {
				@unlink($memoryCombinedFile);
				$this->log($this->__('Updater could not write combined contents file (%s). You should change file permissions so file can be written.', $file));
			}
		}
		return $this;
	}
	
	protected function log($message) {
		$this->mHelper('system')->log($message, true);
		return $this;
	}
}