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


class AW_Mobile2_Model_Enterprise_PageCache_Processor extends Enterprise_PageCache_Model_Processor
{
    protected function _getDesignPackage()
    {
        try {
            Mage::app()->getSafeStore();
        } catch (Exception $e) {
            //need for init current store
            $option = Mage::registry('application_params');
            Mage::app()->init($option['scope_code'], $option['scope_type'], $option['options']);
        }

        if ( ! Mage::helper('aw_mobile2')->isCanShowMobileVersion()) {
            return parent::_getDesignPackage();
        }
        return AW_Mobile2_Helper_Data::IPHONE_PACKAGE_NAME . AW_Mobile2_Helper_Data::IPHONE_THEME_NAME;
    }
}