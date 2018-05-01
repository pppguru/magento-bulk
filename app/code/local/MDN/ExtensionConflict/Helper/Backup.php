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
class MDN_ExtensionConflict_Helper_Backup extends Mage_Core_Helper_Abstract
{

    const EXT_FOLDER_NAME = 'ExtensionConflictBackup';
    const ROOT_FOLDER_NAME = 'var';
    const DELIM = '.';

    private $_rootPath;
    private $_conflictPath;
    private $_differencePath;
    private $_conflictModel;


    public function backupConflict($ecId){

        if($this->checkFolderPathForBackup()){
            $this->checkAndLoadConflict($ecId);
            if($this->createFolderForConflictBackup()){
                $this->backupConflictFiles();
            }
        }
    }





    private function checkAndLoadConflict($ecId){
        $this->_conflictModel = mage::getModel('ExtensionConflict/ExtensionConflict')->load($ecId);
        if(!$this->_conflictModel->getId()){
            throw new Exception("Can't load Conlict#".$ecId);
        }
    }

    private function getFolderPathForBackup(){
        return Mage::getBaseDir() . DS . self::ROOT_FOLDER_NAME . DS .self::EXT_FOLDER_NAME;
    }

    private function checkFolderPathForBackup(){

        $this->_rootPath = $this->getFolderPathForBackup();

        if(!is_dir($this->_rootPath)){
            @mkdir($this->_rootPath, 0775, true);
        }
        return @is_writable($this->_rootPath);
    }

    private function createFolderForConflictBackup(){
        $path = $this->_rootPath.DS.date('Y-m-d').self::DELIM.trim(strtoupper($this->_conflictModel->getec_core_module().'_'.$this->_conflictModel->getec_core_class()));
        if(!is_dir($path)){
            @mkdir($path, 0775, true);
        }
        $this->_conflictPath = $path;
        return @is_writable($path);
    }

    private function backupConflictFiles(){
        $rewriteList = $this->_conflictModel->getRewriteClassesInformation();

        foreach ($rewriteList as $rewrite) {

            $sourcePath = $rewrite['class_path'];
            $backupPath = $this->getFinalBackupPath($sourcePath);
            @copy($sourcePath, $backupPath);
        }

    }

    private function getFinalBackupPath($sourcePath){

        $relativePath = substr($sourcePath, strpos($sourcePath,'app/code'));
        $backupPath = $this->_conflictPath.DS.$relativePath;

        $relativeFolder = substr($relativePath, 0,strrpos($relativePath,'/'));
        $backupFolder = $this->_conflictPath.DS.$relativeFolder.DS;
        if(!is_dir($backupFolder)){
            @mkdir($backupFolder, 0755, true);
        }

        return $backupPath;
    }

    public function getBackupedConflictList()
    {
        $list = array();
        if ($this->checkFolderPathForBackup()) {
            $list = scandir($this->_rootPath);
        }
        return $list;
    }


    //----------------Differences

    public function backupDifferences($differencesList){

        if($this->checkFolderPathForBackup()){
            if($this->createFolderForDifferencesBackup()){
                $this->backupDifferencesFiles($differencesList);
            }
        }
    }

    private function createFolderForDifferencesBackup(){
        $path = $this->_rootPath.DS.date('Y-m-d_H-i-s').'_Differences';
        if(!is_dir($path)){
            @mkdir($path, 0775, true);
        }
        $this->_differencePath = $path;
        return @is_writable($path);
    }

    private function getFinalDifferenceBackupPath($sourcePath){

        $relativePath = substr($sourcePath, strpos($sourcePath,'app/code'));
        $backupPath = $this->_differencePath.DS.$relativePath;

        $relativeFolder = substr($relativePath, 0,strrpos($relativePath,'/'));
        $backupFolder = $this->_differencePath.DS.$relativeFolder.DS;
        if(!is_dir($backupFolder)){
            @mkdir($backupFolder, 0755, true);
        }

        echo $backupPath;
        return $backupPath;
    }

    private function backupDifferencesFiles($differencesList){

        foreach ($differencesList as $sourcePath) {
            $backupPath = $this->getFinalDifferenceBackupPath($sourcePath);
            @copy($sourcePath, $backupPath);
        }

    }
}