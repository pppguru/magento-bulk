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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Model_Source_ModuleEnableValidator extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        if ($this->_isCanEnabled()) {
            return parent::_beforeSave();
        }
        $this->setValue(AW_Helpdesk3_Model_Source_Yesno::NO_VALUE);
        return $this;
    }

    protected function _isCanEnabled()
    {
        foreach ($this->_getStoreIds() as $storeId) {
            $store = Mage::getModel('core/store')->load($storeId);
            if ($this->getValue() == 1 && !Mage::helper('aw_hdu3/config')->isActiveDepartments($storeId)) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('aw_hdu3')->__('Module can\'t be enabled until there is at least one department. '
                    . 'There are no active Help Desk departments for the \'%s\' store.', $store->getName()));
                return false;
            }
            $defaultDepartmentId = $this->getData('fieldset_data/default_department');
            if ($this->getValue() == 1 && empty($defaultDepartmentId)) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('aw_hdu3')->__('Module can\'t be enabled if no Primary department selected. '
                    . 'There is no Primary Help Desk department for the \'%s\' store.', $store->getName()));
                return false;
            }
            /** @var AW_Helpdesk3_Model_Department $department */
            $department = Mage::getModel('aw_hdu3/department')->load($defaultDepartmentId);
            if ($this->getValue() == 1 && (!$department->getId() || !$department->isEnabled())) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('aw_hdu3')->__('Module can\'t be enabled if no Primary department selected. '
                    . 'There is no Primary Help Desk department for the \'%s\' store.', $store->getName()));
                return false;
            }
        }
        return true;
    }

    protected function _getStoreIds()
    {
        $website = Mage::app()->getRequest()->getParam('website', null);
        $store   = Mage::app()->getRequest()->getParam('store', null);
        $_storeIds = array();

        //store config
        if (null !== $store) {
            $storeModel = Mage::getModel('core/store')->load($store);
            if ($storeModel->getId()) {
                array_push($_storeIds, $storeModel->getId());
            }
        }

        //website config
        if (null === $store && null !== $website) {
            $websiteModel = Mage::getModel('core/website')->load($website);
            foreach ($websiteModel->getStoreCollection() as $storeModel) {
                array_push($_storeIds, $storeModel->getId());
            }
        }

        //default config
        if (null === $store && null === $website) {
            $storeCollection = Mage::getModel('core/store')->getCollection();
            foreach ($storeCollection as $storeModel) {
                array_push($_storeIds, $storeModel->getId());
            }
        }
        return $_storeIds;
    }
}
