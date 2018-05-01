<?php

class Extendware_EWCore_Model_System_Report_File extends Extendware_EWCore_Model_Varien_Object
{
	
	public function loadById($id) {
		return $this->load($id);
	}
	
	public function load($id) {
		$collection = Mage::getModel('ewcore/system_report')->getFileCollection(true);
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
			Mage::throwException($this->__('Could not delete report file'));
		}
		
		return $this;
	}
	
	public function getParsedData()
	{
		if ($this->getData('parsed_data') === null) {
			$data = false;
			
			$report = unserialize(file_get_contents($this->getPath()));
			if (is_array($report) === true and count($report)) {
				$data = @array(
					'url' => $report['url'],
					'skin' => $report['skin'],
					'script_name' => $report['script_name'],
					'message'	=> $report[0],
					'trace'		=> array()
				);
				
				$trace = preg_split('/\#\d+\s+/', (string) @$report[1]);
				foreach ($trace as $item) {
					if (preg_match('/(.+?)\s*\((\d+)\)\:\s*(.+)/si', $item, $match)) {
						$data['trace'][] = array(
								'file' => $match[1],
								'line' => $match[2],
								'caller' => $match[3]
						);
					}
				}
			}
			
			$this->setData('parsed_data', $data);
		}
		
		return $this->getData('parsed_data');
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
}