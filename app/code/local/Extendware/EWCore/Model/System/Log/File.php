<?php

class Extendware_EWCore_Model_System_Log_File extends Extendware_EWCore_Model_Varien_Object
{
	
	public function loadById($id) {
		return $this->load($id);
	}
	
	public function load($id) {
		$collection = Mage::getModel('ewcore/system_log')->getFileCollection(true);
		foreach ($collection as $file) {
			if ($file->getId() == $id) {
				$this->setPath($file->getPath());
			}
		}
		
		return $this;
	}
	
	public function delete()
	{
		if (@unlink($this->getPath()) === false) {
			Mage::throwException($this->__('Could not delete log file'));
		}
		
		return $this;
	}
	public function setPath($value) {
		$this->setData('path', $value);
		$this->setId(md5($this->getPath()));
		$this->setFileId($this->getId());
		$this->setName(basename($value));
		$this->setRelativePath(str_replace(Mage::getBaseDir(), '', $this->getPath()));
		$this->setSize(sprintf('%.2f', (filesize($this->getPath()) / 1024)) . ' KB');
		$this->setUpdatedAt(filemtime($this->getPath()));
		
		return $this;
	}
	
	public function getLineCollection()
	{
		$maxCollectionSize = max(1, $this->mHelper('config')->getLogViewerMaxCollectionSize());
		
		$regExp = '/(\d+-\d+-\d+T\d+[0-9\-+T:]+?)\s+?([A-Z]+?)\s+?\((\d+)\)\:\s+?(.+?)(?:(?=(?:\d+-\d+-\d+T\d+[0-9\-+T:]+?))|$)/si';
		
		$contents = file_get_contents($this->getPath());
		
		$lines = array();
		if (preg_match_all($regExp, $contents, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$lines[] = $match[0];
			}
		}
		$lines = array_reverse($lines);
		
		$collection = Mage::getModel('ewcore/system_log_file_line_collection');
		foreach ($lines as $line) {
			if ($collection->count() >= $maxCollectionSize) break;
			$line = trim($line);
			
			if (preg_match($regExp, $line, $match)) {
				list ($date, $priorityName, $priority, $message) = array_slice($match, 1);
				$message = trim($message);
				
				if ($date and $priorityName and $priority and $message) {
					$item = new Varien_Object();
					$item->setDate($date);
					$item->setPriorityName($priorityName);
					$item->setPriority($priority);
					$item->setMessage($message);
					
					$collection->addItem($item);
				}
			}
			
		}

		return $collection;
	}
}