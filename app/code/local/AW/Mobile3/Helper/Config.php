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
 * @package    AW_Mobile3
 * @version    3.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile3_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_IS_ENABLED = 'aw_mobile3/general/is_enabled';
    const GENERAL_IPHONE_HOME_PAGE = 'aw_mobile3/general/iphone_home_page';
    const GENERAL_IPAD_HOME_PAGE = 'aw_mobile3/general/ipad_home_page';
    const GENERAL_DISPLAY_STOCK_STATUS_AT_CATALOG = 'aw_mobile3/general/display_stock_status_at_catalog';

    const DESIGN_MOBILE_THEME_NAME = 'aw_mobile3/design/mobile_theme_name';
    const DESIGN_TABLET_THEME_NAME = 'aw_mobile3/design/tablet_theme_name';
    const DESIGN_MOBILE_LOGO_SRC = 'aw_mobile3/design/mobile_logo_src';
    const DESIGN_TABLET_LOGO_SRC = 'aw_mobile3/design/tablet_logo_src';
    const DESIGN_BOOKMARKS_SRC = 'aw_mobile3/design/bookmarks_src';
    const DESIGN_FOOTER_LINKS_BLOCK = 'aw_mobile3/design/footer_links_block';
    const DESIGN_CYRILLIC_FONTS = 'aw_mobile3/design/cyrillic_fonts';

    const BEHAVIOR_TABLET_DETECT = 'aw_mobile3/behavior/tablet_detect';
    const BEHAVIOR_MOBILE_DETECT = 'aw_mobile3/behavior/mobile_detect';
    const BEHAVIOR_SWITCHER = 'aw_mobile3/behavior/switcher';
    const BEHAVIOR_MOBILE_REDIRECT = 'aw_mobile3/behavior/mobile_redirect';
    const BEHAVIOR_REDIRECT_TO = 'aw_mobile3/behavior/redirect_to';

    const GANALYTICS_GA_CODE = 'aw_mobile3/ganalytics/gacode';

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store) && (bool)Mage::helper('core')->isModuleOutputEnabled('AW_Mobile3');
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getMobileThemeName($store = null)
    {
        return  (string) Mage::getStoreConfig(self::DESIGN_MOBILE_THEME_NAME, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getTabletThemeName($store = null)
    {
        return  (string) Mage::getStoreConfig(self::DESIGN_TABLET_THEME_NAME, $store);
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
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_mobile3/' . $src;
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
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_mobile3/' . $src;
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
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_mobile3/' . $src;
        }
        return trim($src);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string|null
     */
    public static function getCustomFooterLinksHtml($store = null)
    {
        if ($blockId = Mage::getStoreConfig(self::DESIGN_FOOTER_LINKS_BLOCK)) {
            $block = Mage::app()->getLayout()->createBlock('cms/block');
            if ($block) {
                return $block->setBlockId($blockId)->toHtml();
            }
        }
        return null;
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public static function isCyrillicFonts($store = null)
    {
        return (int)Mage::getStoreConfig(self::DESIGN_CYRILLIC_FONTS, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public static function isTabletDetection($store = null)
    {
        return (int)Mage::getStoreConfig(self::BEHAVIOR_TABLET_DETECT, $store);
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

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public static function isMobileRedirectToSubdomain($store = null)
    {
        return (bool)Mage::getStoreConfig(self::BEHAVIOR_MOBILE_REDIRECT, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public static function getStoreMobileRedirectToSubdomain($store = null)
    {
        return Mage::getStoreConfig(self::BEHAVIOR_REDIRECT_TO, $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    public static function getGoogleAnalyticsCode($store = null)
    {
        return Mage::getStoreConfig(self::GANALYTICS_GA_CODE, $store);
    }
}