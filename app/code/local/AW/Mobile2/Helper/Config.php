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
 * @package    AW_Mobile2
 * @version    2.0.6
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile2_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_IS_ENABLED = 'aw_mobile2/general/is_enabled';
    const GENERAL_IPHONE_HOME_PAGE = 'aw_mobile2/general/iphone_home_page';
    const GENERAL_IPAD_HOME_PAGE = 'aw_mobile2/general/ipad_home_page';
    const GENERAL_DISPLAY_STOCK_STATUS_AT_CATALOG = 'aw_mobile2/general/display_stock_status_at_catalog';

    const DESIGN_MOBILE_LOGO_SRC = 'aw_mobile2/design/mobile_logo_src';
    const DESIGN_TABLET_LOGO_SRC = 'aw_mobile2/design/tablet_logo_src';
    const DESIGN_BOOKMARKS_SRC = 'aw_mobile2/design/bookmarks_src';

    const BEHAVIOR_MOBILE_DETECT = 'aw_mobile2/behavior/mobile_detect';
    const BEHAVIOR_SWITCHER = 'aw_mobile2/behavior/switcher';

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getIPhoneHomePageId($store = null)
    {
        return (string)Mage::getStoreConfig(self::GENERAL_IPHONE_HOME_PAGE, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getIPadHomePageId($store = null)
    {
        return (string)Mage::getStoreConfig(self::GENERAL_IPAD_HOME_PAGE, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isCanDisplayStockStatusAtCatalog($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_DISPLAY_STOCK_STATUS_AT_CATALOG, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getMobileLogoSrc($store = null)
    {
        $src = (string)Mage::getStoreConfig(self::DESIGN_MOBILE_LOGO_SRC, $store);
        if ($src) {
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_mobile2/' . $src;
        }
        return trim($src);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getTabletLogoSrc($store = null)
    {
        $src = (string)Mage::getStoreConfig(self::DESIGN_TABLET_LOGO_SRC, $store);
        if (!$src) {
            $src = (string)Mage::getStoreConfig(self::DESIGN_MOBILE_LOGO_SRC, $store);
        }
        if ($src) {
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_mobile2/' . $src;
        }
        return trim($src);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getBookmarksSrc($store = null)
    {
        $src = (string)Mage::getStoreConfig(self::DESIGN_BOOKMARKS_SRC, $store);
        if ($src) {
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_mobile2/' . $src;
        }
        return trim($src);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public static function isMobileDetection($store = null)
    {
        return (int)Mage::getStoreConfig(self::BEHAVIOR_MOBILE_DETECT, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isMobileSwitcherEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::BEHAVIOR_SWITCHER, $store);
    }
}