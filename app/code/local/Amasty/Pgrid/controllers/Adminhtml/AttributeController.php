<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Adminhtml_AttributeController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        $attributes = Mage::app()->getRequest()->getParam('pattribute', array());

        // will store columns by admin users, if necessary
        $extraKey = Mage::app()->getRequest()->getParam('attributesKey', '');
        $category = Mage::app()->getRequest()->getParam('category', '');
        $preset = Mage::app()->getRequest()->getParam('preset', 0) == 1;
        
        $categoryKey = '';
        
        if (Mage::getStoreConfig('ampgrid/attr/byadmin'))
        {
            $extraKey .= Mage::getSingleton('admin/session')->getUser()->getId();
            $categoryKey .= Mage::getSingleton('admin/session')->getUser()->getId();
        }
        
        if ($preset){
            $attributes = array();
        }

        Mage::getConfig()->saveConfig('ampgrid/attributes/ongrid' . $extraKey, implode(',', array_keys($attributes)));
        Mage::getConfig()->saveConfig('ampgrid/attributes/category' . $categoryKey, $category);
        
        Mage::getConfig()->cleanCache();
        
        $backUrl = Mage::app()->getRequest()->getParam('backurl');
        if (!$backUrl)
        {
            $backUrl = Mage::helper('core/url')->getUrl('adminhtml/catalog/product');
        }
        $this->getResponse()->setRedirect($backUrl);
    }
}
