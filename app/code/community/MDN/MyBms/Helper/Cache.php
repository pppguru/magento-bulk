<?php

/**
 * Class Cache
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MDN_MyBms_Helper_Cache extends Mage_Core_Helper_Abstract
{
    /**
     * Directory
     *
     * @return string
     */
    public function getBaseDir()
    {
        $filePath = Mage::getBaseDir('var') . DS . '/mdn/mybms/';

        if (!file_exists($filePath)){
            @mkdir($filePath, 0777, true);
        }

        return $filePath;
    }

    /**
     * Write on file
     *
     * @param $file
     * @param $json
     */
    public function addCache($file, $json)
    {
        file_put_contents($this->getCacheFilePath($file), $json);
    }

    /**
     * get contents of the file
     *
     * @param $file
     * @param int $ttl
     * @return bool|string
     */
    public function loadCache($file, $ttl = 86400)
    {
        $path = $this->getCacheFilePath($file);

        if (file_exists($path) && (time() - filemtime($path)) < $ttl){
            return file_get_contents($path);
        }

        return false;
    }

    /**
     * delete file
     * @param $file
     */
    public function flushCache($file)
    {
        $path = $this->getCacheFilePath($file);
        if (file_exists($path)){
            unlink($path);
        }
    }

    public function getCacheFilePath($file){
        return $this->getBaseDir().$file;
    }
}