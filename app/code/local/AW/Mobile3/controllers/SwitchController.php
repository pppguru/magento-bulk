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


class AW_Mobile3_SwitchController extends Mage_Core_Controller_Front_Action
{
    public function toMobileAction()
    {
        Mage::getSingleton('core/cookie')->delete(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, '/', Mage::helper('aw_mobile3')->getDomainName());
        Mage::getSingleton('core/cookie')->set(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, AW_Mobile3_Helper_Data::MOBILE_VERSION_COOKIE_NAME, true, '/', Mage::helper('aw_mobile3')->getDomainName());
        $this->_redirectReferer();
    }

    public function toDesktopAction()
    {
        Mage::getSingleton('core/cookie')->delete(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, '/', Mage::helper('aw_mobile3')->getDomainName());
        Mage::getSingleton('core/cookie')->set(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, AW_Mobile3_Helper_Data::DESKTOP_VERSION_COOKIE_NAME, true, '/', Mage::helper('aw_mobile3')->getDomainName());
        $this->_redirectReferer();
    }
}