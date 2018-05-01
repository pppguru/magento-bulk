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


class AW_Mobile3_Model_Core_Design_Package extends Mage_Core_Model_Design_Package
{
    protected function _checkUserAgentAgainstRegexps($regexpsConfigPath)
    {
        if ( ! Mage::helper('aw_mobile3')->isCanShowMobileVersion()) {
            return parent::_checkUserAgentAgainstRegexps($regexpsConfigPath);
        }

        if ($regexpsConfigPath == 'design/package/ua_regexp') {
            return AW_Mobile3_Helper_Data::IPHONE_PACKAGE_NAME;
        }
        return AW_Mobile3_Helper_Data::getIphoneThemeName();
    }

    public function setPackageName($name = '')
    {
        if ( ! Mage::helper('aw_mobile3')->isCanShowMobileVersion()) {
            return parent::setPackageName($name);
        }
        return parent::setPackageName(AW_Mobile3_Helper_Data::IPHONE_PACKAGE_NAME);
    }

    public function setTheme()
    {
        if ( ! Mage::helper('aw_mobile3')->isCanShowMobileVersion()) {
            switch (func_num_args()) {
                case 1: return parent::setTheme(func_get_arg(0));
                case 2: return parent::setTheme(func_get_arg(0), func_get_arg(1));
                default:
                    throw Mage::exception(Mage::helper('core')->__('Wrong number of arguments for %s', __METHOD__));
            }
        }
        return parent::setTheme(AW_Mobile3_Helper_Data::getIphoneThemeName());
    }

    /**
     * @param $packageName string
     * @param $themeName string
     *
     * @return bool
     */
    public function designPackageThemeExists($packageName, $themeName)
    {
        return in_array($themeName, $this->getThemeList($packageName));
    }
}