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


class AW_Mobile2_Helper_Data extends Mage_Core_Helper_Abstract
{
    const IPHONE_THEME_NAME = 'iphone';
    const IPHONE_PACKAGE_NAME = 'aw_mobile2';
    const IPAD_THEME_NAME = 'ipad';
    const IPAD_PACKAGE_NAME = 'aw_mobile2';
    const MOBILE_COOKIE_NAME = 'aw_mobile_version';
    const MOBILE_VERSION_COOKIE_NAME = 'mobile';
    const DESKTOP_VERSION_COOKIE_NAME = 'desktop';
    const PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE = 'mob2_description';
    const CATEGORY_IPHONE_CMS_BLOCK = 'mob2_cat_iphone_cms_block';
    const CATEGORY_IPAD_CMS_BLOCK = 'mob2_cat_ipad_cms_block';

    protected $_isMobile = null;
    protected $_isTablet = null;

    protected $_isAndroid = null;

    static function isIphoneTheme()
    {
        $currentPackage = Mage::getSingleton('core/design_package')->getPackageName();
        $currentTheme = Mage::getSingleton('core/design_package')->getTheme('frontend');
        if ($currentPackage == self::IPHONE_PACKAGE_NAME && $currentTheme == self::IPHONE_THEME_NAME) {
            return true;
        }
        return false;
    }

    static function isIPadTheme()
    {
        $currentPackage = Mage::getSingleton('core/design_package')->getPackageName();
        $currentTheme = Mage::getSingleton('core/design_package')->getTheme('frontend');
        if ($currentPackage == self::IPAD_PACKAGE_NAME && $currentTheme == self::IPAD_THEME_NAME) {
            return true;
        }
        return false;
    }

    /**
     * Retrive is Magento Enterprise Edition Flag
     * @return boolean
     */
    public function isEE()
    {
        return AW_All_Helper_Versions::getPlatform() == AW_All_Helper_Versions::EE_PLATFORM;
    }

    public function isPhoneDevice()
    {
        return $this->_isMobile() &&  ! $this->_isTablet();
    }

    protected function _isMobile()
    {
        if (is_null($this->_isMobile)) {
            if ( ! array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
                $this->_isMobile = false;
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $detect = new Mobile_Detect;
            $this->_isMobile = $detect->isMobile($userAgent);
        }
        return $this->_isMobile;
    }

    protected function _isTablet()
    {
        if (is_null($this->_isTablet)) {
            if (!array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
                $this->_isTablet = false;
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $detect = new Mobile_Detect;
            $this->_isTablet = $detect->isTablet($userAgent);
        }
        return $this->_isTablet;
    }

    public function isNotSupportedPhoneDevice()
    {
        if (!array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            return true;
        }
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $detect = new Mobile_Detect;
        $isMobile = $detect->isMobile($userAgent) && !$detect->isTablet($userAgent);
        $ieVersion = $detect->version('IE');
        $androidVersion = $detect->version('Android', Mobile_Detect::VERSION_TYPE_FLOAT);
        $isSafari = (bool)$detect->version('Safari');
        if ($isMobile &&
            ($ieVersion && $ieVersion >= 10 || $androidVersion && $androidVersion < 4.0 && $isSafari)
        ) {
            //doesn't support ie10+
            return true;
        }
        return false;
    }

    public function isTabletDevice()
    {
        return $this->_isTablet();
    }

    public function isAndroidDevice()
    {
        if (is_null($this->_isAndroid)) {
            $detect = new Mobile_Detect;
            $this->_isAndroid = (bool)$detect->version('Android', Mobile_Detect::VERSION_TYPE_FLOAT);
        }
        return $this->_isAndroid;
    }

    public function getCategoryIphoneCmsBlockHtml()
    {
        $currentCategory = Mage::registry('current_category');
        if ($currentCategory && $currentCategory->getData(self::CATEGORY_IPHONE_CMS_BLOCK)) {
            return $this->getLayout()->createBlock('cms/block')
                ->setBlockId($currentCategory->getData(self::CATEGORY_IPHONE_CMS_BLOCK))
                ->toHtml()
                ;
        }
        return '';
    }

    public function getCategoryIpadCmsBlockHtml()
    {
        $currentCategory = Mage::registry('current_category');
        if ($currentCategory && $currentCategory->getData(self::CATEGORY_IPAD_CMS_BLOCK)) {
            return $this->getLayout()->createBlock('cms/block')
                ->setBlockId($currentCategory->getData(self::CATEGORY_IPAD_CMS_BLOCK))
                ->toHtml()
                ;
        }
        return '';
    }

    public function isCanOpenCartPopup()
    {
        $visitorDataArray = Mage::getModel('core/session')->getVisitorData();
        if (strpos($visitorDataArray['request_uri'],'awmobile2/cart/updatePost/') ||
            strpos($visitorDataArray['request_uri'],'awmobile2/cart/couponPost/') ||
            strpos($visitorDataArray['request_uri'],'awraf/cart/createCoupon/') ||
            strpos($visitorDataArray['request_uri'],'awmobile2/cart/delete/')
        ) {
            return true;
        }
        return false;
    }

    public function isCanShowMobileVersion()
    {
        //if area != frontend or module is disabled -> default behavior
        if (Mage::getModel('core/design_package')->getArea() != AW_Mobile2_Model_Core_Design_Package::DEFAULT_AREA ||
            !AW_Mobile2_Helper_Config::isEnabled()
        ) {
            return false;
        }

        //switch to mobile version flag
        $isNeedToSwitchFlag = false;

        //check switcher cookie
        $switcherCookie = Mage::getSingleton('core/cookie')->get(self::MOBILE_COOKIE_NAME);

        //if cookie & mobile switcher enabled
        if ($switcherCookie && AW_Mobile2_Helper_Config::isMobileSwitcherEnabled()) {
            //if cookie value = mobile version -> set flag
            if ($switcherCookie == self::MOBILE_VERSION_COOKIE_NAME) {
                $isNeedToSwitchFlag = true;
            }
        } else {
            //if !cookie check detection
            $detection = AW_Mobile2_Helper_Config::isMobileDetection();
            //if detection = auto and its mobile -> show mobile version
            //if detection = force mobile -> show mobile version
            if ($detection == AW_Mobile2_Model_Source_Detection::AUTO_VALUE
                && ($this->isPhoneDevice() || $this->isTabletDevice())
                || $detection == AW_Mobile2_Model_Source_Detection::FORCE_MOBILE_VALUE
            ){
                $isNeedToSwitchFlag = true;
            }

            //TODO: remove if for tablet support
            if ($detection == AW_Mobile2_Model_Source_Detection::AUTO_VALUE
                && $this->isTabletDevice()) {
                $isNeedToSwitchFlag = false;
            }
        }
        if ($this->isPhoneDevice() && $this->isNotSupportedPhoneDevice()) {
            $isNeedToSwitchFlag = false;
        }
        return $isNeedToSwitchFlag;
    }

    public function getIsCanShowUpdateButton()
    {
        if (@class_exists('Mage_Checkout_Block_Cart_Item_Configure')) {
            return true;
        }
        return false;
    }
}