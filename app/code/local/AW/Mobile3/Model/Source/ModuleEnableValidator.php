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


class AW_Mobile3_Model_Source_ModuleEnableValidator extends Mage_Core_Model_Config_Data
{
    const NO_VALUE = 0;
    protected function _beforeSave()
    {
        if ($this->_isCanEnabled()) {
            return parent::_beforeSave();
        }
        $this->setValue(self::NO_VALUE);
        return $this;
    }

    protected function _isCanEnabled()
    {
        $data = Mage::app()->getRequest()->getParam('groups', array());
        $iphoneHomePage = $data['general']['fields']['iphone_home_page']['value'];
        if (!$iphoneHomePage) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('aw_mobile3')->__('Please specify mobile home pages.')
            );
            return false;
        }
        return true;
    }
}