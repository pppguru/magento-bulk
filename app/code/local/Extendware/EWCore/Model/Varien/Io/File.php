<?php
class Extendware_EWCore_Model_Varien_Io_File extends Varien_Io_File 
{
	public function ls($grep = null) {
		$directories = parent::ls($grep);
		if (is_array($directories)) {
			foreach ($directories as $key => $directory) {
				if (isset($directory['id']) === false) {
					$directories[$key]['id'] = $this->_cwd . DS . $directory['text'];
				}
			}
		}
		
		return $directories;
	}
	public function lsRecursive($grep=null, $depth = 0)
	{
		$currentDir = $this->_cwd;
		
		$directories = $this->ls();
		$subdirectories = array();
		foreach ($directories as $directory) {
			if ($directory['leaf'] === false) {
				if ($this->cd($directory['id']) === true) {
					$dirs = $this->lsRecursive($grep, $depth + 1);
					if (is_array($dirs)) {
						$subdirectories = array_merge($subdirectories, $dirs);
					}
				} else {
					Mage::throwException($this->__('Unable to list current working directory. Access forbidden.'));
		        }
				
				
			}
		}
		
		$directories = array_merge($directories, $subdirectories);
		
		if (!$depth) {
			$this->cd($currentDir);
			$processedDirectories = array();
			if (!$grep) $processedDirectories  = $directories;
			else {
				foreach ($directories as $directory) {
					if($grep == self::GREP_DIRS and is_dir($directory['id'])) {
	                    $processedDirectories[] = $directory;
	                } elseif($grep == self::GREP_FILES and is_file($directory['id'])) {
	                    $processedDirectories[] = $directory;
	                }
				}
			}
			
			return $processedDirectories;
		} else return $directories;
	}
}