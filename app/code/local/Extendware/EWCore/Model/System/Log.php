<?php

class Extendware_EWCore_Model_System_Log extends Extendware_EWCore_Model_Varien_Object
{

	public function getFileCollection($forceShow = false)
	{
		$collection = Mage::getModel('ewcore/system_log_file_collection');
		$files = $this->mHelper('file')->getFilesInDirectory(Mage::getBaseDir('log'), true);
		
		if ($forceShow === false and $this->mHelper('config')->isShowLogsEnabled() === false) {
			foreach ($files as $k => $v) {
				if (strpos($v, 'log' . DS . 'extendware') === false and strpos($v, 'exception_verbose.log') === false and strpos($v, 'exception_system.log') === false) {
					unset($files[$k]);
				}
			}
		}
		
		// sort the files in descending order of filemtime()
		$filesToTime = array();
		foreach ($files as $file) {
			$filesToTime[$file] = filemtime($file);
		}
		
		asort($filesToTime, SORT_NUMERIC);
		$files = array_reverse(array_keys($filesToTime));


		foreach ($files as $filePath) {
			$file = Mage::getModel('ewcore/system_log_file');
			$file->setPath($filePath);
			
			$collection->addItem($file);
		}
		
		return $collection;
	}
}