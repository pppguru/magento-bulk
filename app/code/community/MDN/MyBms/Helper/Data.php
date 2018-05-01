<?php

/**
 * Class Data
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MDN_MyBms_Helper_Data extends Mage_Core_Helper_Abstract
{
    CONST DOC_CACHE_FILENAME = 'cached_documentation.json';
    /**
     * If not in cache return Json + cache file. If in cache return file cached
     *
     * @return array
     */
    public function listDocumentation()
    {
        $list = Mage::helper("MyBms/Cache")->loadCache(self::DOC_CACHE_FILENAME);

        if ($list === false)
        {
            $list = Mage::helper("MyBms/Webservice")->listBmsDocumentation();

            Mage::helper("MyBms/Cache")->addCache(self::DOC_CACHE_FILENAME, $list);
        }
        return json_decode($list, true);
    }
}