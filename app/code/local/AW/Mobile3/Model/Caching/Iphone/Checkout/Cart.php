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


class AW_Mobile3_Model_Caching_Iphone_Checkout_Cart extends Enterprise_PageCache_Model_Container_Abstract
{
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }

    protected function _renderBlock()
    {
        $layout = $this->_getLayout('default');
        $block = $layout->getBlock('checkout.cart');
        if (!$block) {
            return parent::_renderBlock();
        }
        $block->setSkipRenderTag(true);
        return $block->toHtml();
    }

    protected function _getCacheId()
    {
        return 'AW_Mobile3_Model_Caching_Iphone_Checkout_Cart';
    }
}