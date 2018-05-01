<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Helper_Data extends Mage_Core_Helper_Abstract
{
    static protected $_customerGroupId = null;
    
    public function canUseMethod($method, $type)
    {
        if ('payment' == $type)
        {
            return $this->_canUsePaymentMethod($method);
        }
        if ('shipping' == $type)
        {
            return $this->_canUseShippingMethod($method);
        }
        return true;
    }
    
    protected function _getCustomerGroupId()
    {
        if (!is_null(self::$_customerGroupId))
        {
            return self::$_customerGroupId;
        }
        if (!Mage::helper('customer')->getCustomer()->getId() && $this->_doReturnNullForNotLoggedIn())
        {
            return 0;
        }
        return Mage::helper('customer')->getCustomer()->getGroupId();
    }
    
    /**
    * @param string $method Method Code
    */
    protected function _canUseShippingMethod($method)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $visibility = Mage::getModel('ammethods/visibility')->loadVisibility('shipping', $websiteId, $method);
        if (!$visibility->getEntityId())
        {
            // if nothing for current website, will load default one
            $visibility = Mage::getModel('ammethods/visibility')->loadVisibility('shipping', 0, $method);
        }
        if (!$visibility->getEntityId())
        {
            // if no record, will allow
            return true;
        }
        if ('' === $visibility->getGroupIds())
        {
            return false;
        }
        $allowedGroups = explode(',', $visibility->getGroupIds());
        $customerGroupId = $this->_getCustomerGroupId();

        if (empty($customerGroupId)) {
            $customerGroupId = 0;
        }

        if (in_array($customerGroupId, $allowedGroups))
        {
            return true;
        }
        return false;
    }
    
    /**
    * @param object $method Method Object
    */
    protected function _canUsePaymentMethod($method)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $visibility = Mage::getModel('ammethods/visibility')->loadVisibility('payment', $websiteId, $method->getCode());
        if (!$visibility->getEntityId())
        {
            // if nothing for current website, will load default one
            $visibility = Mage::getModel('ammethods/visibility')->loadVisibility('payment', 0, $method->getCode());
        }
        if (!$visibility->getEntityId())
        {
            // if no record, will allow
            return true;
        }
        if ('' === $visibility->getGroupIds())
        {
            return false;
        }
        $allowedGroups = explode(',', $visibility->getGroupIds());
        if (in_array($this->_getCustomerGroupId(), $allowedGroups))
        {
            return true;
        }
        return false;
    }
    
    protected function _doReturnNullForNotLoggedIn()
    {
        $onepage = Mage::getSingleton('checkout/type_onepage');
        if (Mage_Checkout_Model_Type_Onepage::METHOD_GUEST == $onepage->getQuote()->getCheckoutMethod())
        {
            return true;
        }
        return false;
    }
}