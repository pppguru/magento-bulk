<?php
class AW_Followupemail_Helper_Image extends Mage_Catalog_Helper_Image
{
    protected $_resourceFile    = null;
    protected $_handle          = null;

    public function processImage()
    {
        // Process Image model
        $this->__toString();
    }

    public function getResizedImageFile()
    {
        return $this->_getModel()->getNewFile();
    }

    public function getContentType()
    {
        $fileType = pathinfo($this->getResizedImageFile(), PATHINFO_EXTENSION);
        switch (strtolower($fileType)) {
            case 'gif':
                $contentType = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $contentType = 'image/jpeg';
                break;
            case 'png':
                $contentType = 'image/png';
                break;
            default:
                $contentType = '';
        }
        return $contentType;
    }

    public function getFileContent()
    {
        $this->_resourceFile = $this->getPathToFile();
        try {
            if ($this->_resourceFile) {
                if (is_null($this->_handle)) {
                    $this->_handle = new Varien_Io_File();
                    $this->_handle->open(array('path'=>Mage::getBaseDir('var')));
                    if (!$this->_handle->fileExists($this->_resourceFile, true)) {
                        Mage::throwException(Mage::helper('followupemail')->__('The file does not exist.'));
                    }
                    return $this->_handle->read($this->_resourceFile);
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return null;
    }

    protected function getPathToFile()
    {
        return dirname($this->getResizedImageFile()) . DS . basename($this->getResizedImageFile());
    }
}