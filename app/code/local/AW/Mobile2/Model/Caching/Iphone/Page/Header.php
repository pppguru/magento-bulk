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


class AW_Mobile2_Model_Caching_Iphone_Page_Header extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * @var array
     */
    protected $_layouts = array();

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }

    protected function _renderBlock()
    {
        $layout = $this->_getLayout('default');
        $block = $layout->getBlock('header');
        if (!$block) {
            return parent::_renderBlock();
        }
        $block->setSkipRenderTag(true);
        return $block->toHtml();
    }

    protected function _getCacheId()
    {
        return 'AW_Mobile2_Model_Caching_Iphone_Page_Header';
    }

    protected function _getLayout($handler = 'default')
    {
        if (!isset($this->_layouts[$handler])) {
            $layout = Mage::app()->getLayout();
            $layout->getUpdate()->load($handler);
            $layout->generateXml();
            $layout->generateBlocks();
            $this->_layouts[$handler] = $layout;
        }
        return $this->_layouts[$handler];
    }
}