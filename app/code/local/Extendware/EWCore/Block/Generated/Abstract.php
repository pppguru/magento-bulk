<?php
abstract class Extendware_EWCore_Block_Generated_Abstract extends Extendware_EWCore_Block_Mage_Core_Template
{
    private $cacheKey = null;
    
    public function __construct()
    {
        $this->createHtaccesFile();
    }
    
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getFilename()
    {
        if (@filemtime($this->getTemplateFilePath()) >= @filemtime($this->getCachedFilePath())) {
            $this->_saveCache($this->_toHtml());
        }

        return $this->getCachedFilename();
    }
    
    public function getCachedFilePath() 
    {
        return $this->getCacheDirectory() . DS . $this->getCachedFilename();
    }
    
    protected function _getCacheKey()
    {
        if (!$this->cacheKey) {
            $this->cacheKey = $this->getCacheKey();
        }
        
        return $this->cacheKey;
    }
    
	public function getCacheKey() {
        $key = get_class($this);
        $key .= '-' . Mage::app()->getStore()->getId();
        $key .= '-' . Mage::getDesign()->getPackageName();
        $key .= '-' . Mage::getDesign()->getTheme('template');
		$key .= '-' . (int) @filemtime($this->getTemplateFilePath());
	
        return md5($key);
	}
    
    public function getTemplateFilePath() {
        $this->setScriptPath(Mage::getBaseDir('design'));
        $params = array('_relative'=>true);
        if ($area = $this->getArea()) {
            $params['_area'] = $area;
        }

        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        return Mage::getBaseDir('design') . DS . $templateName;
    }
    
    public function fetchView($fileName)
    {
        extract($this->_viewVars);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        
        include $this->_viewDir.DS.$fileName;

        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }

        return $html;
    }
    
    // never use blocks built in load cache
    protected function _loadCache()
    {
        return false;
    }
    
    protected function createHtaccesFile() {
        $srcFile = Mage::getModuleDir(null, 'Extendware_EWCore') . DS . 'resources' . DS . '.htaccess.template';
		$destFile = $this->getCacheDirectory() . DS . '.htaccess';
		if (@filemtime($srcFile) > @filemtime($destFile)) {
			@copy($srcFile, $destFile);
		}
    }
    
    protected function setCacheDirectory($directory) {
    	if (Mage::getConfig()->getOptions()->createDirIfNotExists($directory) === false) {
    		Mage::throwException($this->__('Could not create directory %s', $directory));
    	}
    	return $this->setData('cache_directory', $directory);
    }
    
     // overwrite how block handles saving
    protected function _saveCache($data)
    {
		if (@file_put_contents($this->getCachedFilePath(), $data, LOCK_EX) === false) {
			@unlink($this->getCachedFilePath());
		}
		return $this;
    }
}

