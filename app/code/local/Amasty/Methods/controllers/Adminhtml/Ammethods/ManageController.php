<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Adminhtml_Ammethods_ManageController extends Mage_Adminhtml_Controller_Action
{
    protected $_availableTypes = array('payment', 'shipping');

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/ammethods')
            ->_addBreadcrumb(Mage::helper('ammethods')->__('System'), Mage::helper('ammethods')->__('System'))
            ->_addBreadcrumb(Mage::helper('ammethods')->__('Methods Visibility'), Mage::helper('ammethods')->__('Methods Visibility'))
        ;
        return $this;
    }
    
    public function saveAction()
    {
        $type = $this->getRequest()->getParam('type');
        if (!in_array($type, $this->_availableTypes))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ammethods')->__('Unable to save. Wrong type specified.'));
            $this->_redirect('*/*', array('type' => 'payment', '_current' => true));
        }
        $websiteId = $this->getRequest()->getParam('website_id', 0);
        $methods  = $this->getRequest()->getPost('ammethods');
        $methodCodes = $this->getRequest()->getPost('ammethods_codes');
        
        foreach ($methodCodes as $methodCode)
        {
            $groups = isset($methods[$methodCode]) ? $methods[$methodCode] : array();
            $visibility = Mage::getModel('ammethods/visibility')->loadVisibility($type, $websiteId, $methodCode);
            if (!$visibility->getEntityId())
            {
                $visibility->setType($type);
                $visibility->setWebsiteId($websiteId);
                $visibility->setMethod($methodCode);
            }
            $visibility->setGroupIds(implode(',', $groups));
            $visibility->save();
        }
        
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ammethods')->__('Visibility options have been saved.'));
        $this->_redirect('*/*', array('type' => $type, '_current' => true));
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('ammethods/adminhtml_manage'))
            ->renderLayout();
    }
    
    public function paymentAction()
    {
        $this->_redirect('*/*', array('type' => 'payment', '_current' => true));
    }
    
    public function shippingAction()
    {
        $this->_redirect('*/*', array('type' => 'shipping', '_current' => true));
    }

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('system/ammethods');
    }
}