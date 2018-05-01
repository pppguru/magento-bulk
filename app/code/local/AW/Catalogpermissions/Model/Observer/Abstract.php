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
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


abstract class AW_Catalogpermissions_Model_Observer_Abstract extends Varien_Object
{

    /**
     * @var AW_Catalogpermissions_Helper_Data
     */
    protected $_helper;

    protected function _construct()
    {

        $this->_initEnv();
    }

    protected function _initEnv()
    {

        $this->_helper = Mage::helper('catalogpermissions');
    }

    public function regroupProductCollection($ProductCollection)
    {
        if (version_compare(Mage::getVersion(), '1.4.2.0', '<')) {
            $group = $ProductCollection->getSelect()->getPart(Zend_Db_Select::GROUP);
            if (count($group) > 0) {
                $ProductCollection->getSelect()->reset(Zend_Db_Select::GROUP);
                foreach ($group as $g) {
                    if (!preg_match("#\.#is", $g)) {
                        $ProductCollection->getSelect()->group("e." . $g);
                    } else {
                        $ProductCollection->getSelect()->group($g);
                    }
                }
            }
        }
    }

    protected function _getGroupId()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')
            ->getCustomer()->getGroupId() : -1;
    }

    protected function _registerScope($data, $key, $flag = true)
    {

        if (empty($data)) {
            $data = array();
        }

        Mage::register($key, $data, $flag);

    }

    protected function _validateRegistryKey($key)
    {

        if (Mage::registry($key) !== null) {
            return Mage::registry($key);
        }
        return false;

    }

    protected static function _validateProcess()
    {

        if (Mage::getStoreConfig('advanced/modules_disable_output/AW_Catalogpermissions')) {
            return false;
        }
        if (Mage::app()->getRequest()->getModuleName() === 'api') {
            return false;
        }
        return true;
    }

    public function prepareRewrites()
    {
        AW_Catalogpermissions_Helper_Data::prepareRewrites();
        if (Mage::getStoreConfig('advanced/modules_disable_output/AW_Catalogpermissions')) {
            return;
        }
        if (Mage::app()->getRequest()->getModuleName() === 'api') {
            return;
        }
        if (AW_Catalogpermissions_Helper_Data::_getPathInfo() == 'cart_add') {
            AW_Catalogpermissions_Helper_Data::checkProductAvailability();
        }
        if (AW_Catalogpermissions_Helper_Data::_getPathInfo('_', 'full') == 'checkout_cart_addgroup') {
            AW_Catalogpermissions_Helper_Data::checkRecentOrdersAvailability();
        }
    }

}
