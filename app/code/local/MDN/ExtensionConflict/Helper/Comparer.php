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
class MDN_ExtensionConflict_Helper_Comparer extends Mage_Core_Helper_Abstract
{
    const EXT_FOLDER_NAME = 'extension';

    public function compareExtension($debug = false)
    {
        $outputBuffer = '';
        try {
            $startPath = $this->getFolderPathToCompare();
            if($this->checkFolderPathToCompare($startPath)){
                $differences = $this->recursiveComparator($startPath, $outputBuffer, $debug);
                $summary = $this->listDifferences($differences);
                $outputBuffer = $summary . '<br><hr><br>' . $outputBuffer;
            }else{
                $outputBuffer = $startPath.' cant be write';
            }
        }catch (Exception $ex){
            $outputBuffer .= $ex->getMessage().'<br>'.$ex->getTraceAsString();
        }
        return $outputBuffer;
    }

    public function getCompareExtensionList($debug = false)
    {
        $differences = array();
        try {
            $startPath = $this->getFolderPathToCompare();
            if($this->checkFolderPathToCompare($startPath)){
                $differences = $this->recursiveComparator($startPath, $outputBuffer, $debug);
            }
        }catch (Exception $ex){
            $differences = $ex->getMessage().'<br>'.$ex->getTraceAsString();
        }
        return $differences;
    }

    public function getFolderPathToCompare(){
        return Mage::app()->getConfig()->getTempVarDir() . DS .self::EXT_FOLDER_NAME. DS;
    }

    public function checkFolderPathToCompare($path){
        if(!is_dir($path)){
            @mkdir($path, 0775, true);
        }
        return @is_writable($path);
    }


    public function listDifferences($differences)
    {
        $buffer = '<br><br><b>' . count($differences) . ' files have at least a difference</b>';

        foreach ($differences as $difference) {
            $buffer .= '<br>' . $difference;
        }

        return $buffer;
    }


    public function recursiveComparator($startPath, &$outputBuffer,  $debug = false)
    {

        $diffs = array();
        $extensionFilesPath = array();

        /*if ($debug) {
            $outputBuffer .= '<br><br>startPath:' . $startPath;
        }*/

        $this->getDirContents($startPath, $startPath, $extensionFilesPath);

        /*if ($debug) {
            $outputBuffer .= '<br><br>Nb File to compare:' . count($extensionFilesPath);
        }*/

        foreach ($extensionFilesPath as $filePath) {
            $originalFile = $startPath . DS . $filePath;
            $fileInstalled = Mage::getBaseDir() . DS . $filePath;
            if ($debug) {
                $outputBuffer .= '<br><br>Comparing :';
                $outputBuffer .='<br>original  : ' . $originalFile;
                $outputBuffer .='<br>installed : ' . $fileInstalled;
            }
            if ($this->isComparableFile($originalFile) && $this->isComparableFile($fileInstalled)) {

                $diffBuffer = array();
                if (!$this->filesAreIdentical($originalFile, $fileInstalled, $diffBuffer)) {
                    $outputBuffer .='<br><br><b>DIFF FOUND ON : ' . $fileInstalled . '</b>';
                    foreach ($diffBuffer as $lineId => $lineContent) {
                        $outputBuffer .='<br>on line(s) : ' . $lineId . '<br><pre>' . $lineContent . '</pre>';
                    }
                    $diffs[] = $fileInstalled;
                }
            } else {
                if ($debug) {
                    $outputBuffer .='<br><br><b>SKIPPED : ' . $originalFile . ' OR ' . $fileInstalled . '</b>';
                }
            }
        }
        return $diffs;
    }

    function getDirContents($dir, $rootPathToExclude, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DS . $value);
            if (!is_dir($path)) {
                $results[] = str_replace($rootPathToExclude, '', $path);
            } else if (is_dir($path) && $value != "." && $value != "..") {
                $this->getDirContents($path, $rootPathToExclude, $results);
            }
        }

        return $results;
    }


    public function isComparableFile($path)
    {
        $comparable = false;
        $allowedExt = array('.php', '.js', '.xml', '.phtml', '.csv', '.css', '.html');
        foreach ($allowedExt as $fileExt) {
            if ((strpos($path, $fileExt) !== FALSE)) {
                $comparable = true;
                break;
            }
        }
        return $comparable;
    }


    function filesAreIdentical($originalFile, $fileInstalled, &$diffBuffer)
    {
        $lineCount = 0;
       
        if (!file_exists($fileInstalled)) {
            $diffBuffer[$lineCount] = 'FILE2: does not exist ' . $fileInstalled;
            return FALSE;
        }
        
        if (!$fp1 = fopen($originalFile, 'rb')) {
            $diffBuffer[$lineCount] = 'FILE1: cant be open ' . $originalFile;
            return FALSE;
        }

        if (!$fp2 = fopen($fileInstalled, 'rb')) {
            $diffBuffer[$lineCount] = 'FILE2: cant be open ' . $fileInstalled;
            fclose($fp1);
            return FALSE;
        }

        $same = TRUE;
        $securityMax = 50000;
        while (!feof($fp1) and !feof($fp2)) {
            $lineCount++;
            if ($lineCount > $securityMax) {
                break;
            }

            $lineFp1 = $this->prepareForDiff(fgets($fp1));
            $lineFp2 = $this->prepareForDiff(fgets($fp2));

            if ($lineFp1 !== $lineFp2) {
                $same = FALSE;
                $diffBuffer[$lineCount] = 'First change is : FILE1: ' . $lineFp1 . '<br/>FILE2: ' . $lineFp2;
                break;
            }
            unset($lineFp1);
            unset($lineFp2);
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    }

    function prepareForDiff($text)
    {
        $token = array("\n", "\r");
        return trim(str_replace($token, '', $text));
    }

    /**
     * Delete a file or recursively delete a directory
     *
     * @param string $str Path to file or directory
     */
    public function recursiveDelete($str)
    {
        if (is_file($str)) {
            return @unlink($str);
        } elseif (is_dir($str)) {
            $scan = glob(rtrim($str, '/').'/*');
            foreach ($scan as $index => $path) {
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }

    public function cleanComparerFolder(){

        $startPath = $this->getFolderPathToCompare();
        if($this->checkFolderPathToCompare($startPath)){
            $this->recursiveDelete($startPath);
        }
        //recreate folder
        $startPath = $this->getFolderPathToCompare();
        return 'Folder '.$startPath.' is now empty';
    }


}