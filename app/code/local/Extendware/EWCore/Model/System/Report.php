<?php

class Extendware_EWCore_Model_System_Report extends Extendware_EWCore_Model_Varien_Object
{

	public function getFileCollection($forceShow = false)
	{
		$collection = Mage::getModel('ewcore/system_report_file_collection');
		$files = $this->mHelper('file')->getFilesInDirectory(Mage::getBaseDir() . DS . 'var' . DS . 'report');
		
		if ($forceShow === false and $this->mHelper('config')->isShowReportsEnabled() === false) {
			$files = array();
		}
		
		// sort the files in descending order of filemtime()
		$filesToTime = array();
		foreach ($files as $file) {
			$filesToTime[$file] = filemtime($file);
		}
		
		asort($filesToTime, SORT_NUMERIC);
		$files = array_reverse(array_keys($filesToTime));


		foreach ($files as $filePath) {
			$file = Mage::getModel('ewcore/system_report_file');
			$file->setPath($filePath);
			
			$collection->addItem($file);
		}
		
		return $collection;
	}
}