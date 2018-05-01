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


class AW_Mobile3_Block_Page_Html_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    public function addCrumb($crumbName, $crumbInfo, $after = false)
    {
        if ($this->_isCMSHomePage()) {
            return $this;
        }
        if ($crumbName === 'home') {
            $this->_updateHomeLink($crumbInfo);
        }
        return parent::addCrumb($crumbName, $crumbInfo, $after);
    }

    protected function _isCMSHomePage()
    {
        if (!Mage::app()->getFrontController()->getAction() instanceof Mage_Cms_PageController) {
            return false;
        }
        $pageId = Mage::app()->getRequest()->getParam('page_id', Mage::app()->getRequest()->getParam('id', false));
        $page = Mage::getModel('cms/page')->load($pageId);
        if ($page->getIdentifier() !== AW_Mobile3_Helper_Config::getIPadHomePageId()) {
            return false;
        }
        return true;
    }

    protected function _updateHomeLink(&$crumbInfo)
    {
        if (!AW_Mobile3_Helper_Config::getIPadHomePageId()) {
            return $this;
        }
        $crumbInfo['link'] = Mage::helper('cms/page')->getPageUrl(
            AW_Mobile3_Helper_Config::getIPadHomePageId()
        );
    }
}