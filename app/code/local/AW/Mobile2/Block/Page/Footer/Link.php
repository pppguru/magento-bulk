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


class AW_Mobile2_Block_Page_Footer_Link extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        $_result = parent::_prepareLayout();
        if (!$this->helper('aw_mobile2/config')->isEnabled() ||
            !$this->helper('aw_mobile2/config')->isMobileSwitcherEnabled()
        ) {
            return $_result;
        }
        $footerBlock = $this->getLayout()->getBlock('footer_links');
        if ($footerBlock) {
            if (!Mage::helper('aw_mobile2')->isIphoneTheme() && !Mage::helper('aw_mobile2')->isIPadTheme()) {
                $footerBlock->addLink($this->__('Mobile Version'),
                    'awmobile2/switch/toMobile', $this->__('Mobile Version'), true
                );
            } else {
                $footerBlock->addLink($this->__('Desktop Version'),
                    'awmobile2/switch/toDesktop', $this->__('Desktop Version'), true
                );
            }
        }
        return $_result;
    }
}