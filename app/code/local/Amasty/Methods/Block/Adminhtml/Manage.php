<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Template
{
    protected $_type = '';
    protected $_visibility = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->_type = Mage::app()->getRequest()->getParam('type');
        $this->_prepareVisibility();
    }
    
    protected function _prepareVisibility()
    {
        $collection = Mage::getModel('ammethods/visibility')->getCollection();
        $collection->addFieldToFilter('type', array('eq' => $this->_type));
        $collection->addFieldToFilter('website_id', array('eq' => $this->getCurrentWebsite()));
        $collection->load();
        foreach ($collection as $method)
        {
            $this->_visibility[$method->getMethod()] = explode(',', $method->getGroupIds());
        }
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('amasty/ammethods/manage.phtml');
        return $this;
    }
    
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => 'true'));
    }
    
    public function getWebsiteUrl($website = null)
    {
        if (is_null($website))
        {
            $websiteId = 0;
        } else 
        {
            $websiteId = $website->getId();
        }
        return $this->getUrl('*/*/*', array('website_id' => $websiteId, '_current' => true));
    }
    
    public function getWebsites()
    {
        // $websites = Mage::app()->getWebsites(true);
        // modified by erik, 2016/10/28
        // remove "Admin" store view from Current Scope dropdown on Payment/Shipping Methods Visibility admin pages
        // check http://mantis.zyloo.com/view.php?id=413
        $websites = Mage::app()->getWebsites(false);
        return $websites;
    }
    
    public function getCurrentWebsite()
    {
        // $websiteId = Mage::app()->getRequest()->getParam('website_id', 0);
        // modified by erik, 2016/10/28
        // remove "Admin" store view from Current Scope dropdown on Payment/Shipping Methods Visibility admin pages
        // check http://mantis.zyloo.com/view.php?id=413
        $websiteId = Mage::app()->getDefaultStoreView()->getStoreId();
        return $websiteId;
    }
    
    public function getMethodsType()
    {
        return ucwords($this->_type);
    }
    
    public function getMethods()
    {
        if ('payment' == $this->_type)
        {
            $methods = $this->_getPaymentMethods();
        } elseif ('shipping' == $this->_type)
        {
            $methods = $this->_getShippingMethods();
        }
        return $methods;
    }
    
    public function getCustomerGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();
        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label' => Mage::helper('salesrule')->__('NOT LOGGED IN')));
        }
        return $customerGroups;
    }
    
    public function isGroupSelected($group, $methodCode)
    {
        if (isset($this->_visibility[$methodCode]) && in_array($group['value'], $this->_visibility[$methodCode]))
        {
            return true;
        }
        return false;
    }
    
    protected function _getPaymentMethods()
    {
        $methods = Mage::getStoreConfig('payment');
        return $methods;
    }
    
    protected function _getShippingMethods()
    {
        $methods = Mage::getStoreConfig('carriers');
        return $methods;
    }
}