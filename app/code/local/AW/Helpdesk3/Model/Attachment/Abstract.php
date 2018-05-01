<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


abstract class AW_Helpdesk3_Model_Attachment_Abstract extends Mage_Core_Model_Abstract
{
    const ROOT_FOLDER = 'aw_hdu3';

    /**
     * @return string
     */
    abstract protected function _getFilePath();

    /**
     * @return $this
     */
    protected function _removeFile()
    {
        @unlink($this->getFilePath());
        return $this;
    }

    /**
     * @return $this
     */
    protected function _beforeDelete()
    {
        $this->_removeFile();
        return parent::_beforeDelete();
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @return $this
     * @throws Exception
     */
    public function setFile($filename, $content)
    {
        $filename = Mage::helper('aw_hdu3')->escapeFilename($filename);
        $this->setFileRealName($filename);
        $filename = $this->_generateFileName($filename);
        $this->setFileName($filename);
        $this->setFileContent($content);
        return $this;
    }

    /**
     * Processing object before save data
     *
     * @return Exception | Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->_prepareFoldersRecursive();
        file_put_contents($this->_getRootDir() . DS . $this->getFileName(), $this->getFileContent());
        return parent::_beforeSave();
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->_getRootDir() . DS . $this->getFileName();
    }

    /**
     * @return string
     */
    public function getFileUrl()
    {
        return $this->_getRootUrl() . DS . $this->getFileName();
    }

    /**
     * @return string
     */
    protected final function _getRootDir()
    {
        return Mage::getBaseDir('media') . DS . self::ROOT_FOLDER . DS . $this->_getFilePath();
    }

    /**
     * @return string
     */
    protected final function _getRootUrl()
    {
        return Mage::getBaseUrl('media') . self::ROOT_FOLDER . DS . $this->_getFilePath();
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function _generateFileName($filename)
    {
        $newFileName = base64_encode($filename . time());
        $fileExt = substr(strrchr($filename, '.'),1);
        if ($fileExt) {
            $newFileName .= '.' . $fileExt;
        }
        return $newFileName;
    }

    protected function _prepareFoldersRecursive()
    {
        $folders = explode(DS, self::ROOT_FOLDER . DS . $this->_getFilePath());
        $folderPath = '';
        foreach ($folders as $folder) {
            $folderPath .= $folder;
            if (!is_dir(Mage::getBaseDir('media') . DS . $folderPath)) {
                mkdir(Mage::getBaseDir('media') . DS . $folderPath, 0777);
            }
            $folderPath .= DS;
        }
        return $this;
    }
}